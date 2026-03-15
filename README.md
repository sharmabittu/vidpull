# VidPull — Commercial Video Downloader

A production-ready Laravel application for downloading videos **you own or have rights to**.  
Built on yt-dlp with a polished dark/light mode UI, real-time SSE progress, and full legal disclaimer flow.

---

## Features

- **Dark / Light mode** — system preference detection + manual toggle, persisted in localStorage
- **Legal disclaimer modal** — shown once per session, requires checkbox confirmation before use
- **Ownership reminder** — persistent top bar + in-card reminder on every URL input
- **Real-time progress** — Server-Sent Events stream with speed + ETA
- **Format & resolution selector** — MP4, WebM, MKV, Audio-only · 360p–4K
- **Download history** — persistent, with per-item delete
- **Commercial footer** — legal section, DMCA contact, terms link

---

## Quick Start

```bash
# 1. Install yt-dlp
sudo curl -L https://yt-dlp.org/downloads/latest/yt-dlp \
    -o /usr/local/bin/yt-dlp && sudo chmod +x /usr/local/bin/yt-dlp

# 2. Install Laravel deps
composer install
cp .env.example .env
php artisan key:generate

# 3. Configure DB in .env then:
php artisan migrate
php artisan storage:link
mkdir -p storage/app/downloads

# 4. Start queue worker
php artisan queue:work --queue=downloads --tries=2

# 5. Serve
php artisan serve
```

---

## Dark / Light Mode

The theme is controlled by an Alpine.js `themeManager()` on the `<html>` element.

- **Auto-detect**: uses `prefers-color-scheme` on first visit
- **Manual toggle**: sun/moon toggle in the nav, saved to `localStorage` as `vp_theme`
- **CSS variables**: all colors are defined as `--bg`, `--text`, `--brand`, etc. in `:root` (light) and `.dark` (dark). No Tailwind dark: prefixes needed for custom components — just use the variables.

Color roles per mode:

| Token           | Light         | Dark              |
|-----------------|---------------|-------------------|
| `--bg`          | `#ffffff`     | `#0a0a0a`         |
| `--surface`     | `#ffffff`     | `#141414`         |
| `--text`        | `#0a0a0a`     | `#f5f5f5`         |
| `--brand`       | `#DC2626`     | `#EF4444`         |
| `--brand-light` | `#FEE2E2`     | `rgba(239,68,68,.12)` |
| `--brand-text`  | `#7F1D1D`     | `#FCA5A5`         |

---

## Legal Disclaimer System

Three layers of protection:

1. **Top disclaimer bar** — always visible, links to the full legal section
2. **Session modal** — shown once per session, requires checkbox + button click to proceed
3. **In-card reminder** — shown inside the URL input card on every use

The modal stores acknowledgement in `sessionStorage` under `vp_ack`. Clear it to re-show.

Customize the legal text in:
- `resources/views/layouts/app.blade.php` → `#legal` section and disclaimer bar
- `resources/views/pages/home.blade.php` → disclaimer modal content

---

## Production

```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis   # Required for SSE to work across workers
```

### Supervisor

```ini
[program:vidpull-worker]
command=php /var/www/vidpull/artisan queue:work --queue=downloads --sleep=3 --tries=2 --timeout=3600
numprocs=3
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/www/vidpull/storage/logs/worker.log
```

### Nginx SSE config

```nginx
location /api/download/ {
    proxy_pass         http://127.0.0.1:8000;
    proxy_buffering    off;
    proxy_cache        off;
    proxy_read_timeout 3600s;
    proxy_set_header   X-Accel-Buffering no;
}
```

---

## Legal

VidPull is a tool for downloading content that users own or have rights to.  
Operators must display the provided disclaimer and comply with applicable copyright law.  
DMCA contact: legal@vidpull.com
