<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDownload;
use App\Models\Download;
use App\Services\YtDlpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __construct(protected YtDlpService $ytdlp) {}

    // ─── Pages ───────────────────────────────────────────────────────────────

    public function index(Request $request): \Illuminate\View\View
    {
        $history = Download::forSession($request->session()->getId())
            ->recent(15)
            ->get();

        return view('home', compact('history'));
    }

    // ─── API ─────────────────────────────────────────────────────────────────

    public function fetchMeta(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'url' => ['required', 'url', 'max:2000'],
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()->first('url')], 422);
        }

        $url = $request->input('url');

        try {
            $meta = $this->ytdlp->fetchMetadata($url);
        } catch (\Throwable $e) {
            dd($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not process that URL. Make sure it is a valid, public video link.',
            ], 422);
        }

        $download = Download::create([
            ...$meta,
            'url'        => $url,
            'status'     => 'ready',
            'session_id' => $request->session()->getId(),
            'user_id'    => $request->user()?->id,
        ]);

        return response()->json([
            'success'     => true,
            'uuid'        => $download->uuid,
            'title'       => $download->title,
            'thumbnail'   => $download->thumbnail,
            'duration'    => $download->duration,
            'channel'     => $download->channel,
            'view_count'  => $download->views,
            'platform'    => $download->platform_label,
        ]);
    }

    public function startDownload(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'uuid'       => ['required', 'string', 'exists:downloads,uuid'],
            'format'     => ['required', 'in:mp4,webm,mkv,audio'],
            'resolution' => ['required', 'integer', 'in:360,480,720,1080,2160'],
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => $v->errors()->first()], 422);
        }

        $download = Download::where('uuid', $request->input('uuid'))->firstOrFail();

        if (
            $download->session_id !== $request->session()->getId()
            && $download->user_id !== $request->user()?->id
        ) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (!in_array($download->status, ['ready', 'failed'])) {
            return response()->json(['success' => false, 'message' => 'Download already in progress or complete.'], 409);
        }

        $download->update([
            'format'     => $request->input('format'),
            'resolution' => (string) $request->input('resolution'),
            'status'     => 'pending',
            'progress'   => 0,
            'error'      => null,
        ]);

        // Seed the cache immediately so SSE has something to read right away
        Cache::put("dl_progress:{$download->uuid}", [
            'status' => 'pending',
            'pct'    => 0,
            'speed'  => null,
            'eta'    => null,
            'error'  => null,
        ], now()->addMinutes(60));

        ProcessDownload::dispatch($download);

        return response()->json(['success' => true, 'uuid' => $download->uuid]);
    }

    /**
     * GET /download/progress/{uuid}
     *
     * Server-Sent Events stream.
     *
     * Reads from Cache (written by the job every progress tick) rather than
     * polling the DB — this gives instant updates without DB lag.
     *
     * Frontend field names:
     *   pct    → integer 0-100
     *   speed  → string  e.g. "2.4MiB/s" or null
     *   eta    → string  e.g. "00:18" or null
     *   status → string  "pending" | "downloading" | "done" | "failed"
     *   error  → string|null
     */
    public function progress(Request $request, string $uuid): StreamedResponse
    {
        $download = Download::where('uuid', $uuid)->firstOrFail();

        if (
            $download->session_id !== $request->session()->getId()
            && $download->user_id !== $request->user()?->id
        ) {
            abort(403);
        }

        // ── SSE design: short-lived, reconnect-based ─────────────────────
        //
        // Instead of one long blocking process (bad for server — ties up a
        // PHP worker for the entire download duration), we use short 25-second
        // windows. The browser's native SSE EventSource reconnects automatically
        // every time the server closes the connection, so progress is seamless.
        //
        // Each window: poll cache every 1s for up to 25 iterations, then close.
        // Browser reconnects within ~1s (SSE retry header). Server workers are
        // freed between reconnects. No set_time_limit(0) needed.
        //
        // Timeline from browser perspective: continuous stream.
        // Timeline from server perspective: many short 25s requests.

        return response()->stream(function () use ($uuid) {

            $cacheKey       = "dl_progress:{$uuid}";
            $lastSent       = null;
            $stuckAt90Since = null;
            $windowSeconds  = 25;   // stay under PHP max_execution_time (30s)
            $windowStart    = time();

            while (time() - $windowStart < $windowSeconds) {

                if (connection_aborted()) break;

                // ── Read from cache (written by the job) ───────────────────
                $cached = Cache::get($cacheKey);

                if (!$cached) {
                    // Cache not seeded yet — read DB once as fallback
                    $dl     = Download::where('uuid', $uuid)->first();
                    $cached = [
                        'status' => $dl?->status ?? 'pending',
                        'pct'    => $dl?->progress ?? 0,
                        'speed'  => null,
                        'eta'    => null,
                        'error'  => null,
                    ];
                }

                $payload = [
                    'status' => $cached['status'] ?? 'pending',
                    'pct'    => (int) ($cached['pct'] ?? 0),
                    'speed'  => $cached['speed'] ?? null,
                    'eta'    => $cached['eta']   ?? null,
                    'error'  => $cached['error'] ?? null,
                ];

                // ── Heartbeat during silent ffmpeg phase ───────────────────
                if (
                    $payload['pct'] >= 90 && $payload['pct'] < 100
                    && $payload['status'] === 'downloading'
                ) {
                    $stuckAt90Since ??= time();
                    if (time() - $stuckAt90Since >= 3) {
                        $payload['pct']   = 95;
                        $payload['speed'] = null;
                        $payload['eta']   = 'Processing…';
                    }
                } else {
                    $stuckAt90Since = null;
                }

                // ── Send only changed frames ───────────────────────────────
                $frame = json_encode($payload);
                if ($frame !== $lastSent) {
                    echo "data: {$frame}

";
                    if (ob_get_level()) ob_flush();
                    flush();
                    $lastSent = $frame;
                }

                // ── Close immediately on terminal state ────────────────────
                if (in_array($payload['status'], ['done', 'failed'])) return;

                sleep(1);
            }

            // Window expired — send a keep-alive comment so the browser
            // knows the connection is healthy before it auto-reconnects.
            echo ": reconnecting

";
            if (ob_get_level()) ob_flush();
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
            'Retry'             => '800',   // tell browser to reconnect after 800ms
        ]);
    }

    public function serveFile(Request $request, string $uuid): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $download = Download::where('uuid', $uuid)->firstOrFail();

        if (
            $download->session_id !== $request->session()->getId()
            && $download->user_id !== $request->user()?->id
        ) {
            abort(403);
        }

        if ($download->status !== 'done' || !$download->filepath) {
            abort(404, 'File not ready.');
        }

        if (!file_exists($download->filepath)) {
            abort(410, 'File has been removed.');
        }

        return response()->download(
            $download->filepath,
            $download->filename ?? 'download.' . $download->format
        );
    }

    public function history(Request $request): JsonResponse
    {
        $downloads = Download::forSession($request->session()->getId())
            ->recent(20)
            ->get()
            ->map(fn($d) => [
                'uuid'         => $d->uuid,
                'url'          => $d->url,
                'title'        => $d->title,
                'thumbnail'    => $d->thumbnail,
                'format_label' => strtoupper($d->format === 'audio' ? 'MP3' : $d->format)
                    . ($d->format !== 'audio' ? ' ' . $d->resolution . 'p' : ''),
                'file_size'    => $d->filesize_human,
                'download_url' => $d->status === 'done'
                    ? route('download.file', $d->uuid)
                    : null,
                'status'       => $d->status,
                'error_message' => $d->error,
                'created_at'   => $d->created_at->diffForHumans(),
                'platform'     => $d->platform_label,
            ]);

        return response()->json($downloads);
    }

    public function deleteHistory(Request $request, string $uuid): JsonResponse
    {
        $download = Download::where('uuid', $uuid)->firstOrFail();

        if (
            $download->session_id !== $request->session()->getId()
            && $download->user_id !== $request->user()?->id
        ) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($download->filepath && file_exists($download->filepath)) {
            @unlink($download->filepath);
        }

        Cache::forget("dl_progress:{$download->uuid}");
        $download->delete();

        return response()->json(['deleted' => true]);
    }
}
