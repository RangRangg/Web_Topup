@props([
    'variant' => 'primary',
    'size' => 'md',
    'dot' => false,
])

@php
    $variants = [
        'primary' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400 border-sky-500/30',
        'purple' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-500/30',
        'pink' => 'bg-pink-500/10 text-pink-700 dark:text-pink-400 border-pink-500/30',
        'success' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
        'warning' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/30',
        'danger' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/30',
        'neutral' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    ];

    $sizes = [
        'sm' => 'text-[11px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
        'lg' => 'text-sm px-3 py-1.5',
    ];

    $dotColors = [
        'primary' => 'bg-sky-500 dark:bg-sky-400',
        'purple' => 'bg-indigo-500 dark:bg-indigo-400',
        'pink' => 'bg-pink-500 dark:bg-pink-400',
        'success' => 'bg-emerald-500 dark:bg-emerald-400',
        'warning' => 'bg-amber-500 dark:bg-amber-400',
        'danger' => 'bg-rose-500 dark:bg-rose-400',
        'neutral' => 'bg-slate-500 dark:bg-slate-400',
    ];

    $classes = 'inline-flex items-center gap-1.5 font-semibold rounded-full border transition-colors ' . 
               ($variants[$variant] ?? $variants['primary']) . ' ' . 
               ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$variant] ?? 'bg-sky-400' }}"></span>
    @endif
    {{ $slot }}
</span>
