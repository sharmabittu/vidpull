<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ $title ?? 'VidPull — Download Any Video' }}</title>
    <meta name="description" content="Download videos from YouTube, Twitter, Instagram and 1000+ sites. Choose format and quality." />

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />

    {{-- Tailwind via CDN for standalone use; replace with Vite in production --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Syne', 'sans-serif'],
                        mono: ['DM Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#FFF1F1',
                            100: '#FFE0E0',
                            200: '#FFC5C5',
                            400: '#F87171',
                            500: '#EF4444',
                            600: '#DC2626',
                            700: '#B91C1C',
                            800: '#991B1B',
                            900: '#7F1D1D',
                        },
                    },
                    animation: {
                        'slide-up': 'slideUp 0.45s cubic-bezier(0.22,1,0.36,1) both',
                        'fade-in': 'fadeIn 0.3s ease both',
                        'shimmer': 'shimmer 1.4s linear infinite',
                        'pulse-dot': 'pulseDot 2s ease-in-out infinite',
                        'spin-slow': 'spin 1.8s linear infinite',
                    },
                    keyframes: {
                        slideUp: {
                            from: {
                                opacity: '0',
                                transform: 'translateY(18px)'
                            },
                            to: {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        fadeIn: {
                            from: {
                                opacity: '0'
                            },
                            to: {
                                opacity: '1'
                            }
                        },
                        shimmer: {
                            '0%': {
                                transform: 'translateX(-100%)'
                            },
                            '100%': {
                                transform: 'translateX(300%)'
                            }
                        },
                        pulseDot: {
                            '0%,100%': {
                                opacity: '1',
                                transform: 'scale(1)'
                            },
                            '50%': {
                                opacity: '.4',
                                transform: 'scale(1.4)'
                            }
                        },
                    },
                },
            },
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #111827;
        }

        ::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 3px;
        }

        /* Shimmer helper */
        .shimmer-overlay::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.12) 50%, transparent 100%);
            animation: shimmer 1.4s linear infinite;
        }

        /* Red glow radial */
        .hero-glow {
            background: radial-gradient(ellipse 700px 300px at 50% 0%, rgba(220, 38, 38, 0.13) 0%, transparent 70%);
        }

        /* Input focus glow */
        .url-input:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
        }
    </style>

    @yield('meta')

    @stack('head')
</head>

<body class=" font-sans antialiased">

    {{-- Navigation --}}
    <x-nav />

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-white/5 mt-16 py-8">
        <div class="max-w-3xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-brand-600 rounded flex items-center justify-center text-white text-xs font-bold">▼</div>
                <span class="font-semibold text-gray-500">VidPull</span>
            </div>
            <p>Powered by <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" class="text-brand-600 hover:text-brand-400 transition-colors">yt-dlp</a>. For personal use only.</p>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>