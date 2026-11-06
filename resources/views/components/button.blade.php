@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 select-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none cursor-pointer';

    $variants = [
        'primary' => 'bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold focus:ring-sky-400 shadow-sm',
        'purple' => 'bg-indigo-600 hover:bg-indigo-500 text-white font-bold focus:ring-indigo-500 shadow-sm',
        'accent' => 'bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold focus:ring-amber-400 shadow-sm',
        'secondary' => 'bg-slate-800 hover:bg-slate-700 text-slate-100 border border-slate-700 shadow-sm focus:ring-slate-500',
        'outline' => 'bg-transparent hover:bg-sky-500/10 text-sky-400 border border-sky-500/50 hover:border-sky-400 focus:ring-sky-500',
        'ghost' => 'bg-transparent hover:bg-slate-800 text-slate-300 hover:text-white border-transparent focus:ring-slate-500',
        'danger' => 'bg-rose-600 hover:bg-rose-500 text-white focus:ring-rose-500 shadow-sm',
        'success' => 'bg-emerald-600 hover:bg-emerald-500 text-white focus:ring-emerald-500 shadow-sm',
    ];

    $sizes = [
        'sm' => 'text-xs px-3 py-1.5 gap-1.5',
        'md' => 'text-sm px-4 py-2.5 gap-2',
        'lg' => 'text-base px-6 py-3.5 gap-2.5',
        'xl' => 'text-lg px-8 py-4 gap-3',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($as === 'a' || $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="inline-flex shrink-0">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <span class="inline-flex shrink-0">{!! $iconRight !!}</span>
        @endif
    </a>
@else
    <button {{ $disabled || $loading ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon)
            <span class="inline-flex shrink-0">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight && !$loading)
            <span class="inline-flex shrink-0">{!! $iconRight !!}</span>
        @endif
    </button>
@endif
