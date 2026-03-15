@extends('layouts.app')

@section('meta')
<title>VidPull — Free Online Video Downloader | YouTube, Instagram, TikTok & More</title>
<meta name="description" content="Download videos from YouTube, Instagram, TikTok, Twitter/X, Vimeo and 1000+ sites for free. No software, no signup. Pick MP4, WebM or MP3 audio.">
<meta name="keywords" content="video downloader, youtube downloader, instagram video download, tiktok downloader, free video download, mp4 downloader, online video downloader">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:title" content="VidPull — Free Online Video Downloader">
<meta property="og:description" content="Download videos from YouTube, Instagram, TikTok, Twitter/X, Vimeo and 1000+ sites. Free, fast, no signup.">
<meta property="og:image" content="{{ asset('images/og-vidpull.png') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="VidPull — Free Online Video Downloader">
<meta name="twitter:description" content="Paste a URL. Pick your quality. Download instantly from 1000+ platforms.">
<meta name="twitter:image" content="{{ asset('images/og-vidpull.png') }}">
<script type="application/ld+json">
    @php
    json_encode([
        "@context" => "https://schema.org",
        "@type" => "WebApplication",
        "name" => "VidPull",
        "url" => url('/'),
        "description" => "Free online video downloader for YouTube, Instagram, TikTok, Twitter/X, Vimeo and 1000+ platforms.",
        "applicationCategory" => "MultimediaApplication",
        "operatingSystem" => "Any",
        "offers" => [
            "@type" => "Offer",
            "price" => "0",
            "priceCurrency" => "USD",
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    @endphp
</script>

@endsection

@section('title', 'VidPull — Free Online Video Downloader')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap');

    /* ─────────────────────────────────────────────────────────────────
   RESET
───────────────────────────────────────────────────────────────── */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    /* ─────────────────────────────────────────────────────────────────
   LIGHT THEME  (default — always)
───────────────────────────────────────────────────────────────── */
    :root {
        --bg: #f6f6f7;
        --bg-card: #ffffff;
        --bg-input: #f3f4f6;
        --bg-hover: #ebebec;
        --border: #e2e2e5;
        --border-strong: #d1d1d6;
        --text-h: #111113;
        --text-body: #3f3f46;
        --text-soft: #71717a;
        --text-muted: #a1a1aa;
        --red: #dc2626;
        --red-dark: #b91c1c;
        --red-light: #fef2f2;
        --red-border: #fecaca;
        --green: #16a34a;
        --yellow-bg: #fffbeb;
        --yellow-border: #fde68a;
        --yellow-text: #92400e;
        --blue-bg: #eff6ff;
        --blue-border: #bfdbfe;
        --blue-text: #1e40af;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 1px 3px rgba(0, 0, 0, 0.07), 0 4px 12px rgba(0, 0, 0, 0.05);
        --shadow-lg: 0 2px 8px rgba(0, 0, 0, 0.06), 0 8px 24px rgba(0, 0, 0, 0.06);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 18px;
        --radius-xl: 22px;
    }

    /* ─────────────────────────────────────────────────────────────────
   DARK THEME  (only when user explicitly toggled)
───────────────────────────────────────────────────────────────── */
    [data-theme="dark"] {
        --bg: #0f0f10;
        --bg-card: #1a1a1c;
        --bg-input: #242426;
        --bg-hover: #2a2a2d;
        --border: rgba(255, 255, 255, 0.08);
        --border-strong: rgba(255, 255, 255, 0.13);
        --text-h: #f4f4f5;
        --text-body: #a1a1aa;
        --text-soft: #71717a;
        --text-muted: #52525b;
        --red-light: rgba(220, 38, 38, 0.1);
        --red-border: rgba(220, 38, 38, 0.25);
        --yellow-bg: rgba(251, 191, 36, 0.07);
        --yellow-border: rgba(251, 191, 36, 0.2);
        --yellow-text: #fcd34d;
        --blue-bg: rgba(59, 130, 246, 0.07);
        --blue-border: rgba(59, 130, 246, 0.2);
        --blue-text: #93c5fd;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
        --shadow-md: 0 1px 3px rgba(0, 0, 0, 0.4), 0 4px 12px rgba(0, 0, 0, 0.3);
        --shadow-lg: 0 2px 8px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    /* ─────────────────────────────────────────────────────────────────
   BASE
───────────────────────────────────────────────────────────────── */
    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 15px;
        line-height: 1.6;
        background-color: var(--bg) !important;
        color: var(--text-body);
        -webkit-font-smoothing: antialiased;
        transition: background-color 0.2s, color 0.2s;
    }

    /* Force override any Tailwind bg-gray-950 etc on parent wrappers */
    .bg-gray-950,
    .bg-gray-900,
    .bg-black,
    .dark\:bg-gray-900 {
        background-color: var(--bg) !important;
    }

    /* ─────────────────────────────────────────────────────────────────
   PAGE WRAPPER
───────────────────────────────────────────────────────────────── */
    .vp-wrap {
        max-width: 680px;
        margin: 0 auto;
        padding: 80px 20px 80px;
    }

    @media (max-width: 640px) {
        .vp-wrap {
            padding: 72px 16px 60px;
        }
    }

    /* ─────────────────────────────────────────────────────────────────
   AD SLOTS
───────────────────────────────────────────────────────────────── */
    .ad-slot {
        width: 100%;
        background: var(--bg-card);
        border: 1.5px dashed var(--border-strong);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .ad-top {
        height: 90px;
        margin-bottom: 32px;
    }

    .ad-mid {
        height: 250px;
        margin-top: 32px;
        margin-bottom: 0;
    }

    .ad-bottom {
        height: 90px;
        margin-top: 40px;
    }

    /* ─────────────────────────────────────────────────────────────────
   HERO
───────────────────────────────────────────────────────────────── */
    .hero {
        text-align: center;
        padding: 8px 0 32px;
    }

    .hero h1 {
        font-family: 'Syne', sans-serif;
        font-size: clamp(28px, 5.5vw, 48px);
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -0.025em;
        color: var(--text-h);
        margin-bottom: 14px;
    }

    .hero h1 em {
        font-style: normal;
        color: var(--red);
    }

    .hero-sub {
        font-size: 15px;
        color: var(--text-soft);
        max-width: 400px;
        margin: 0 auto;
        line-height: 1.7;
    }

    /* ─────────────────────────────────────────────────────────────────
   DISCLAIMER
───────────────────────────────────────────────────────────────── */
    .disclaimer {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        background: var(--yellow-bg);
        border: 1px solid var(--yellow-border);
        color: var(--yellow-text);
        border-radius: var(--radius-md);
        padding: 12px 16px;
        font-size: 13px;
        line-height: 1.55;
        margin-bottom: 24px;
    }

    .disclaimer-icon {
        flex-shrink: 0;
        font-size: 15px;
        margin-top: 1px;
    }

    /* ─────────────────────────────────────────────────────────────────
   CARD  (shared)
───────────────────────────────────────────────────────────────── */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        margin-bottom: 14px;
        transition: border-color 0.18s, box-shadow 0.18s;
        overflow: hidden;
    }

    .card-body {
        padding: 20px;
    }

    /* ─────────────────────────────────────────────────────────────────
   URL INPUT SECTION
───────────────────────────────────────────────────────────────── */
    .card.focused {
        border-color: var(--red);
        box-shadow: var(--shadow-md), 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .input-row {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
    }

    @media (max-width: 500px) {
        .input-row {
            flex-direction: column;
        }
    }

    .url-input {
        flex: 1;
        min-width: 0;
        background: var(--bg-input);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 13px 16px;
        font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-h);
        outline: none;
        transition: border-color 0.18s, box-shadow 0.18s;
    }

    .url-input::placeholder {
        color: var(--text-muted);
    }

    .url-input:focus {
        border-color: var(--red);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        background: var(--bg-card);
    }

    .fetch-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--red);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: 13px 22px;
        font-size: 14px;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
    }

    .fetch-btn:hover:not(:disabled) {
        background: var(--red-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.28);
    }

    .fetch-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .fetch-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* Platform chips */
    .platform-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
    }

    .platform-label {
        font-size: 11.5px;
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
        margin-right: 2px;
    }

    .p-chip {
        font-size: 11px;
        font-family: 'DM Mono', monospace;
        background: var(--bg-input);
        border: 1px solid var(--border);
        color: var(--text-soft);
        padding: 3px 8px;
        border-radius: 5px;
    }

    /* Fetch progress bar */
    .fetch-progress {
        margin-top: 14px;
        height: 3px;
        background: var(--bg-input);
        border-radius: 99px;
        overflow: hidden;
    }

    .fetch-bar {
        height: 100%;
        background: var(--red);
        border-radius: 99px;
        width: 0%;
        animation: fbar 2.5s ease-in-out forwards;
    }

    @keyframes fbar {
        0% {
            width: 0%
        }

        60% {
            width: 75%
        }

        100% {
            width: 92%
        }
    }

    /* Spinner */
    .spin {
        width: 15px;
        height: 15px;
        border: 2.5px solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: rot 0.65s linear infinite;
        flex-shrink: 0;
    }

    @keyframes rot {
        to {
            transform: rotate(360deg);
        }
    }

    /* ─────────────────────────────────────────────────────────────────
   NOTIFICATIONS
───────────────────────────────────────────────────────────────── */
    .notif {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 13px 16px;
        border-radius: var(--radius-md);
        font-size: 13.5px;
        line-height: 1.5;
        margin-bottom: 14px;
        border: 1px solid transparent;
        animation: fadeSlide 0.2s ease;
    }

    .notif.success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: var(--green);
    }

    .notif.error {
        background: var(--red-light);
        border-color: var(--red-border);
        color: var(--red-dark);
    }

    .notif.info {
        background: var(--blue-bg);
        border-color: var(--blue-border);
        color: var(--blue-text);
    }

    [data-theme="dark"] .notif.success {
        background: rgba(22, 163, 74, 0.08);
        border-color: rgba(22, 163, 74, 0.2);
        color: #4ade80;
    }

    [data-theme="dark"] .notif.error {
        color: #f87171;
    }

    [data-theme="dark"] .notif.info {
        color: #93c5fd;
    }

    .notif-body {
        flex: 1;
    }

    .notif-close {
        margin-left: auto;
        background: none;
        border: none;
        cursor: pointer;
        opacity: 0.45;
        font-size: 16px;
        color: inherit;
        padding: 0;
        flex-shrink: 0;
        line-height: 1;
    }

    .notif-close:hover {
        opacity: 1;
    }

    /* ─────────────────────────────────────────────────────────────────
   DOWNLOAD PROGRESS CARD
───────────────────────────────────────────────────────────────── */
    .dl-card {
        padding: 20px;
    }

    .dl-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .dl-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-h);
    }

    .dl-pct {
        font-family: 'DM Mono', monospace;
        font-size: 15px;
        font-weight: 600;
        color: var(--red);
    }

    .dl-track {
        height: 8px;
        background: var(--bg-input);
        border-radius: 99px;
        overflow: hidden;
        margin-bottom: 10px;
        position: relative;
    }

    .dl-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--red-dark), var(--red));
        border-radius: 99px;
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        /* width driven entirely by Alpine :style — no CSS default */
    }

    .dl-fill::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.25) 50%, transparent 100%);
        animation: shimmer 1.6s infinite;
    }

    @keyframes shimmer {
        from {
            transform: translateX(-100%)
        }

        to {
            transform: translateX(200%)
        }
    }

    .dl-meta {
        display: flex;
        justify-content: space-between;
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: var(--text-muted);
    }

    /* ─────────────────────────────────────────────────────────────────
   VIDEO META PREVIEW CARD
───────────────────────────────────────────────────────────────── */
    .meta-layout {
        display: flex;
    }

    @media (max-width: 520px) {
        .meta-layout {
            flex-direction: column;
        }
    }

    /* Thumbnail */
    .thumb-box {
        position: relative;
        background: #111;
        flex-shrink: 0;
    }

    @media (min-width: 521px) {
        .thumb-box {
            width: 196px;
        }
    }

    .thumb-img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        display: block;
    }

    .thumb-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.22);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .play-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--red);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 13px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        transition: transform 0.18s;
    }

    .thumb-overlay:hover .play-circle {
        transform: scale(1.1);
    }

    .thumb-dur {
        position: absolute;
        bottom: 7px;
        right: 7px;
        background: rgba(0, 0, 0, 0.78);
        color: #fff;
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
    }

    /* Info panel */
    .meta-info {
        flex: 1;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-width: 0;
    }

    .meta-title {
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-h);
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .meta-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        font-size: 12px;
        color: var(--text-soft);
    }

    .plat-badge {
        background: var(--red-light);
        color: var(--red-dark);
        font-size: 9px;
        font-weight: 800;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.08em;
        padding: 3px 7px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    [data-theme="dark"] .plat-badge {
        background: rgba(220, 38, 38, 0.12);
        color: #fca5a5;
    }

    /* Selector labels */
    .sel-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
        margin-bottom: 7px;
    }

    .pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .pill {
        border: 1.5px solid var(--border);
        background: var(--bg-input);
        color: var(--text-soft);
        padding: 6px 12px;
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .pill:hover:not(.active) {
        border-color: rgba(220, 38, 38, 0.35);
        color: var(--text-h);
        background: var(--bg-hover);
    }

    .pill.active {
        border-color: var(--red);
        background: var(--red-light);
        color: var(--red-dark);
    }

    [data-theme="dark"] .pill.active {
        background: rgba(220, 38, 38, 0.1);
        color: #fca5a5;
    }

    .res-tag {
        background: var(--red);
        color: #fff;
        font-size: 8px;
        font-weight: 800;
        padding: 2px 4px;
        border-radius: 3px;
        font-family: 'DM Mono', monospace;
    }

    /* CTA row */
    .cta-row {
        border-top: 1px solid var(--border);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .dl-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--red);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: 12px 22px;
        font-size: 13px;
        font-weight: 700;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
    }

    .dl-btn:hover:not(:disabled) {
        background: var(--red-dark);
        transform: translateY(-1px);
        box-shadow: 0 5px 18px rgba(220, 38, 38, 0.25);
    }

    .dl-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .cta-note {
        font-size: 11px;
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
    }

    /* ─────────────────────────────────────────────────────────────────
   HISTORY SECTION
───────────────────────────────────────────────────────────────── */
    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .section-title {
        font-family: 'Syne', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-h);
        letter-spacing: -0.01em;
    }

    .clear-all {
        font-size: 12px;
        font-family: 'DM Sans', sans-serif;
        font-weight: 500;
        color: var(--text-muted);
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 9px;
        border-radius: 6px;
        transition: all 0.15s;
    }

    .clear-all:hover {
        color: var(--red);
        background: var(--red-light);
    }

    .empty-box {
        text-align: center;
        padding: 48px 20px;
    }

    .empty-icon {
        font-size: 38px;
        margin-bottom: 12px;
        opacity: 0.25;
    }

    .empty-txt {
        font-size: 13px;
        color: var(--text-muted);
    }

    .hist-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .hist-item {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 11px;
        box-shadow: var(--shadow-sm);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .hist-item:hover {
        border-color: rgba(220, 38, 38, 0.15);
        box-shadow: var(--shadow-md);
    }

    .s-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .s-dot.done {
        background: #22c55e;
    }

    .s-dot.failed {
        background: var(--red);
    }

    .s-dot.pending {
        background: #f59e0b;
        animation: blink 1.4s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: 0.3
        }
    }

    .hist-thumb {
        width: 60px;
        height: 38px;
        object-fit: cover;
        border-radius: 6px;
        background: var(--bg-input);
        flex-shrink: 0;
    }

    .hist-text {
        flex: 1;
        min-width: 0;
    }

    .hist-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-h);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 3px;
    }

    .hist-sub {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 11px;
        font-family: 'DM Mono', monospace;
        color: var(--text-muted);
    }

    .hist-err {
        color: #ef4444;
    }

    .hist-btns {
        display: flex;
        gap: 5px;
        flex-shrink: 0;
        opacity: 0;
        transition: opacity 0.15s;
    }

    .hist-item:hover .hist-btns {
        opacity: 1;
    }

    @media (max-width: 500px) {
        .hist-btns {
            opacity: 1;
        }
    }

    .hbtn {
        border-radius: 7px;
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s;
    }

    .hbtn-save {
        background: var(--red-light);
        border: 1px solid var(--red-border);
        color: var(--red-dark);
    }

    [data-theme="dark"] .hbtn-save {
        background: rgba(220, 38, 38, 0.08);
        color: #fca5a5;
    }

    .hbtn-save:hover {
        background: #fee2e2;
    }

    .hbtn-re {
        background: var(--bg-input);
        border: 1px solid var(--border);
        color: var(--text-soft);
    }

    .hbtn-re:hover {
        color: var(--text-h);
    }

    .hbtn-del {
        background: var(--bg-input);
        border: 1px solid var(--border);
        color: var(--text-muted);
    }

    .hbtn-del:hover {
        border-color: rgba(220, 38, 38, 0.3);
        color: var(--red);
    }

    /* ─────────────────────────────────────────────────────────────────
   SEO CONTENT BLOCK
───────────────────────────────────────────────────────────────── */
    .seo-block {
        margin-top: 56px;
        padding: 36px 32px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
    }

    @media (max-width: 500px) {
        .seo-block {
            padding: 24px 18px;
        }
    }

    .seo-block h2 {
        font-family: 'Syne', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-h);
        margin-bottom: 10px;
        letter-spacing: -0.01em;
    }

    .seo-block p {
        font-size: 14px;
        color: var(--text-soft);
        line-height: 1.75;
        margin-bottom: 14px;
    }

    .seo-block a {
        color: var(--red);
        text-decoration: none;
    }

    .seo-block a:hover {
        text-decoration: underline;
    }

    .seo-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 16px;
    }

    .seo-tag {
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 5px 12px;
        font-size: 12.5px;
        font-weight: 500;
        color: var(--text-soft);
        font-family: 'DM Sans', sans-serif;
    }

    /* ─────────────────────────────────────────────────────────────────
   FOOTER
───────────────────────────────────────────────────────────────── */
    .vp-footer {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        font-family: 'DM Mono', monospace;
        color: var(--text-muted);
    }

    .vp-footer a {
        color: var(--text-muted);
        text-decoration: none;
        margin-right: 12px;
    }

    .vp-footer a:hover {
        color: var(--red);
    }

    /* ─────────────────────────────────────────────────────────────────
   ANIMATIONS
───────────────────────────────────────────────────────────────── */
    @keyframes fadeSlide {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeSlide 0.25s ease;
    }

    [x-cloak] {
        display: none !important;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
    }
