{{-- @param \Illuminate\Database\Eloquent\Collection $downloads --}}

@props(['downloads'])

<section id="history" class="max-w-3xl mx-auto px-4 pb-16 animate-slide-up">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold tracking-tight text-gray-200">Recent Downloads</h2>
        @if($downloads->count())
        <button
            x-data
            @click="
                if (!confirm('Clear all download history?')) return;
                fetch('/api/downloads', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
                    .then(() => $el.closest('section').remove())
            "
            class="text-xs text-gray-600 hover:text-brand-500 transition-colors"
        >
            Clear all
        </button>
        @endif
    </div>

    @if($downloads->isEmpty())
        <div class="text-center py-14 text-gray-700">
            <div class="text-4xl mb-3">⬇</div>
            <p class="text-sm">No downloads yet. Paste a URL above to get started.</p>
        </div>
    @else
        <ul class="flex flex-col gap-2" id="download-history-list">
            @foreach($downloads as $dl)
                <x-history.item :download="$dl" />
            @endforeach
        </ul>
    @endif

</section>
