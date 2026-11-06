<x-layout title="Katalog Top Up Game Instan Resmi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{
        search: '',
        activeCat: 'all',
        matchGame(cat, name) {
            const matchesCat = this.activeCat === 'all' || cat === this.activeCat;
            const matchesSearch = !this.search || name.toLowerCase().includes(this.search.toLowerCase());
            return matchesCat && matchesSearch;
        }
    }">
        {{-- Breadcrumb --}}
        <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-900 dark:hover:text-white">Beranda</a>
            <span>/</span>
            <span class="text-slate-900 dark:text-white font-semibold">Top Up Game</span>
        </div>

        {{-- Header & Search --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-wide">PILIH GAME UNTUK TOP UP</h1>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Pilih judul game favorit Anda untuk mengisi saldo diamond, koin, atau voucher secara instan.</p>
            </div>

            <div class="w-full md:w-72 relative">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari game..."
                    class="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-sky-500"
                />
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        {{-- Category Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <button
                type="button"
                @click="activeCat = 'all'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer"
                :class="activeCat === 'all' ? 'bg-sky-500 text-white dark:text-slate-950 font-black' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
            >
                Semua Game
            </button>
            <button
                type="button"
                @click="activeCat = 'moba'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer"
                :class="activeCat === 'moba' ? 'bg-sky-500 text-white dark:text-slate-950 font-black' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
            >
                MOBA
            </button>
            <button
                type="button"
                @click="activeCat = 'fps'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer"
                :class="activeCat === 'fps' ? 'bg-sky-500 text-white dark:text-slate-950 font-black' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
            >
                FPS & Tactical
            </button>
            <button
                type="button"
                @click="activeCat = 'battle_royale'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer"
                :class="activeCat === 'battle_royale' ? 'bg-sky-500 text-white dark:text-slate-950 font-black' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
            >
                Battle Royale
            </button>
            <button
                type="button"
                @click="activeCat = 'rpg'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer"
                :class="activeCat === 'rpg' ? 'bg-sky-500 text-white dark:text-slate-950 font-black' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
            >
                RPG & Gacha
            </button>
        </div>

        {{-- Game Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
            @foreach($games as $game)
                <a
                    href="{{ route('topup.game', $game->slug) }}"
                    x-show="matchGame('{{ $game->category }}', '{{ addslashes($game->name) }}')"
                    class="group rounded-xl bg-white dark:bg-[#111927] border border-slate-200 dark:border-slate-800 hover:border-sky-500 overflow-hidden transition-colors flex flex-col justify-between shadow-sm"
                >
                    <div class="aspect-square relative overflow-hidden bg-slate-100 dark:bg-slate-950">
                        <img
                            src="{{ $game->icon_url }}"
                            alt="{{ $game->name }}"
                            loading="lazy"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                        />
                        @if($game->badge)
                            <span class="absolute top-2 left-2 text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-sky-500 text-slate-950">
                                {{ $game->badge }}
                            </span>
                        @endif
                    </div>

                    <div class="p-2.5 sm:p-3 space-y-1">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors truncate">
                            {{ $game->name }}
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                            {{ $game->publisher }}
                        </p>
                        <div class="pt-1 flex items-center justify-between text-[10px]">
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span> Instan
                            </span>
                            <span class="text-slate-400 dark:text-slate-500 group-hover:text-sky-600 dark:group-hover:text-sky-400 font-bold">Top Up &raquo;</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-layout>
