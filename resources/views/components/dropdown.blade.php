@props([
    'align' => 'right',
    'width' => 'w-56',
    'contentClasses' => 'py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl backdrop-blur-xl',
])

@php
    $alignmentClasses = match ($align) {
        'left' => 'origin-top-left left-0',
        'top' => 'origin-top',
        default => 'origin-top-right right-0',
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="absolute z-50 mt-2 {{ $width }} rounded-xl shadow-2xl {{ $alignmentClasses }}"
        style="display: none;"
        @click="open = false"
    >
        <div class="{{ $contentClasses }} divide-y divide-slate-100 dark:divide-slate-800">
            {{ $content }}
        </div>
    </div>
</div>
