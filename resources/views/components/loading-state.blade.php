@props([
    'message' => 'Memuat data...',
    'size' => 'md', // sm, md, lg
])

@php
    $spinnerSizes = [
        'sm' => 'w-6 h-6 border-2',
        'md' => 'w-10 h-10 border-3',
        'lg' => 'w-16 h-16 border-4',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 gap-4 text-center']) }}>
    <div class="relative">
        {{-- Outer glowing pulse ring --}}
        <div class="absolute inset-0 rounded-full bg-cyan-500/20 blur-md animate-ping"></div>

        {{-- Futuristic Dual Spinner --}}
        <div class="relative {{ $spinnerSizes[$size] ?? $spinnerSizes['md'] }} rounded-full border-cyan-500/20 border-t-cyan-400 border-r-purple-500 animate-spin"></div>
    </div>

    @if($message)
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-400 animate-pulse">{{ $message }}</p>
    @endif
</div>
