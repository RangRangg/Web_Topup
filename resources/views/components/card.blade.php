@props([
    'title' => null,
    'subtitle' => null,
    'badge' => null,
    'badgeVariant' => 'primary',
    'headerAction' => null,
    'hover' => true,
    'glow' => false, // 'cyan', 'purple', true
    'padding' => 'p-6',
])

@php
    $hoverClass = $hover ? 'transition-colors duration-150 hover:border-slate-300 dark:hover:border-slate-700' : '';
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl bg-white dark:bg-[#131c2e] border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-none overflow-hidden transition-colors {$hoverClass}"]) }}>

    @if($title || $badge || $headerAction)
        <div class="px-6 pt-6 pb-4 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/60">
            <div>
                @if($title)
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        {{ $title }}
                        @if($badge)
                            <x-badge :variant="$badgeVariant" size="sm">{{ $badge }}</x-badge>
                        @endif
                    </h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($headerAction)
                <div>{!! $headerAction !!}</div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
