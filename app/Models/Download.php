<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Download extends Model
{
    protected $fillable = [
        'uuid','url','title','thumbnail','duration','platform','channel',
        'views','upload_year','format','resolution','filesize','filepath',
        'filename','status','progress','speed','eta','error','session_id','user_id',
    ];

    protected $casts = [
        'filesize' => 'integer',
        'progress' => 'integer',
    ];

    // ─── Boot ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    /** Human-readable filesize e.g. "142 MB" */
    public function getFilesizeHumanAttribute(): string
    {
        if (!$this->filesize) return '—';
        $units = ['B','KB','MB','GB'];
        $size  = $this->filesize;
        $i     = 0;
        while ($size >= 1024 && $i < 3) { $size /= 1024; $i++; }
        return round($size, 1) . ' ' . $units[$i];
    }

    /** Detect platform label from URL */
    public function getPlatformLabelAttribute(): string
    {
        return match(true) {
            str_contains($this->url, 'youtube.com') || str_contains($this->url, 'youtu.be') => 'YouTube',
            str_contains($this->url, 'twitter.com') || str_contains($this->url, 'x.com')    => 'Twitter/X',
            str_contains($this->url, 'instagram.com')                                        => 'Instagram',
            str_contains($this->url, 'vimeo.com')                                            => 'Vimeo',
            str_contains($this->url, 'tiktok.com')                                           => 'TikTok',
            str_contains($this->url, 'facebook.com') || str_contains($this->url, 'fb.watch') => 'Facebook',
            default => $this->platform ?? 'Video',
        };
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeRecent($query, int $limit = 20)
    {
        return $query->latest()->limit($limit);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isDone(): bool   { return $this->status === 'done'; }
    public function isFailed(): bool { return $this->status === 'failed'; }
    public function isReady(): bool  { return $this->status === 'ready'; }

    /**
     * Build yt-dlp -f format selector string.
     */
    public function ytdlpFormatString(): string
    {
        if ($this->format === 'audio') {
            return 'bestaudio/best';
        }
        $ext = $this->format;    // mp4 | webm | mkv
        $res = $this->resolution; // 360 | 480 | 720 | 1080 | 2160

        return "bestvideo[height<={$res}][ext={$ext}]+bestaudio[ext=m4a]"
             . "/bestvideo[height<={$res}]+bestaudio"
             . "/best[height<={$res}]"
             . "/best";
    }
}
