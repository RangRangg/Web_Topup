@props([
    'items' => [], // [['label' => 'Home', 'url' => '/'], ['label' => 'Joki', 'url' => '/joki'], ['label' => 'MLBB']]
])

<nav aria-label="Breadcrumb" class="flex items-center text-xs font-medium text-slate-500 dark:text-slate-400 py-3">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
        <li class="inline-flex items-center">
            <a href="{{ route('home') }}" class="inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                Home
            </a>
        </li>

        @foreach($items as $item)
            <li>
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if(!empty($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-cyan-600 dark:text-cyan-400 font-semibold" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
