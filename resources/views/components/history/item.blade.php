@props(['download'])

@php
    $statusColor = match($download->status) {
        'done'   => 'bg-emerald-500',
        'failed' => 'bg-brand-600',
        default  => 'bg-yellow-500',
    };

    $label = match($download->format) {
        'audio' => 'MP3 · Audio only',
        default => strtoupper($download->format) . ' · ' . $download->resolution,
    };
@endphp

<li
    id="history-item-{{ $download->uuid }}"
    x-data="{ removing: false }"
    x-show="!removing"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="flex items-center gap-3 bg-gray-900 border border-white/6 rounded-xl px-4 py-3 hover:border-white/10 transition-colors group"
>
    {{-- Status dot --}}
    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $statusColor }}"></span>

    {{-- Thumbnail --}}
    @if($download->thumbnail)
        <img
            src="{{ $download->thumbnail }}"
            alt=""
            class="w-14 h-9 object-cover rounded-md flex-shrink-0 bg-gray-800"
            loading="lazy"
            onerror="this.style.display='none'"
        />
    @else
        <div class="w-14 h-9 bg-gray-800 rounded-md flex-shrink-0 flex items-center justify-center text-gray-700 text-lg">▶</div>
    @endif

    {{-- Meta --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium truncate text-gray-200 mb-0.5">
            {{ $download->title ?? $download->url }}
        </p>
        <p class="text-xs text-gray-600 flex items-center gap-2 flex-wrap">
            <span>{{ $label }}</span>
            @if($download->file_size)
                <span>{{ $download->file_size }}</span>
            @endif
            @if($download->status === 'failed')
                <span class="text-brand-500">Failed</span>
            @endif
            <span>{{ $download->created_at->diffForHumans() }}</span>
        </p>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
        @if($download->status === 'done' && $download->file_path)
            <a
                href="{{ route('api.download.serve', $download->uuid) }}"
                class="text-xs px-3 py-1.5 bg-brand-600/10 border border-brand-600/30 text-brand-400 rounded-lg hover:bg-brand-600/20 transition-colors font-medium"
            >
                ↓ Save
            </a>
        @endif

        <button
            @click="
                removing = true;
                fetch('/api/download/{{ $download->uuid }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
            "
            class="text-xs px-2.5 py-1.5 bg-white/4 border border-white/8 text-gray-500 rounded-lg hover:border-white/15 hover:text-white transition-colors"
            title="Remove"
        >
            ✕
        </button>
    </div>
</li>
