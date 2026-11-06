@props([
    'title' => 'Tidak Ada Data',
    'description' => 'Belum ada data atau item yang tersedia saat ini.',
    'actionUrl' => null,
    'actionText' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-12 text-center rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-white/50 dark:bg-slate-950/40 backdrop-blur-sm']) }}>
    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-cyan-500 dark:text-cyan-400 mb-4 shadow-xs dark:shadow-inner">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-8 h-8 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    <h4 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h4>
    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mt-1.5 leading-relaxed">{{ $description }}</p>

    @if($actionUrl && $actionText)
        <div class="mt-6">
            <x-button as="a" :href="$actionUrl" variant="outline" size="sm">
                {{ $actionText }}
            </x-button>
        </div>
    @endif
</div>
