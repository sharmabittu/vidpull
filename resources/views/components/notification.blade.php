{{--
    Usage:
    <x-notification type="success" message="Download complete!" :auto-dismiss="4000" />
    <x-notification type="error"   message="Could not process URL." />

    OR render both and show/hide with Alpine from the parent component.
--}}

@props([
    'type'        => 'success', // 'success' | 'error' | 'info'
    'message'     => '',
    'autoDismiss' => 4000,      // ms; 0 = never
    'show'        => false,
])

@php
    $styles = match($type) {
        'success' => 'bg-emerald-950/80 border-emerald-500/30 text-emerald-300',
        'error'   => 'bg-red-950/80 border-brand-600/40 text-red-300',
        default   => 'bg-blue-950/80 border-blue-500/30 text-blue-300',
    };

    $icon = match($type) {
        'success' => '✓',
        'error'   => '✕',
        default   => 'ℹ',
    };
@endphp

<div
    x-data="{ visible: @js($show), timeout: null }"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    x-init="
        $watch('visible', v => {
            if (v && {{ $autoDismiss }} > 0) {
                clearTimeout(timeout);
                timeout = setTimeout(() => visible = false, {{ $autoDismiss }});
            }
        });
        // Allow parent to trigger: $dispatch('notify', {type:'success', msg:'...'})
        $el.addEventListener('show', () => visible = true);
    "
    @notify.window="
        if ($event.detail.type === '{{ $type }}') {
            $el.querySelector('[data-msg]').textContent = $event.detail.message;
            visible = true;
        }
    "
    class="max-w-3xl mx-auto px-4 mb-3"
>
    <div class="flex items-start gap-3 px-4 py-3 rounded-xl border {{ $styles }} text-sm backdrop-blur-sm">
        <span class="mt-0.5 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
            {{ $type === 'success' ? 'bg-emerald-500/20 text-emerald-400' : ($type === 'error' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400') }}">
            {{ $icon }}
        </span>
        <span data-msg class="flex-1 leading-relaxed">{{ $message }}</span>
        <button
            @click="visible = false"
            class="ml-auto flex-shrink-0 opacity-50 hover:opacity-100 transition-opacity text-base leading-none"
            aria-label="Dismiss"
        >✕</button>
    </div>
</div>
