<div class="mb-8">
    {{-- Mobile friendly horizontal scroll pills with purple/violet booster workstation aesthetic --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none border-b border-slate-800/80 -mx-4 px-4 sm:mx-0 sm:px-0">
        <a
            href="{{ route('joki.dashboard') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('joki.dashboard') && !request()->routeIs('joki.dashboard.*') ? 'text-purple-300 bg-purple-500/15 border border-purple-500/35 shadow-[0_0_15px_rgba(168,85,247,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('joki.dashboard') && !request()->routeIs('joki.dashboard.*') ? 'text-purple-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>Overview Workstation</span>
        </a>

        <a
            href="{{ route('joki.dashboard.orders') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('joki.dashboard.orders*') ? 'text-purple-300 bg-purple-500/15 border border-purple-500/35 shadow-[0_0_15px_rgba(168,85,247,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('joki.dashboard.orders*') ? 'text-purple-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Antrean & Tugas</span>
        </a>

        <a
            href="{{ route('joki.dashboard.earnings') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('joki.dashboard.earnings*') ? 'text-purple-300 bg-purple-500/15 border border-purple-500/35 shadow-[0_0_15px_rgba(168,85,247,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('joki.dashboard.earnings*') ? 'text-purple-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Komisi & Pendapatan</span>
        </a>

        <a
            href="{{ route('joki.dashboard.profile') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('joki.dashboard.profile*') ? 'text-purple-300 bg-purple-500/15 border border-purple-500/35 shadow-[0_0_15px_rgba(168,85,247,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
        >
            <svg class="w-4 h-4 {{ request()->routeIs('joki.dashboard.profile*') ? 'text-purple-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profil Pro Booster</span>
        </a>
    </div>
</div>