</style>

{{-- ══════════════════════════════════════════════
     Alpine component
══════════════════════════════════════════════ --}}
<div x-data="vidpull()" x-init="loadHistory()" class="vp-wrap">

    {{-- ── AD: Top ── --}}
    <div class="ad-slot ad-top" aria-label="Advertisement"><!-- Ad: 728×90 --></div>

    {{-- ── HERO ── --}}
    <header class="hero">
        <h1>Download Any Video,<br><em>Instantly Free.</em></h1>
        <p class="hero-sub">Paste a link from YouTube, Twitter/X, Instagram, TikTok, Vimeo or 1000+ other platforms. Choose your format and quality — no signup needed.</p>
    </header>

    {{-- ── DISCLAIMER ── --}}
    <div class="disclaimer" role="note">
        <span class="disclaimer-icon">⚠️</span>
        <span><strong>Use responsibly.</strong> Only download videos you own or have explicit permission to download. Downloading copyrighted content without authorization may violate copyright law and the platform's terms of service. VidPull is not responsible for misuse of downloaded content.</span>
    </div>

    {{-- ── NOTIFICATION ── --}}
    <template x-if="notification.show">
        <div class="notif fade-in" :class="notification.type" role="alert">
            <span style="font-size:15px;flex-shrink:0;margin-top:1px" x-text="notification.icon"></span>
            <span class="notif-body" x-html="notification.message"></span>
            <button class="notif-close" @click="notification.show=false" aria-label="Close">✕</button>
        </div>
    </template>

    {{-- ── URL INPUT CARD ── --}}
    <div class="card" :class="focused ? 'focused' : ''">
        <div class="card-body">
            <div class="input-row">
                <label for="vp-url" class="sr-only">Video URL</label>
                <input
                    id="vp-url"
                    type="url"
                    x-model="url"
                    @focus="focused=true"
                    @blur="focused=false"
                    @keydown.enter="fetchMeta()"
                    @paste="onPaste()"
                    placeholder="Paste video URL here — https://youtube.com/watch?v=..."
                    autocomplete="off" spellcheck="false"
                    class="url-input" />
                <button @click="fetchMeta()" :disabled="fetching" class="fetch-btn">
                    <template x-if="!fetching">
                        <span style="display:flex;align-items:center;gap:6px">⚡ <span>Fetch</span></span>
                    </template>
                    <template x-if="fetching">
                        <span style="display:flex;align-items:center;gap:7px">
                            <span class="spin"></span><span>Fetching…</span>
                        </span>
                    </template>
                </button>
            </div>

            <div class="platform-row">
                <span class="platform-label">Works with:</span>
                @foreach(['YouTube','Twitter/X','Instagram','TikTok','Vimeo','Facebook','Reddit','+1000 more'] as $p)
                <span class="p-chip">{{ $p }}</span>
                @endforeach
            </div>

            <div x-show="fetching" x-cloak class="fetch-progress">
                <div class="fetch-bar"></div>
            </div>
        </div>
    </div>

    {{-- ── DOWNLOAD PROGRESS ── --}}
    <div
        x-show="downloading"
        x-cloak
        class="card"
        style="margin-bottom:14px"
        role="progressbar"
        :aria-valuenow="progress.pct"
        aria-valuemin="0"
        aria-valuemax="100">
        <div class="dl-card">
            <div class="dl-top">
                <span class="dl-label">Downloading…</span>
                <span class="dl-pct" x-text="progress.pct + '%'">0%</span>
            </div>
            <div class="dl-track">
                <div class="dl-fill" :style="{ width: progress.pct + '%' }"></div>
            </div>
            <div class="dl-meta">
                <span x-text="progress.speed || '—'"></span>
                <span x-text="progress.pct >= 100 ? 'Complete!' : 'ETA: ' + (progress.eta || '—')"></span>
            </div>
        </div>
    </div>

    {{-- ── VIDEO META PREVIEW ── --}}
    <template x-if="meta">
        <div class="card fade-in">
            <div class="meta-layout">
                <div class="thumb-box">
                    <img class="thumb-img"
                        :src="meta.thumbnail"
                        :alt="'Thumbnail — ' + meta.title"
                        onerror="this.src='https://placehold.co/196x110/f3f4f6/a1a1aa?text=No+Preview'" />
                    <div class="thumb-overlay">
                        <div class="play-circle">▶</div>
                    </div>
                    <div class="thumb-dur" x-text="meta.duration">0:00</div>
                </div>

                <div class="meta-info">
                    <div>
                        <h2 class="meta-title" x-text="meta.title"></h2>
                        <div class="meta-tags" style="margin-top:8px">
                            <span class="plat-badge" x-text="meta.platform?.substring(0,2).toUpperCase()"></span>
                            <span x-show="meta.channel">👤 <span x-text="meta.channel"></span></span>
                            <span x-show="meta.view_count">👁 <span x-text="meta.view_count"></span></span>
                        </div>
                    </div>

                    <div>
                        <div class="sel-label">Format</div>
                        <div class="pill-row">
                            <template x-for="fmt in ['mp4','webm','mkv','audio']" :key="fmt">
                                <button @click="selectedFormat=fmt"
                                    :class="selectedFormat===fmt ? 'pill active' : 'pill'"
                                    x-text="fmt==='audio' ? '🎵 Audio only' : fmt.toUpperCase()">
                                </button>
                            </template>
                        </div>

                        <template x-if="selectedFormat !== 'audio'">
                            <div>
                                <div class="sel-label">Resolution</div>
                                <div class="pill-row" style="margin-bottom:0">
                                    <template x-for="res in ['360','480','720','1080','2160']" :key="res">
                                        <button @click="selectedRes=res"
                                            :class="selectedRes===res ? 'pill active' : 'pill'">
                                            <span x-text="res+'p'"></span>
                                            <template x-if="res==='720'||res==='1080'||res==='2160'">
                                                <span class="res-tag" x-text="res==='2160'?'4K':res==='1080'?'FHD':'HD'"></span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="cta-row">
                <button @click="startDownload()" :disabled="downloading" class="dl-btn">
                    ⬇ Download ·
                    <span x-text="selectedFormat==='audio' ? 'MP3 Audio' : selectedFormat.toUpperCase()+' '+selectedRes+'p'"></span>
                </button>
            </div>
        </div>
    </template>

    {{-- ── AD: Mid (shown when idle) ── --}}
    <template x-if="!meta && !downloading">
        <div class="ad-slot ad-mid" aria-label="Advertisement"><!-- Ad: 300×250 --></div>
    </template>

    {{-- ── HISTORY ── --}}
    <section style="margin-top:44px" id="history" aria-label="Download history">
        <div class="section-head">
            <h2 class="section-title">Recent Downloads</h2>
            <button @click="clearHistory()" x-show="history.length>0" class="clear-all">Clear all</button>
        </div>

        <template x-if="history.length===0">
            <div class="empty-box">
                <div class="empty-icon">⬇</div>
                <p class="empty-txt">No downloads yet — paste a URL above to get started.</p>
            </div>
        </template>

        <div class="hist-list" role="list">
            <template x-for="item in history" :key="item.uuid">
                <div class="hist-item" role="listitem">
                    <div class="s-dot" :class="item.status==='done'?'done':item.status==='failed'?'failed':'pending'"></div>
                    <img class="hist-thumb"
                        :src="item.thumbnail||'https://placehold.co/60x38/f3f4f6/a1a1aa?text=?'"
                        :alt="item.title"
                        onerror="this.src='https://placehold.co/60x38/f3f4f6/a1a1aa?text=?'" />
                    <div class="hist-text">
                        <div class="hist-title" x-text="item.title||'Untitled'"></div>
                        <div class="hist-sub">
                            <span x-text="item.format_label"></span>
                            <span x-show="item.file_size" x-text="item.file_size"></span>
                            <span x-text="item.created_at"></span>
                            <span x-show="item.status==='failed'" class="hist-err"
                                x-text="'Failed · '+(item.error_message||'Unknown error')"></span>
                        </div>
                    </div>
                    <div class="hist-btns">
                        <a x-show="item.status==='done'&&item.download_url"
                            :href="item.download_url" class="hbtn hbtn-save">↓ Save</a>
                        <button @click="refetch(item)" class="hbtn hbtn-re">↺ Re-dl</button>
                        <button @click="deleteItem(item.uuid)" class="hbtn hbtn-del">✕</button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    {{-- ── AD: Bottom ── --}}
    <div class="ad-slot ad-bottom" aria-label="Advertisement"><!-- Ad: 728×90 --></div>

    {{-- ── SEO CONTENT ── --}}
    <section class="seo-block" aria-label="About VidPull">
        <h2>Free Online Video Downloader — No Software Required</h2>
        <p>VidPull lets you download videos from over 1000 websites directly in your browser. Paste the URL, choose your format (MP4, WebM, MKV) and resolution (360p up to 4K), and your file will be ready in seconds — no browser extension, no desktop app, no account required.</p>
        <p>Supported platforms include YouTube, Instagram Reels, TikTok, Twitter/X, Facebook, Vimeo, Reddit, Twitch clips, Dailymotion, SoundCloud and hundreds more, all processed via the open-source <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" rel="noopener noreferrer">yt-dlp</a> library.</p>
        <div class="seo-tags">
            @foreach(['YouTube','Instagram','TikTok','Twitter / X','Facebook','Vimeo','Reddit','Twitch','Dailymotion','SoundCloud','Bilibili','Pinterest','Rumble','Dailymotion'] as $p)
            <span class="seo-tag">{{ $p }}</span>
            @endforeach
        </div>
        <p>Want audio only? Switch to <strong>Audio only</strong> format to save MP3 audio from any supported video. All processing happens server-side — files are not stored after delivery.</p>
    </section>

    {{-- ── FOOTER ── --}}
    <footer class="vp-footer">
        <span>© {{ date('Y') }} VidPull. All rights reserved.</span>
        <span>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Use</a>
            <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" rel="noopener noreferrer">yt-dlp</a>
        </span>
    </footer>

