@php
    $unreadCount = $unreadNotificationsCount ?? auth()->user()?->notifications()->where('is_read', false)->count() ?? 0;
@endphp

<div class="mb-6">
    {{-- Mobile friendly horizontal scroll pills --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none border-b border-slate-200 dark:border-slate-800 -mx-4 px-4 sm:mx-0 sm:px-0">
        <a
            href="{{ route('dashboard') }}"
            class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 border border-sky-300 dark:border-sky-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>Ringkasan</span>
        </a>

        <a
            href="{{ route('dashboard.orders') }}"
            class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('dashboard.orders*') ? 'text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 border border-sky-300 dark:border-sky-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('dashboard.orders*') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span>Pesanan Saya</span>
        </a>

        <a
            href="{{ route('dashboard.notifications') }}"
            class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap relative {{ request()->routeIs('dashboard.notifications*') ? 'text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 border border-sky-300 dark:border-sky-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('dashboard.notifications*') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span>Notifikasi</span>
            @if($unreadCount > 0)
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-slate-950 bg-sky-400 rounded-full">
                    {{ $unreadCount }}
                </span>
            @endif
        </a>

        <a
            href="{{ route('dashboard.profile') }}"
            class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('dashboard.profile*') ? 'text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 border border-sky-300 dark:border-sky-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('dashboard.profile*') ? 'text-sky-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profil & Keamanan</span>
        </a>
    </div>
</div>
