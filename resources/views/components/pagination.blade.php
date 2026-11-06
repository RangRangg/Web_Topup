@props([
    'paginator' => null,
])

@if ($paginator && $paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between py-4">
        {{-- Mobile Navigation --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg cursor-default">
                    &laquo; Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 shadow-xs">
                    &laquo; Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 shadow-xs">
                    Next &raquo;
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg cursor-default">
                    Next &raquo;
                </span>
            @endif
        </div>

        {{-- Desktop Navigation --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Showing <span class="font-semibold text-slate-900 dark:text-white">{{ $paginator->firstItem() }}</span> to <span class="font-semibold text-slate-900 dark:text-white">{{ $paginator->lastItem() }}</span> of <span class="font-semibold text-slate-900 dark:text-white">{{ $paginator->total() }}</span> results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl shadow-xs space-x-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Previous" class="px-3 py-2 text-xs font-medium text-slate-400 dark:text-slate-600 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg cursor-default">
                            &laquo;
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition shadow-xs">
                            &laquo;
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="px-3.5 py-2 text-xs font-bold text-slate-950 bg-gradient-to-r from-cyan-400 to-teal-400 border border-cyan-400 rounded-lg shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3.5 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition shadow-xs">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-cyan-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition shadow-xs">
                            &raquo;
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Next" class="px-3 py-2 text-xs font-medium text-slate-400 dark:text-slate-600 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg cursor-default">
                            &raquo;
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
