@props([
    'tabs' => [], // ['id' => 'Label', ...]
    'activeTab' => null,
])

<div x-data="{ activeTab: '{{ $activeTab ?? (count($tabs) > 0 ? array_key_first($tabs) : '') }}' }" class="w-full">
    <div class="border-b border-slate-200 dark:border-slate-800">
        <nav class="-mb-px flex space-x-2 sm:space-x-4 overflow-x-auto scrollbar-none py-1">
            @foreach($tabs as $id => $label)
                <button
                    type="button"
                    @click="activeTab = '{{ $id }}'"
                    class="group inline-flex items-center gap-2 py-3 px-4 border-b-2 font-semibold text-sm transition-all whitespace-nowrap cursor-pointer"
                    :class="activeTab === '{{ $id }}' 
                        ? 'border-cyan-600 dark:border-cyan-400 text-cyan-600 dark:text-cyan-400 shadow-[0_4px_15px_-4px_rgba(6,182,212,0.3)]' 
                        : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    <div class="pt-6">
        {{ $slot }}
    </div>
</div>
