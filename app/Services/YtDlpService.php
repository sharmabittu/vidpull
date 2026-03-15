<?php

namespace App\Services;

use App\Models\Download;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class YtDlpService
{
    protected string $binary;
    protected string $storageDir;

    public function __construct()
    {
        $this->binary     = config('ytdlp.binary', env('YT_DLP_PATH', 'yt-dlp'));
        $this->storageDir = storage_path('app/downloads');

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Return --cookies args if a cookies file is configured and exists.
     * Set YT_DLP_COOKIES_FILE in .env to the absolute path of your cookies.txt
     */
    private function cookiesArgs(): array
    {
        $path = config('ytdlp.cookies_file', env('YT_DLP_COOKIES_FILE'));
        if ($path && file_exists($path)) {
            return ['--cookies', $path];
        }
        return [];
    }

    // ─── Metadata ────────────────────────────────────────────────────────────

    public function fetchMetadata(string $url): array
    {
        $process = new Process(array_merge(
            [$this->binary, '--dump-json', '--no-playlist', '--no-warnings', '--quiet'],
            $this->cookiesArgs(),
            [$url]
        ));
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'Could not fetch video info: ' . Str::limit($process->getErrorOutput(), 300)
            );
        }

        $json = json_decode($process->getOutput(), true);
        if (!$json) {
            throw new RuntimeException('yt-dlp returned invalid JSON.');
        }

        return [
            'title'       => $json['title']      ?? null,
            'thumbnail'   => $this->bestThumbnail($json['thumbnails'] ?? []) ?? ($json['thumbnail'] ?? null),
            'duration'    => $this->formatDuration($json['duration']  ?? 0),
            'platform'    => $json['extractor']  ?? null,
            'channel'     => $json['uploader']   ?? $json['channel'] ?? null,
            'views'       => $this->formatViews($json['view_count']   ?? null),
            'upload_year' => substr($json['upload_date'] ?? '????', 0, 4),
        ];
    }

    public function availableResolutions(string $url): array
    {
        $process = new Process(array_merge(
            [$this->binary, '--dump-json', '--no-playlist', '--no-warnings', '--quiet'],
            $this->cookiesArgs(),
            [$url]
        ));
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) return [360, 480, 720, 1080];

        $json = json_decode($process->getOutput(), true);
        if (empty($json['formats'])) return [360, 480, 720, 1080];

        $heights = array_filter(
            array_unique(array_column($json['formats'], 'height'))
        );
        sort($heights);
        return array_values($heights) ?: [360, 480, 720, 1080];
    }

    // ─── Download ─────────────────────────────────────────────────────────────

    /**
     * Download a video/audio, streaming progress to callback.
     *
     * Callback signature: fn(int $percent, string $speed, string $eta): void
     *
     * Returns absolute filepath on success.
     *
     * @throws RuntimeException
     */
    public function download(Download $download, callable $onProgress): string
    {
        $safeUuid    = preg_replace('/[^a-zA-Z0-9\-]/', '', $download->uuid);
        $outTemplate = $this->storageDir . '/' . $safeUuid . '.%(ext)s';
        $isAudio     = $download->format === 'audio';

        // ── Build command ──────────────────────────────────────────────────
        //
        //  AUDIO vs VIDEO need completely different flag sets.
        //
        //  AUDIO:
        //    -f bestaudio/best          → pick best audio stream
        //    -x                         → extract audio (no video)
        //    --audio-format mp3         → convert to mp3
        //    --audio-quality 0          → best VBR quality
        //    NO --merge-output-format   → that flag is for muxing video+audio,
        //                                 NOT for audio extraction. Using it with
        //                                 "mp3" causes: "invalid merge output format"
        //
        //  VIDEO:
        //    -f <format selector>       → pick best video + audio streams
        //    --merge-output-format mp4  → mux them into the container
        //    NO -x / --audio-format     → those would strip the video

        $cookies = $this->cookiesArgs();

        if ($isAudio) {
            $cmd = array_merge(
                [$this->binary, '--no-playlist', '--no-warnings'],
                $cookies,
                [
                    '-f',
                    'bestaudio/best',
                    '-x',
                    '--audio-format',
                    'mp3',
                    '--audio-quality',
                    '0',
                    '--newline',
                    '--progress-template',
                    'PROG|%(progress._percent_str)s|%(progress._speed_str)s|%(progress._eta_str)s',
                    '-o',
                    $outTemplate,
                    $download->url,
                ]
            );
        } else {
            $height       = (int) $download->resolution;
            $mergeFormat  = in_array($download->format, ['mp4', 'webm', 'mkv']) ? $download->format : 'mp4';
            $formatString = $this->buildVideoFormatString($download->format, $height);

            $cmd = array_merge(
                [$this->binary, '--no-playlist', '--no-warnings'],
                $cookies,
                [
                    '-f',
                    $formatString,
                    '--merge-output-format',
                    $mergeFormat,
                    '--newline',
                    '--progress-template',
                    'PROG|%(progress._percent_str)s|%(progress._speed_str)s|%(progress._eta_str)s',
                    '-o',
                    $outTemplate,
                    $download->url,
                ]
            );
        }

        // ── Run and parse progress ─────────────────────────────────────────
        $process     = new Process($cmd);
        $process->setTimeout(config('ytdlp.timeout', 3600));

        // ── Stream weighting ──────────────────────────────────────────────
        //
        // AUDIO (-x): 1 download stream + ffmpeg conversion
        //   Stream 0: raw 0→100 maps to overall 0→90%
        //   ffmpeg:   completion jumps to 100% (set on done)
        //
        // VIDEO: 2 download streams (video + audio) + ffmpeg mux
        //   Stream 0 (video): raw 0→100 maps to overall 0→45%
        //   Stream 1 (audio): raw 0→100 maps to overall 45→90%
        //   ffmpeg mux:       completion jumps to 100% (set on done)
        //
        // The bar NEVER goes backwards — if mapped value ≤ reportedPct, skip it.

        $streamIndex  = 0;
        $lastRawPct   = -1;
        $reportedPct  = 0;
        $streamRanges = $isAudio
            ? [[0, 90]]           // audio: single stream maps full range
            : [[0, 45], [45, 90]]; // video: two streams split the range

        $process->run(function (string $type, string $buffer) use (
            &$streamIndex,
            &$lastRawPct,
            &$reportedPct,
            $streamRanges,
            $onProgress
        ) {
            foreach (
                explode("
", $buffer) as $raw
            ) {
                $line = trim($raw);
                if ($line === '') continue;

                // Only process our tagged progress lines
                if (!str_starts_with($line, 'PROG|')) continue;

                $parts    = explode('|', $line);
                $rawPct   = trim($parts[1] ?? '');
                $rawSpeed = trim($parts[2] ?? '');
                $rawEta   = trim($parts[3] ?? '');

                // Strip % and spaces, bail if not numeric
                $stripped = str_replace(['%', ' '], '', $rawPct);
                if (!is_numeric($stripped)) continue;

                $pctFloat = (float) $stripped;
                $rawInt   = (int) round($pctFloat);
                $rawInt   = min(max($rawInt, 0), 100);

                // Detect stream reset: current raw% dropped significantly below
                // previous — this means yt-dlp moved to the next stream
                if ($lastRawPct > 5 && $rawInt < ($lastRawPct - 20)) {
                    $streamIndex = min($streamIndex + 1, count($streamRanges) - 1);
                }
                $lastRawPct = $rawInt;

                // Map raw 0–100 into this stream's weighted range
                [$rangeStart, $rangeEnd] = $streamRanges[$streamIndex] ?? [45, 90];
                $mapped = (int) round($rangeStart + ($rawInt / 100) * ($rangeEnd - $rangeStart));

                // Never go backwards
                if ($mapped <= $reportedPct) continue;
                $reportedPct = $mapped;

                // Clean display values
                $speed = ($rawSpeed === '' || str_contains(strtolower($rawSpeed), 'unknown') || $rawSpeed === 'NA')
                    ? '—' : $rawSpeed;
                $eta = ($rawEta === '' || str_contains(strtolower($rawEta), 'unknown') || $rawEta === 'NA')
                    ? '—' : $rawEta;

                $onProgress($reportedPct, $speed, $eta);
            }
        });

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'Download failed: ' . Str::limit($process->getErrorOutput(), 500)
            );
        }

        // ── Find output file ───────────────────────────────────────────────
        // yt-dlp writes the final extension itself — find whatever it created
        $files = glob($this->storageDir . '/' . $safeUuid . '.*');

        // Filter out temp files yt-dlp leaves behind (.part, .ytdl)
        $files = array_filter($files, fn($f) => !preg_match('/\.(part|ytdl|temp)$/i', $f));

        if (empty($files)) {
            throw new RuntimeException('yt-dlp finished but no output file was found.');
        }

        // If multiple files somehow exist, prefer the expected extension
        $expectedExt = $isAudio ? 'mp3' : $download->format;
        foreach ($files as $file) {
            if (str_ends_with($file, ".{$expectedExt}")) {
                return $file;
            }
        }

        // Fallback: return whatever was created
        return array_values($files)[0];
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Build the yt-dlp -f format string for video downloads.
     *
     * Examples produced:
     *   mp4  720p  → bestvideo[ext=mp4][height<=720]+bestaudio[ext=m4a]/bestvideo[height<=720]+bestaudio/best[height<=720]/best
     *   webm 1080p → bestvideo[ext=webm][height<=1080]+bestaudio[ext=webm]/bestvideo[height<=1080]+bestaudio/best
     *   mkv  2160p → bestvideo[height<=2160]+bestaudio/best[height<=2160]/best
     */
    private function buildVideoFormatString(string $format, int $height): string
    {
        return match ($format) {
            'mp4'  => "bestvideo[ext=mp4][height<={$height}]+bestaudio[ext=m4a]/bestvideo[height<={$height}]+bestaudio/best[height<={$height}]/best",
            'webm' => "bestvideo[ext=webm][height<={$height}]+bestaudio[ext=webm]/bestvideo[height<={$height}]+bestaudio/best[height<={$height}]/best",
            'mkv'  => "bestvideo[height<={$height}]+bestaudio/best[height<={$height}]/best",
            default => "bestvideo[height<={$height}]+bestaudio/best[height<={$height}]/best",
        };
    }

    protected function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) return '—';
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }

    protected function formatViews(?int $count): ?string
    {
        if ($count === null) return null;
        if ($count >= 1_000_000) return round($count / 1_000_000, 1) . 'M views';
        if ($count >= 1_000)     return round($count / 1_000,     1) . 'K views';
        return $count . ' views';
    }

    protected function bestThumbnail(array $thumbnails): ?string
    {
        if (empty($thumbnails)) return null;
        usort($thumbnails, fn($a, $b) => ($b['width'] ?? 0) <=> ($a['width'] ?? 0));
        foreach ($thumbnails as $t) {
            $w = $t['width'] ?? 0;
            if ($w <= 640 && $w >= 320) return $t['url'];
        }
        return $thumbnails[0]['url'] ?? null;
    }

    public function isAvailable(): bool
    {
        $p = new Process([$this->binary, '--version']);
        $p->run();
        return $p->isSuccessful();
    }
}
