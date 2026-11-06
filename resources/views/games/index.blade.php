<x-layout title="Katalog Game & Layanan Top Up">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Katalog Games', 'url' => route('games')]]" />

        {{-- Page Header --}}
        <div class="mt-4 mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <x-badge variant="primary" size="sm" class="mb-2">KATALOG GAME RESMI</x-badge>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Semua Game & Layanan Tersedia</h1>
                <p class="text-sm text-slate-400 mt-1">Pilih judul game favoritmu untuk top up diamond instan 1 detik atau push rank bersama pro booster.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Total Game: <strong class="text-cyan-400 font-bold">{{ $games->count() }}</strong></span>
            </div>
        </div>

        {{-- Search & Filter Section --}}
        <div class="mb-10 p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
            <form action="{{ route('games') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari game (misal: Mobile Legends, Valorant, Free Fire)..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}" />
                @endif

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto">
                        Cari Game
                    </x-button>

                    @if(request()->hasAny(['search', 'type']))
                        <x-button as="a" :href="route('games')" variant="ghost" size="sm" class="shrink-0">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>

            {{-- Category Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800/80">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Kategori:</span>
                
                <a
                    href="{{ route('games', request()->only('search')) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('type') ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/40' : 'bg-slate-950 border border-slate-800 text-slate-400 hover:text-white' }}"
                >
                    Semua
                </a>

                <a
                    href="{{ route('games', array_merge(request()->only('search'), ['type' => 'moba'])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'moba' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/40' : 'bg-slate-950 border border-slate-800 text-slate-400 hover:text-white' }}"
                >
                    MOBA
                </a>

                <a
                    href="{{ route('games', array_merge(request()->only('search'), ['type' => 'fps'])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'fps' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/40' : 'bg-slate-950 border border-slate-800 text-slate-400 hover:text-white' }}"
                >
                    FPS & Tactical
                </a>

                <a
                    href="{{ route('games', array_merge(request()->only('search'), ['type' => 'battle_royale'])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'battle_royale' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/40' : 'bg-slate-950 border border-slate-800 text-slate-400 hover:text-white' }}"
                >
                    Battle Royale
                </a>

                <a
                    href="{{ route('games', array_merge(request()->only('search'), ['type' => 'rpg'])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'rpg' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/40' : 'bg-slate-950 border border-slate-800 text-slate-400 hover:text-white' }}"
                >
                    RPG & Gacha
                </a>
            </div>
        </div>

        {{-- Popular Games Highlight Showcase (if available) --}}
        @if($popularGames->isNotEmpty() && !request()->hasAny(['search', 'type']))
            <div class="mb-10">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Rekomendasi Game Terpopuler</h3>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($popularGames as $pop)
                        <a
                            href="{{ route('topup.game', $pop->slug) }}"
                            class="group rounded-xl bg-[#131c2e] border border-slate-800 hover:border-sky-500 p-3.5 flex items-center gap-3 transition-colors"
                        >
                            <img
                                src="{{ $pop->icon_url }}"
                                alt="{{ $pop->name }}"
                                class="w-11 h-11 rounded-lg object-cover border border-slate-700 group-hover:border-sky-400 transition shrink-0"
                            />
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-sm font-bold text-white group-hover:text-sky-400 transition truncate">{{ $pop->name }}</h4>
                                <span class="text-[10px] text-sky-400 font-semibold uppercase tracking-wider block">{{ $pop->badge }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Main Games Grid --}}
        @if($games->isEmpty())
            <x-empty-state
                title="Tidak Ada Game Ditemukan"
                description="Game yang Anda cari tidak sesuai dengan filter atau kata kunci saat ini. Silakan reset filter untuk melihat katalog lengkap."
                :actionUrl="route('games')"
                actionText="Reset Filter"
            />
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($games as $game)
                    <div class="rounded-2xl bg-[#131c2e] border border-slate-800 hover:border-sky-500/60 p-5 flex flex-col justify-between transition-colors group">
                        <div>
                            {{-- Banner Preview --}}
                            <div class="relative h-40 rounded-xl overflow-hidden mb-4 border border-slate-800 bg-slate-950">
                                <img
                                    src="{{ $game->banner_url }}"
                                    alt="{{ $game->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>

                                @if($game->badge)
                                    <span class="absolute top-2.5 right-2.5 text-[10px] font-black px-2 py-0.5 rounded bg-sky-500 text-slate-950">
                                        {{ $game->badge }}
                                    </span>
                                @endif

                                <div class="absolute bottom-2.5 left-2.5 flex items-center gap-2.5">
                                    <img
                                        src="{{ $game->icon_url }}"
                                        alt="{{ $game->name }}"
                                        class="w-9 h-9 rounded-lg border border-slate-700 object-cover shadow-sm"
                                    />
                                    <div>
                                        <h3 class="text-sm font-bold text-white leading-none">{{ $game->name }}</h3>
                                        <span class="text-[10px] text-slate-300">{{ $game->publisher }}</span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">
                                {{ $game->description }}
                            </p>

                            {{-- Services availability indicators --}}
                            <div class="mt-3.5 pt-3 border-t border-slate-800/80 flex items-center gap-3 text-[11px] text-slate-400 font-medium">
                                <span class="flex items-center gap-1 text-sky-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    {{ $game->game_products_count }} Produk Top Up
                                </span>
                                <span>&bull;</span>
                                <span class="flex items-center gap-1 text-slate-300">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    {{ $game->joki_services_count }} Paket Joki
                                </span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-5 pt-3.5 border-t border-slate-800/80 grid grid-cols-2 gap-2">
                            <x-button as="a" :href="route('games.show', $game->slug)" variant="secondary" size="sm" class="w-full text-center">
                                Detail Game
                            </x-button>

                            <x-button as="a" :href="route('topup.game', $game->slug)" variant="primary" size="sm" class="w-full text-center">
                                Top Up &raquo;
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layout>
