@props([
    'name' => 'modal',
    'title' => null,
    'maxWidth' => 'lg', // sm, md, lg, xl, 2xl
    'show' => false,
])

@php
    $maxWidths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '4xl' => 'max-w-4xl',
    ];
    $widthClass = $maxWidths[$maxWidth] ?? 'max-w-lg';
@endphp

<div
    x-data="{ open: @json($show) }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"
        x-on:click="open = false"
    ></div>

    {{-- Modal Panel --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full {{ $widthClass }} rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 z-10 overflow-hidden text-slate-800 dark:text-slate-200"
    >
        {{-- Neon top strip --}}
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 via-indigo-500 to-purple-600"></div>

        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
            @if($title)
                <h3 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h3>
            @else
                <div></div>
            @endif

            <button
                type="button"
                x-on:click="open = false"
                class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="mt-4 text-slate-600 dark:text-slate-300">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