</div>{{-- /vp-wrap --}}

<script>
    function vidpull() {
        return {
            url: '',
            focused: false,
            fetching: false,
            meta: null,
            selectedFormat: 'mp4',
            selectedRes: '720',
            downloading: false,
            currentDlId: null,
            progress: {
                pct: 0,
                speed: '—',
                eta: '—'
            },
            history: [],
            notification: {
                show: false,
                type: 'success',
                icon: '✓',
                message: ''
            },
            sseSource: null,

            notify(type, message, duration = 5000) {
                const icons = {
                    success: '✓',
                    error: '✕',
                    info: 'ℹ'
                };
                this.notification = {
                    show: true,
                    type,
                    icon: icons[type],
                    message
                };
                if (duration) setTimeout(() => this.notification.show = false, duration);
            },

            async fetchMeta() {
                if (!this.url.trim()) {
                    this.notify('error', '<strong>No URL entered.</strong> Paste a video link above.');
                    return;
                }
                this.fetching = true;
                this.meta = null;
                try {
                    const res = await fetch('{{ route("fetch-meta") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            url: this.url
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        this.notify('error', '<strong>Could not fetch video.</strong> ' + (data.message || 'Check the URL and try again.'));
                        return;
                    }
                    this.meta = data;
                    this.notify('info', 'Video info loaded — choose your format and hit Download.', 3000);
                } catch (e) {
                    this.notify('error', '<strong>Network error.</strong> Could not reach the server.');
                } finally {
                    this.fetching = false;
                }
            },

            async startDownload() {
                if (!this.meta) return;
                this.downloading = true;
                this.progress = {
                    pct: 0,
                    speed: '—',
                    eta: '—'
                };
                try {
                    const res = await fetch('{{ route("download.start") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            uuid: this.meta.uuid,
                            url: this.url,
                            format: this.selectedFormat,
                            resolution: this.selectedRes,
                            title: this.meta.title,
                            thumbnail: this.meta.thumbnail,
                            duration: this.meta.duration,
                            channel: this.meta.channel,
                            platform: this.meta.platform
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        this.notify('error', '<strong>Download failed to start.</strong> ' + (data.message || ''));
                        this.downloading = false;
                        return;
                    }
                    this.currentDlId = data.uuid;
                    this.listenProgress(data.uuid);
                } catch (e) {
                    this.notify('error', '<strong>Network error.</strong> Download could not be queued.');
                    this.downloading = false;
                }
            },

            listenProgress(id) {
                if (this.sseSource) this.sseSource.close();

                const self = this;
                self.downloading = true;

                // Track consecutive errors to detect a real failure
                // (vs normal server-side window close which also fires onerror)
                let errorCount = 0;
                let terminated = false; // set true once done/failed received

                const connect = () => {
                    if (terminated) return;

                    self.sseSource = new EventSource(`/download/progress/${id}`);

                    self.sseSource.onmessage = (e) => {
                        // Any message means connection is healthy — reset error count
                        errorCount = 0;

                        try {
                            const d = JSON.parse(e.data);

                            const pct = Math.min(Math.round(parseFloat(d.pct) || 0), 100);
                            const speed = d.speed || '—';
                            const eta = d.eta || '—';

                            self.progress = {
                                pct,
                                speed,
                                eta
                            };

                            if (d.status === 'done') {
                                terminated = true;
                                self.progress = {
                                    pct: 100,
                                    speed: '—',
                                    eta: 'Complete!'
                                };
                                self.sseSource.close();
                                setTimeout(() => {
                                    self.downloading = false;
                                    self.notify('success', '<strong>Download complete!</strong> Your file is ready below.', 6000);
                                    self.loadHistory();
                                    self.meta = null;
                                    self.downloadFile(id);
                                }, 700);
                            }

                            if (d.status === 'failed') {
                                terminated = true;
                                self.sseSource.close();
                                self.downloading = false;
                                self.notify('error', '<strong>Download failed.</strong> ' + (d.error || 'Please try again.'), 8000);
                                self.loadHistory();
                            }
                        } catch (parseErr) {
                            console.warn('[VidPull] SSE parse error:', parseErr, e.data);
                        }
                    };

                    self.sseSource.onerror = () => {
                        // onerror fires for TWO different reasons:
                        //   1. Server closed the 25s window normally  → reconnect, errorCount stays low
                        //   2. Real network/server error              → errorCount climbs, eventually fall back
                        self.sseSource.close();

                        if (terminated) return; // already done, ignore

                        errorCount++;

                        if (errorCount <= 5) {
                            // Likely a normal window close — reconnect after short delay
                            // (EventSource would auto-reconnect but we manage it manually
                            //  so we can track errorCount and keep self context clean)
                            setTimeout(connect, 1000);
                        } else {
                            // 5 consecutive errors without any message = real problem
                            console.warn('[VidPull] SSE failed after 5 retries — falling back to polling');
                            self.pollProgress(id);
                        }
                    };
                };

                connect();
            },

            async downloadFile(uuid) {
                try {
                    const r = await fetch(`/download/${uuid}/file`, {
                        credentials: 'include'
                    });
                    if (!r.ok) throw new Error('Download failed');
                    const blob = await r.blob();
                    const disp = r.headers.get('Content-Disposition');
                    let fname = 'download';
                    if (disp?.includes('filename=')) fname = disp.split('filename=')[1].replace(/"/g, '');
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = fname;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                } catch (err) {
                    this.notify('error', '<strong>Save failed.</strong> ' + (err.message || ''), 8000);
                }
            },

            pollProgress(id) {
                const iv = setInterval(async () => {
                    try {
                        const r = await fetch('/history');
                        const list = await r.json();
                        const item = list.find(i => i.id === id);
                        if (item?.status === 'done' || item?.status === 'failed') {
                            clearInterval(iv);
                            this.downloading = false;
                            this.loadHistory();
                            if (item.status === 'done') this.notify('success', '<strong>Download complete!</strong>', 6000);
                            else this.notify('error', '<strong>Download failed.</strong> ' + (item.error_message || ''), 8000);
                        }
                    } catch (_) {
                        clearInterval(iv);
                        this.downloading = false;
                    }
                }, 2000);
            },

            async loadHistory() {
                try {
                    const r = await fetch('{{ route("history") }}');
                    this.history = await r.json();
                } catch (_) {}
            },

            refetch(item) {
                this.url = item.url || '';
                this.meta = null;
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                this.$nextTick(() => this.fetchMeta());
            },

            async deleteItem(uuid) {
                try {
                    await fetch(`/history/${uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    this.history = this.history.filter(i => i.uuid !== uuid);
                } catch (_) {}
            },

            async clearHistory() {
                for (const item of [...this.history]) await this.deleteItem(item.uuid);
            },

            onPaste() {
                this.$nextTick(() => {
                    if (this.url.startsWith('http')) setTimeout(() => this.fetchMeta(), 200);
                });
            },
        };
    }
</script>

@endsection