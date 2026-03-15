<x-layouts.app title="VidPull — Download Any Video">

{{-- ═══════════════════════════════════════════════════════════════
     HERO + URL INPUT (Alpine.js controller: "vidpull")
════════════════════════════════════════════════════════════════════ --}}
<div
    x-data="vidpull()"
    x-init="init()"
    class="pt-24"
>

    {{-- Notifications --}}
    <div class="max-w-3xl mx-auto px-4 space-y-2">
        <div
            x-show="notification.show"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'bg-emerald-950/80 border-emerald-500/30 text-emerald-300': notification.type === 'success',
                'bg-red-950/80 border-brand-600/40 text-red-300':           notification.type === 'error',
            }"
            class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm backdrop-blur-sm"
        >
            <span
                :class="{
                    'bg-emerald-500/20 text-emerald-400': notification.type === 'success',
                    'bg-red-500/20 text-red-400':         notification.type === 'error',
                }"
                class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                x-text="notification.type === 'success' ? '✓' : '✕'"
            ></span>
            <span class="flex-1" x-text="notification.message"></span>
            <button @click="notification.show = false" class="opacity-50 hover:opacity-100 transition-opacity">✕</button>
        </div>
    </div>

    {{-- ── Hero ── --}}
    <section class="relative hero-glow px-4 pt-10 pb-10 text-center overflow-hidden">

        {{-- Live badge --}}
        <div class="inline-flex items-center gap-2 bg-brand-600/10 border border-brand-600/25 text-brand-400 px-3 py-1 rounded-full text-xs font-semibold mb-6">
            <span class="w-1.5 h-1.5 bg-brand-500 rounded-full animate-pulse-dot"></span>
            Powered by yt-dlp · 1000+ sites
        </div>

        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight leading-[1.08] mb-5">
            Download Any Video,<br>
            <span class="text-brand-500">Instantly.</span>
        </h1>

        <p class="text-gray-500 text-base sm:text-lg max-w-md mx-auto mb-10 leading-relaxed">
            Paste a link from YouTube, Twitter, Instagram, Vimeo and more.
            Pick your format and quality — done.
        </p>

        {{-- ── URL Input Card ── --}}
        <div
            class="max-w-2xl mx-auto bg-gray-900/80 backdrop-blur border rounded-2xl p-5 transition-all duration-300"
            :class="isFocused ? 'border-brand-600/50 shadow-lg shadow-brand-600/10' : 'border-white/8'"
        >
            <div class="flex gap-2.5">
                <input
                    x-model="url"
                    @focus="isFocused = true"
                    @blur="isFocused = false"
                    @keydown.enter="fetchMeta()"
                    @paste="setTimeout(() => fetchMeta(), 80)"
                    type="url"
                    placeholder="https://youtube.com/watch?v=…"
                    class="url-input flex-1 min-w-0 bg-white/4 border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white placeholder-gray-600 outline-none transition-all focus:border-brand-600/50"
                    :class="{ 'border-red-600/50': error }"
                    :disabled="state === 'fetching'"
                />
                <button
                    @click="fetchMeta()"
                    :disabled="state === 'fetching' || !url.trim()"
                    class="flex items-center gap-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-5 py-3.5 rounded-xl text-sm font-bold transition-all hover:-translate-y-px active:translate-y-0 whitespace-nowrap"
                >
                    <span x-show="state !== 'fetching'">⚡ Fetch Info</span>
                    <span x-show="state === 'fetching'" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin-slow w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                        </svg>
                        Fetching…
                    </span>
                </button>
            </div>

            {{-- Loading bar --}}
            <div class="mt-3 h-0.5 bg-white/5 rounded-full overflow-hidden" x-show="state === 'fetching'" x-cloak>
                <div class="h-full bg-brand-600 rounded-full relative overflow-hidden shimmer-overlay" style="width: 85%"></div>
            </div>

            {{-- Supported platforms --}}
            <div class="mt-3 flex items-center gap-2 flex-wrap justify-center text-xs text-gray-700">
                <span>Supports:</span>
                @foreach(['YouTube','Twitter/X','Instagram','Vimeo','TikTok','Reddit','+1000 more'] as $p)
                    <span class="bg-white/4 border border-white/6 px-2 py-0.5 rounded font-mono text-gray-600">{{ $p }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ METADATA PREVIEW ═══ --}}
    <section
        x-show="state === 'ready' || state === 'downloading' || state === 'done'"
        x-cloak
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="max-w-2xl mx-auto px-4 mb-6"
    >
        <div class="bg-gray-900 border border-white/8 rounded-2xl overflow-hidden">

            {{-- Thumbnail + Meta row --}}
            <div class="flex flex-col sm:flex-row">

                {{-- Thumbnail --}}
                <div class="relative sm:w-52 flex-shrink-0 bg-gray-800">
                    <div class="aspect-video sm:aspect-auto sm:h-full">
                        <img
                            :src="meta.thumbnail"
                            alt="Thumbnail"
                            class="w-full h-full object-cover"
                            onerror="this.src=''"
                        />
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                        <div class="w-10 h-10 bg-brand-600 rounded-full flex items-center justify-center text-white text-sm shadow-lg">▶</div>
                    </div>
                    <span
                        x-text="meta.duration"
                        class="absolute bottom-2 right-2 bg-black/80 text-white text-xs font-mono px-1.5 py-0.5 rounded"
                    ></span>
                </div>

                {{-- Info --}}
                <div class="flex-1 p-4 flex flex-col gap-3 min-w-0">
                    <div>
                        <h2 class="text-sm font-bold leading-snug line-clamp-2 mb-2" x-text="meta.title"></h2>
                        <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                            <span class="bg-brand-600/15 border border-brand-600/30 text-brand-400 px-2 py-0.5 rounded font-bold text-[10px] tracking-wide" x-text="platformBadge"></span>
                            <span x-show="meta.uploader" x-text="'👤 ' + meta.uploader"></span>
                            <span x-show="meta.view_count" x-text="'👁 ' + meta.view_count + ' views'"></span>
                            <span x-show="meta.upload_date" x-text="'📅 ' + meta.upload_date"></span>
                        </div>
                    </div>

                    {{-- Format selector --}}
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1.5">Format</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="fmt in formats" :key="fmt.value">
                                <button
                                    @click="selectedFormat = fmt.value"
                                    :class="selectedFormat === fmt.value
                                        ? 'bg-brand-600/12 border-brand-600 text-white'
                                        : 'bg-white/4 border-white/10 text-gray-500 hover:border-brand-600/40 hover:text-gray-300'"
                                    class="border px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-150"
                                    x-text="fmt.label"
                                ></button>
                            </template>
                        </div>
                    </div>

                    {{-- Resolution selector --}}
                    <div x-show="selectedFormat !== 'audio'">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1.5">Resolution</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="res in resolutions" :key="res.value">
                                <button
                                    @click="selectedResolution = res.value"
                                    :class="selectedResolution === res.value
                                        ? 'bg-brand-600/12 border-brand-600 text-white'
                                        : 'bg-white/4 border-white/10 text-gray-500 hover:border-brand-600/40 hover:text-gray-300'"
                                    class="border px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-1"
                                >
                                    <span x-text="res.label"></span>
                                    <span
                                        x-show="res.badge"
                                        x-text="res.badge"
                                        class="text-[9px] bg-brand-600 text-white px-1 rounded font-bold"
                                    ></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Download CTA --}}
            <div class="px-4 pb-4 flex items-center gap-4 border-t border-white/5 pt-4">
                <button
                    @click="startDownload()"
                    :disabled="state === 'downloading'"
                    class="flex items-center gap-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl text-sm font-bold transition-all hover:-translate-y-px hover:shadow-lg hover:shadow-brand-600/30 active:translate-y-0"
                >
                    <span>⬇</span>
                    <span x-text="downloadBtnLabel"></span>
                </button>
                <p class="text-xs text-gray-600" x-text="dlEstimate"></p>
            </div>
        </div>
    </section>

    {{-- ═══ PROGRESS BAR ═══ --}}
    <section
        x-show="state === 'downloading'"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="max-w-2xl mx-auto px-4 mb-6"
    >
        <div class="bg-gray-900 border border-white/8 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <svg class="animate-spin-slow w-4 h-4 text-brand-500" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                    </svg>
                    <span class="text-sm font-semibold">Downloading…</span>
                </div>
                <span class="text-sm font-bold text-brand-500 font-mono" x-text="progress + '%'"></span>
            </div>

            {{-- Bar --}}
            <div class="h-1.5 bg-white/5 rounded-full overflow-hidden mb-2.5">
                <div
                    class="h-full bg-gradient-to-r from-brand-800 to-brand-500 rounded-full relative overflow-hidden shimmer-overlay transition-all duration-300 ease-out"
                    :style="`width: ${progress}%`"
                ></div>
            </div>

            <div class="flex justify-between text-xs text-gray-600 font-mono">
                <span x-text="speed || '—'"></span>
                <span x-text="eta ? 'ETA ' + eta : '—'"></span>
            </div>
        </div>
    </section>

    {{-- ═══ SUCCESS DOWNLOAD LINK ═══ --}}
    <section
        x-show="state === 'done' && downloadUuid"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="max-w-2xl mx-auto px-4 mb-6"
    >
        <div class="flex items-center gap-3 bg-emerald-950/60 border border-emerald-500/25 rounded-xl px-4 py-3 text-sm">
            <span class="w-6 h-6 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span>
            <span class="text-emerald-300 flex-1">Download complete!</span>
            <a
                :href="`/api/download/${downloadUuid}/file`"
                class="flex-shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors"
            >
                Save File ↓
            </a>
        </div>
    </section>

