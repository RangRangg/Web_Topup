@props([
    'type' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
])

@php
    $types = [
        'info' => [
            'bg' => 'bg-cyan-50 dark:bg-cyan-950/40 border-cyan-200 dark:border-cyan-500/40 text-cyan-900 dark:text-cyan-200',
            'icon' => '<svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-500/40 text-emerald-900 dark:text-emerald-200',
            'icon' => '<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-500/40 text-amber-900 dark:text-amber-200',
            'icon' => '<svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        ],
        'danger' => [
            'bg' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-500/40 text-rose-900 dark:text-rose-200',
            'icon' => '<svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
    ];

    $cfg = $types[$type] ?? $types['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    {{ $attributes->merge(['class' => "relative flex items-start gap-3 p-4 rounded-xl border backdrop-blur-md {$cfg['bg']}"]) }}
>
    <div class="shrink-0 mt-0.5">
        {!! $cfg['icon'] !!}
    </div>

    <div class="flex-1 text-sm">
        @if($title)
            <h4 class="font-bold text-slate-900 dark:text-white mb-0.5">{{ $title }}</h4>
        @endif
        <div class="leading-relaxed opacity-90">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            x-on:click="show = false"
            class="shrink-0 p-1 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
