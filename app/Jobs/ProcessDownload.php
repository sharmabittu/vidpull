<?php

namespace App\Jobs;

use App\Models\Download;
use App\Services\YtDlpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 1;

    public function __construct(protected Download $download) {}

    public function handle(YtDlpService $ytdlp): void
    {
        $dl       = $this->download;
        $cacheKey = "dl_progress:{$dl->uuid}";

        $dl->update(['status' => 'downloading', 'progress' => 0]);

        $this->writeCache($cacheKey, 'downloading', 0, null, null, null);

        try {
            $filepath = $ytdlp->download(
                $dl,
                function (int $pct, string $speed, string $eta) use ($dl, $cacheKey) {

                    // Update DB (for history/persistence)
                    $dl->update([
                        'progress' => $pct,
                        'speed'    => $speed !== '—' ? $speed : null,
                        'eta'      => $eta   !== '—' ? $eta   : null,
                    ]);

                    // Update Cache (for SSE — immediate, no DB lag)
                    $this->writeCache(
                        $cacheKey,
                        'downloading',
                        $pct,
                        $speed !== '—' ? $speed : null,
                        $eta   !== '—' ? $eta   : null,
                        null
                    );
                }
            );

            // ── Success ───────────────────────────────────────────────────
            $dl->update([
                'status'   => 'done',
                'progress' => 100,
                'filepath' => $filepath,
                'filename' => basename($filepath),
                'filesize' => file_exists($filepath) ? filesize($filepath) : null,
                'speed'    => null,
                'eta'      => null,
            ]);

            $this->writeCache($cacheKey, 'done', 100, null, null, null);
        } catch (Throwable $e) {
            Log::error("Download {$dl->uuid} failed: " . $e->getMessage());

            $dl->update(['status' => 'failed', 'error' => $e->getMessage()]);

            $this->writeCache($cacheKey, 'failed', 0, null, null, $e->getMessage());
        }
    }

    public function failed(Throwable $e): void
    {
        $this->download->update(['status' => 'failed', 'error' => $e->getMessage()]);

        Cache::put("dl_progress:{$this->download->uuid}", [
            'status' => 'failed',
            'pct'    => 0,
            'speed'  => null,
            'eta'    => null,
            'error'  => $e->getMessage(),
        ], now()->addMinutes(10));
    }

    // ── Helper: always write the same shape to cache ──────────────────────
    private function writeCache(
        string  $key,
        string  $status,
        int     $pct,
        ?string $speed,
        ?string $eta,
        ?string $error
    ): void {
        Cache::put($key, [
            'status' => $status,
            'pct'    => $pct,     // ← always "pct" — matches SSE + frontend
            'speed'  => $speed,
            'eta'    => $eta,
            'error'  => $error,
        ], now()->addMinutes(60));
    }
}