</div>{{-- end x-data --}}

{{-- ═══ DOWNLOAD HISTORY ═══ --}}
<div class="max-w-2xl mx-auto px-4 mt-4">
    <div class="h-px bg-white/5 mb-8"></div>
</div>

<x-history.index :downloads="$history" />


{{-- ═══════════════════════════════════════════════════════════════
     Alpine.js Component Logic
════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
function vidpull() {
    return {
        // ── State ──────────────────────────────────────────────────
        url:               '',
        state:             'idle', // idle | fetching | ready | downloading | done | error
        error:             null,
        isFocused:         false,

        // Metadata
        meta: {
            title:       '',
            thumbnail:   '',
            duration:    '',
            uploader:    '',
            view_count:  '',
            upload_date: '',
        },

        // Options
        selectedFormat:     'mp4',
        selectedResolution: '720p',

        formats: [
            { value: 'mp4',   label: 'MP4'         },
            { value: 'webm',  label: 'WebM'        },
            { value: 'mkv',   label: 'MKV'         },
            { value: 'audio', label: '🎵 Audio only'},
        ],

        resolutions: [
            { value: '360p',  label: '360p',  badge: ''    },
            { value: '480p',  label: '480p',  badge: ''    },
            { value: '720p',  label: '720p',  badge: 'HD'  },
            { value: '1080p', label: '1080p', badge: 'FHD' },
            { value: '4k',    label: '4K',    badge: 'UHD' },
        ],

        // Progress
        progress:     0,
        speed:        '',
        eta:          '',
        downloadUuid: null,
        sseSource:    null,

        // Notification
        notification: { show: false, type: 'success', message: '' },

        // ── Lifecycle ──────────────────────────────────────────────
        init() {
            // Auto-fetch if URL is pasted from clipboard via query param
            const u = new URLSearchParams(location.search).get('url');
            if (u) { this.url = u; this.fetchMeta(); }
        },

        // ── Computed ───────────────────────────────────────────────
        get platformBadge() {
            try {
                const host = new URL(this.url).hostname.replace('www.', '');
                const map  = {
                    'youtube.com':'YT','youtu.be':'YT',
                    'twitter.com':'X','x.com':'X',
                    'instagram.com':'IG','vimeo.com':'VI',
                    'tiktok.com':'TK','facebook.com':'FB',
                    'reddit.com':'RD','twitch.tv':'TW',
                };
                return map[host] ?? host.substring(0, 3).toUpperCase();
            } catch { return '??'; }
        },

        get downloadBtnLabel() {
            if (this.state === 'downloading') return 'Downloading…';
            const fmt = this.selectedFormat === 'audio'
                ? 'MP3 Audio'
                : `${this.selectedFormat.toUpperCase()} ${this.selectedResolution}`;
            return `Download · ${fmt}`;
        },

        get dlEstimate() {
            if (this.state === 'downloading') return '';
            const sizeMap = { '360p':'~60 MB','480p':'~100 MB','720p':'~150 MB','1080p':'~350 MB','4k':'~1.2 GB' };
            return this.selectedFormat === 'audio'
                ? '~5–20 MB · MP3 320kbps'
                : `${sizeMap[this.selectedResolution] ?? ''} estimated`;
        },

        // ── Methods ────────────────────────────────────────────────

        /** Step 1: Fetch video metadata */
        async fetchMeta() {
            const raw = this.url.trim();
            if (!raw) return;

            // Basic URL check
            try { new URL(raw); } catch {
                this.showNotification('error', 'Please enter a valid URL.');
                this.error = true;
                return;
            }

            this.state = 'fetching';
            this.error = null;
            this.meta  = {};

            try {
                const res  = await this.apiFetch('POST', '/api/fetch-meta', { url: raw });
                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.error ?? 'Could not fetch video info.');
                }

                this.meta  = json.meta;
                this.state = 'ready';

            } catch (err) {
                this.state = 'error';
                this.error = true;
                this.showNotification('error', err.message || 'Failed to fetch video info.');
            }
        },

        /** Step 2: Start download job */
        async startDownload() {
            this.state    = 'downloading';
            this.progress = 0;
            this.speed    = '';
            this.eta      = '';

            try {
                const res  = await this.apiFetch('POST', '/api/download', {
                    url:        this.url.trim(),
                    format:     this.selectedFormat,
                    resolution: this.selectedResolution,
                    title:      this.meta.title      ?? null,
                    thumbnail:  this.meta.thumbnail  ?? null,
                    duration:   this.meta.duration   ?? null,
                    uploader:   this.meta.uploader   ?? null,
                });
                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.error ?? 'Could not start download.');
                }

                this.downloadUuid = json.uuid;
                this.listenProgress(json.uuid);

            } catch (err) {
                this.state = 'error';
                this.showNotification('error', err.message || 'Download failed.');
            }
        },

        /** SSE progress listener */
        listenProgress(uuid) {
            if (this.sseSource) this.sseSource.close();

            this.sseSource = new EventSource(`/api/download/${uuid}/progress`);

            this.sseSource.onmessage = (e) => {
                const data = JSON.parse(e.data);

                this.progress = data.progress ?? this.progress;
                this.speed    = data.speed    ?? '';
                this.eta      = data.eta      ?? '';

                if (data.status === 'done') {
                    this.state = 'done';
                    this.progress = 100;
                    this.sseSource.close();
                    this.showNotification('success', 'Download complete! Click "Save File" to grab it.');
                    this.refreshHistory();
                }

                if (data.status === 'failed') {
                    this.state = 'error';
                    this.sseSource.close();
                    this.showNotification('error', 'Download failed. The video may be private or unavailable.');
                    this.refreshHistory();
                }
            };

            this.sseSource.onerror = () => {
                this.sseSource.close();
                if (this.state === 'downloading') {
                    // Retry after 2s if still running
                    setTimeout(() => this.listenProgress(uuid), 2000);
                }
            };
        },

        /** Reload history section via HTMX-style fetch */
        async refreshHistory() {
            try {
                const res  = await fetch('/?history_only=1');
                const html = await res.text();
                const doc  = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.getElementById('download-history-list');
                const current = document.getElementById('download-history-list');
                if (fresh && current) current.innerHTML = fresh.innerHTML;
            } catch { /* silent */ }
        },

        // ── Helpers ────────────────────────────────────────────────

        apiFetch(method, path, body = null) {
            return fetch(path, {
                method,
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name=csrf-token]').content,
                    'Accept':        'application/json',
                },
                body: body ? JSON.stringify(body) : undefined,
            });
        },

        showNotification(type, message, ttl = 5000) {
            this.notification = { show: true, type, message };
            if (ttl > 0) {
                setTimeout(() => { this.notification.show = false; }, ttl);
            }
        },
    };
}
</script>
@endpush

</x-layouts.app>
