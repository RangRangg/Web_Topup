@props([])

<header
    x-data="{ mobileMenu: false, searchOpen: false }"
    class="sticky top-0 z-40 bg-white/95 dark:bg-[#1b1a23]/95 border-b border-slate-200 dark:border-[#343343] backdrop-blur-md transition-colors duration-200"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            {{-- Brand Logo (Aetherel Glowing Logo) --}}
            <div class="flex items-center gap-6 shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 focus:outline-none group">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="AETHEREL"
                        class="w-10 h-10 rounded-xl object-contain shadow-lg shadow-cyan-500/10 group-hover:scale-105 transition duration-200"
                    />
                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-black tracking-wider text-slate-900 dark:text-white uppercase flex items-center">
                            AETHER<span class="text-cyan-500 dark:text-cyan-400">EL</span>
                        </span>
                        <span class="text-[9px] text-slate-500 dark:text-slate-400 font-medium -mt-1 tracking-wider uppercase">Official Game Store</span>
                    </div>
                </a>

                {{-- Desktop Navigation Links (Tokapedia Layout) --}}
                <nav class="hidden lg:flex items-center space-x-1">
                    <a
                        href="{{ route('home') }}"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ request()->routeIs('home') && !request()->is('*#*') ? 'text-slate-950 bg-yellow-400 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                    >
                        <span>Topup</span>
                    </a>
                    <a
                        href="{{ route('tracking') }}"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ request()->routeIs('tracking') ? 'text-slate-950 bg-yellow-400' : 'text-slate-600 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                    >
                        <span>Cek Transaksi</span>
                    </a>
                    <a
                        href="{{ route('home') }}#leaderboard"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60"
                    >
                        <span>Leaderboard</span>
                    </a>
                    <a
                        href="{{ route('home') }}#artikel"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60"
                    >
                        <span>Artikel</span>
                    </a>
                    <a
                        href="{{ route('home') }}#kalkulator"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60"
                    >
                        <span>Kalkulator</span>
                    </a>
                </nav>
            </div>

            {{-- Quick Search Form --}}
            <div class="hidden sm:flex flex-1 max-w-xs xl:max-w-sm mx-2">
                <form action="{{ route('games') }}" method="GET" class="w-full relative">
                    <input
                        type="text"
                        name="search"
                        placeholder="Cari Game atau Voucher..."
                        value="{{ request('search') }}"
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-yellow-400 transition"
                    />
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </form>
            </div>

            {{-- Right Actions (Theme Switcher + Flag + Auth Button) --}}
            <div class="hidden md:flex items-center gap-2.5">
                {{-- Theme Switcher Button (Mode Siang / Malam) --}}
                <button
                    type="button"
                    @click="$store.theme.toggle()"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 dark:hover:border-yellow-400 text-slate-700 dark:text-yellow-400 transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-400/50 cursor-pointer flex items-center justify-center"
                    :title="$store.theme.isDark ? 'Beralih ke Mode Siang' : 'Beralih ke Mode Malam'"
                    :aria-label="$store.theme.isDark ? 'Beralih ke mode siang' : 'Beralih ke mode malam'"
                >
                    {{-- Sun Icon (Shown in Dark Mode, click to switch to Light) --}}
                    <svg x-show="$store.theme.isDark" class="w-4 h-4 text-yellow-400 transition-transform duration-200 hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{-- Moon Icon (Shown in Light Mode, click to switch to Dark) --}}
                    <svg x-show="!$store.theme.isDark" class="w-4 h-4 text-slate-700 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                {{-- Indonesian Flag Indicator --}}
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-xs text-slate-700 dark:text-slate-300" title="Region Indonesia (IDR)">
                    <span class="text-sm">🇮🇩</span>
                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">ID / IDR</span>
                </div>

                @auth
                    {{-- User Dropdown --}}
                    <x-dropdown align="right" width="w-56">
                        <x-slot name="trigger">
                            <button type="button" class="flex items-center gap-2 p-1.5 pl-2.5 rounded-xl bg-slate-100 dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-200 hover:border-yellow-400 transition focus:outline-none">
                                <div class="w-6 h-6 rounded-lg bg-yellow-400 text-slate-950 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-semibold max-w-[90px] truncate text-slate-800 dark:text-white">{{ auth()->user()->name }}</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="py-1">
                                @if(auth()->user()->isJoki())
                                    <a href="{{ route('joki.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-amber-600 dark:text-yellow-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        Booster Workstation
                                    </a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('dashboard.orders') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        Pesanan Saya
                                    </a>
                                @endif

                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-rose-600 dark:text-rose-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        Admin Panel
                                    </a>
                                @endif
                            </div>

                            <div class="py-1 border-t border-slate-100 dark:border-slate-800">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-slate-400 dark:hover:border-slate-600 text-slate-800 dark:text-white text-xs font-semibold transition"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="px-4 py-1.5 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-slate-950 text-xs font-black transition shadow-md shadow-yellow-400/20"
                    >
                        Daftar
                    </a>
                @endauth
            </div>

            {{-- Mobile Actions --}}
            <div class="flex md:hidden items-center gap-2">
                {{-- Mobile Theme Toggle Button --}}
                <button
                    type="button"
                    @click="$store.theme.toggle()"
                    class="p-2 rounded-lg text-slate-700 dark:text-yellow-400 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:outline-none flex items-center justify-center min-w-[40px] min-h-[40px]"
                    :aria-label="$store.theme.isDark ? 'Beralih ke mode siang' : 'Beralih ke mode malam'"
                >
                    <svg x-show="$store.theme.isDark" class="w-4 h-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="!$store.theme.isDark" class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <a
                    href="{{ route('tracking') }}"
                    class="p-2 text-slate-600 hover:text-slate-950 dark:text-slate-400 dark:hover:text-white min-w-[40px] min-h-[40px] flex items-center justify-center"
                    title="Lacak Pesanan"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </a>

                <button
                    type="button"
                    @click="mobileMenu = !mobileMenu"
                    class="p-2 rounded-lg text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:outline-none min-w-[40px] min-h-[40px] flex items-center justify-center"
                    aria-label="Menu"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div
        x-show="mobileMenu"
        x-cloak
        class="md:hidden border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0f172a] px-4 pt-3 pb-5 space-y-3"
    >
        {{-- Search on mobile --}}
        <form action="{{ route('games') }}" method="GET" class="relative">
            <input
                type="text"
                name="search"
                placeholder="Cari game..."
                value="{{ request('search') }}"
                class="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-yellow-400"
            />
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </form>

        {{-- Mobile Theme Switcher Row --}}
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343]">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <span x-show="$store.theme.isDark">🌙 Mode Malam</span>
                <span x-show="!$store.theme.isDark" style="display: none;">☀️ Mode Siang</span>
            </span>
            <button
                type="button"
                @click="$store.theme.toggle()"
                class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                :class="$store.theme.isDark ? 'bg-yellow-400 text-slate-950 shadow-sm' : 'bg-slate-900 text-white'"
            >
                <span x-text="$store.theme.isDark ? 'Ganti ke Siang' : 'Ganti ke Malam'"></span>
            </button>
        </div>

        <div class="space-y-1 pt-1">
            <a href="{{ route('home') }}" @click="mobileMenu = false" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#23222d]">
                <span>Topup</span>
            </a>
            <a href="{{ route('tracking') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#23222d]">
                <span>Cek Transaksi</span>
            </a>
            <a href="{{ route('home') }}#leaderboard" @click="mobileMenu = false" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#23222d]">
                <span>Leaderboard</span>
            </a>
            <a href="{{ route('home') }}#artikel" @click="mobileMenu = false" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#23222d]">
                <span>Artikel</span>
            </a>
            <a href="{{ route('home') }}#kalkulator" @click="mobileMenu = false" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#23222d]">
                <span>Kalkulator</span>
            </a>
            <a href="{{ route('topup') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#23222d]">Katalog Game</a>
            <a href="{{ route('joki') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#23222d]">Jasa Joki Rank</a>
        </div>

        <div class="pt-3 border-t border-slate-200 dark:border-[#343343]">
            @auth
                <div class="px-3 py-2 bg-slate-100 dark:bg-[#23222d] rounded-lg mb-2 border border-slate-200 dark:border-transparent">
                    <p class="text-xs font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dashboard') }}" class="flex-1 text-center py-2 rounded-lg bg-slate-800 text-xs font-semibold text-white">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2 rounded-lg bg-rose-600/20 text-rose-500 dark:text-rose-400 text-xs font-semibold">Logout</button>
                    </form>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="text-center py-2 rounded-lg bg-slate-100 dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-xs font-bold text-slate-800 dark:text-white">Masuk</a>
                    <a href="{{ route('register') }}" class="text-center py-2 rounded-lg bg-yellow-400 text-xs font-black text-slate-950 shadow-md">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</header>
