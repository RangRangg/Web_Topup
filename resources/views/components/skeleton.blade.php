@props([
    'variant' => 'text', // text, card, avatar, button
    'lines' => 3,
])

@if($variant === 'card')
    <div {{ $attributes->merge(['class' => 'rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-6 animate-pulse space-y-4 shadow-sm']) }}>
        <div class="h-40 bg-slate-200 dark:bg-slate-800/80 rounded-xl w-full"></div>
        <div class="h-5 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
        <div class="space-y-2">
            <div class="h-3 bg-slate-200 dark:bg-slate-800/60 rounded w-full"></div>
            <div class="h-3 bg-slate-200 dark:bg-slate-800/60 rounded w-5/6"></div>
        </div>
        <div class="h-10 bg-slate-200 dark:bg-slate-800 rounded-xl w-full"></div>
    </div>
@elseif($variant === 'avatar')
    <div {{ $attributes->merge(['class' => 'w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-800 animate-pulse']) }}></div>
@elseif($variant === 'button')
    <div {{ $attributes->merge(['class' => 'h-10 bg-slate-200 dark:bg-slate-800 rounded-xl animate-pulse w-32']) }}></div>
@else
    <div {{ $attributes->merge(['class' => 'space-y-2 animate-pulse']) }}>
        @for($i = 0; $i < $lines; $i++)
            <div class="h-3.5 bg-slate-200 dark:bg-slate-800 rounded {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
        @endfor
    </div>
@endif
