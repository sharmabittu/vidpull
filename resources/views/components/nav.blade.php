{{-- ══════════════════════════════════════════════════════════════════════
     VidPull — Navigation
     Sticky · Scroll-aware · Dark/Light toggle switch · Responsive
══════════════════════════════════════════════════════════════════════ --}}

<style>
    /* ── Theme Toggle Switch ── */
    .theme-switch-wrap {
        display: flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        user-select: none;
    }

    .theme-switch-icon {
        font-size: 13px;
        line-height: 1;
        transition: opacity 0.2s;
        width: 16px;
        text-align: center;
    }

    .theme-switch {
        position: relative;
        width: 42px;
        height: 24px;
        flex-shrink: 0;
    }

    .theme-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }

    .theme-switch-track {
        position: absolute;
        inset: 0;
        border-radius: 99px;
        background: var(--switch-off, #e4e4e7);
        border: 1.5px solid var(--switch-border, rgba(0, 0, 0, 0.08));
        transition: background 0.22s, border-color 0.22s;
        cursor: pointer;
    }

    [data-theme="dark"] .theme-switch-track,
    .dark .theme-switch-track {
        background: #dc2626;
        border-color: rgba(255, 255, 255, 0.1);
    }

    .theme-switch-thumb {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.18);
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }

    [data-theme="dark"] .theme-switch-thumb,
    .dark .theme-switch-thumb {
        transform: translateX(18px);
    }

    /* ── Nav base ── */
    .vp-nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 50;
        border-bottom: 1px solid transparent;
        transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
    }

    .vp-nav.scrolled {
        border-color: var(--border);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    }

    [data-theme="light"] .vp-nav.scrolled,
    :root:not([data-theme="dark"]) .vp-nav.scrolled {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    [data-theme="dark"] .vp-nav.scrolled {
        background: rgba(10, 10, 10, 0.94);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-color: rgba(255, 255, 255, 0.06);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.4);
    }

    .vp-nav-inner {
        max-width: 720px;
        margin: 0 auto;
        padding: 0 20px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    /* ── Logo ── */
    .nav-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        flex-shrink: 0;
    }

    .nav-logo-icon {
        width: 30px;
        height: 30px;
        background: #dc2626;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

    .nav-logo:hover .nav-logo-icon {
        transform: scale(1.08);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4);
    }

    .nav-logo-text {
        font-family: 'Syne', sans-serif;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text-primary);
    }

    /* ── Nav Links ── */
    .nav-links {
        display: none;
        align-items: center;
        gap: 24px;
        font-size: 13px;
        font-weight: 500;
        font-family: 'DM Sans', sans-serif;
    }

    @media (min-width: 560px) {
        .nav-links {
            display: flex;
        }
    }

    .nav-links a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.18s;
    }

    .nav-links a:hover {
        color: #dc2626;
    }

    /* ── Right cluster ── */
    .nav-right {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-shrink: 0;
    }

    .status-pill {
        display: none;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-family: 'DM Mono', monospace;
        color: var(--text-muted);
        background: var(--bg-input);
        border: 1px solid var(--border);
        padding: 4px 9px;
        border-radius: 99px;
    }

    @media (min-width: 640px) {
        .status-pill {
            display: flex;
        }
    }

    .status-dot-green {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #22c55e;
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {

        0%,
        100% {
            opacity: 1;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
        }

        50% {
            opacity: 0.8;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0);
        }
    }
</style>

<nav
    class="vp-nav"
    id="vp-nav"
    x-data="{ scrolled: false }"
    x-on:scroll.window="scrolled = window.scrollY > 20"
    :class="{ 'scrolled': scrolled }">

    <div class="vp-nav-inner">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="nav-logo" aria-label="VidPull Home">
            <div class="nav-logo-icon">▼</div>
            <span class="nav-logo-text">VidPull</span>
        </a>

        {{-- Desktop Links --}}
        <nav class="nav-links" aria-label="Main navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="#history">History</a>
            <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" rel="noopener noreferrer">yt-dlp</a>
        </nav>

        {{-- Right: Status + Theme Switch --}}
        <div class="nav-right">

            {{-- Server status --}}
            <div class="status-pill">
                <span class="status-dot-green"></span>
                Online
            </div>

            {{-- Dark / Light toggle switch --}}
            <label
                class="theme-switch-wrap"
                title="Toggle dark/light mode"
                aria-label="Toggle dark/light mode">

                {{-- Sun icon --}}
                <span class="theme-switch-icon" aria-hidden="true"
                    style="opacity: var(--sun-opacity, 1)">☀️</span>

                <div class="theme-switch">
                    <input
                        type="checkbox"
                        id="theme-toggle-cb"
                        onchange="applyThemeFromCheckbox(this.checked)"
                        aria-label="Toggle dark mode" />
                    <span class="theme-switch-track"></span>
                    <span class="theme-switch-thumb"></span>
                </div>

                {{-- Moon icon --}}
                <span class="theme-switch-icon" aria-hidden="true">🌙</span>
            </label>

        </div>
    </div>
</nav>

<script>
    /* ── Theme bootstrap: run before paint to prevent flash ── */
    (function() {
        const saved = localStorage.getItem('vp-theme');
        const isDark = saved === 'dark'; /* light is always the default */
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        // Sync checkbox once DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const cb = document.getElementById('theme-toggle-cb');
            if (cb) cb.checked = isDark;
        });
    })();

    function applyThemeFromCheckbox(isDark) {
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        localStorage.setItem('vp-theme', isDark ? 'dark' : 'light');
    }
</script>