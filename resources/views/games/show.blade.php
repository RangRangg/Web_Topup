<x-layout :title="$game->name . ' — Top Up & Jasa Joki Resmi'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Katalog Games', 'url' => route('games')],
            ['label' => $game->name, 'url' => route('games.show', $game->slug)],
        ]" />

        {{-- Game Hero Header Banner --}}
        <div class="mt-6 mb-10 relative rounded-2xl overflow-hidden border border-slate-800 bg-[#131c2e]">
            <div class="relative h-56 sm:h-72 w-full overflow-hidden">
                <img
                    src="{{ $game->banner_url }}"
                    alt="{{ $game->name }}"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-[#131c2e] via-[#131c2e]/70 to-transparent"></div>
            </div>

            <div class="p-6 sm:p-8 relative -mt-20 sm:-mt-24 flex flex-col md:flex-row items-start md:items-end justify-between gap-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden border-2 border-slate-700 bg-slate-950 shrink-0 shadow-md">
                        <img
                            src="{{ $game->icon_url }}"
                            alt="{{ $game->name }}"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            @if($game->badge)
                                <x-badge variant="primary" size="sm">{{ $game->badge }}</x-badge>
                            @endif
                            <span class="text-xs uppercase font-extrabold tracking-wider text-slate-400">
                                {{ strtoupper(str_replace('_', ' ', $game->category)) }}
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                            {{ $game->name }}
                        </h1>

                        <p class="text-xs text-slate-400">
                            Publisher: <span class="text-slate-200 font-semibold">{{ $game->publisher }}</span> &bull; Status: <span class="text-emerald-400 font-bold">100% Server Online</span>
                        </p>
                    </div>
                </div>

                {{-- Action CTA --}}
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <x-button as="a" :href="route('topup.game', $game->slug)" variant="primary" size="lg" class="w-full sm:w-auto shadow-xl">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Top Up {{ $game->name }} Sekarang</span>
                        </span>
                    </x-button>

                    @if($game->jokiServices->isNotEmpty())
                        <x-button as="a" :href="route('joki', ['game' => $game->slug])" variant="secondary" size="lg" class="w-full sm:w-auto">
                            Jasa Joki Rank
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Game Overview Text & Specs --}}
            <div class="px-6 sm:px-8 pb-8 pt-2 grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-slate-800/80 mt-4">
                <div class="md:col-span-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Tentang Game & Layanan</h3>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        {{ $game->description }}
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800/80 space-y-2">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-cyan-400">Ketentuan Pengisian Akun:</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Pastikan Anda memasukkan <strong>User ID</strong> dengan benar. Saldo otomatis masuk dalam 1-5 detik setelah pembayaran QRIS/VA diverifikasi.
                    </p>
                </div>
            </div>
        </div>

        {{-- Products Section --}}
        <div class="mb-16">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <x-badge variant="success" size="sm" class="mb-2">DAFTAR NOMINAL RESMI</x-badge>
                    <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Pilihan Produk Top Up {{ $game->name }}</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Pilih paket nominal untuk langsung melanjutkan ke formulir pengisian ID akun.</p>
                </div>

                <x-button as="a" :href="route('topup.game', $game->slug)" variant="primary" size="sm">
                    Buka Form Top Up Lengkap &raquo;
                </x-button>
            </div>

            @if($game->gameProducts->isEmpty())
                <x-empty-state
                    title="Produk Sedang Dalam Pembaruan"
                    description="Belum ada nominal top up aktif untuk game ini. Silakan cek kembali beberapa saat lagi."
                />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($game->gameProducts as $prod)
                        <div class="rounded-xl bg-[#131c2e] border border-slate-800 hover:border-sky-500 p-4 flex flex-col justify-between transition-colors group">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-950 text-slate-300 border border-slate-800">
                                        {{ $prod->category }}
                                    </span>
                                    <span class="text-[10px] text-emerald-400 font-semibold">Tersedia</span>
                                </div>

                                <h3 class="text-sm font-bold text-white group-hover:text-sky-400 transition leading-snug">
                                    {{ $prod->name }}
                                </h3>

                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">
                                    {{ $prod->description ?? 'Pengiriman instan via server legal resmi.' }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                                <div>
                                    @if($prod->formatted_original_price)
                                        <span class="text-[11px] text-slate-500 line-through block font-medium">
                                            {{ $prod->formatted_original_price }}
                                        </span>
                                    @endif
                                    <span class="text-base font-bold text-white">
                                        {{ $prod->formatted_price }}
                                    </span>
                                </div>

                                <x-button as="a" :href="route('topup.game', ['game' => $game->slug, 'product_id' => $prod->id])" variant="primary" size="sm">
                                    Beli
                                </x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Joki Services Section if available --}}
        @if($game->jokiServices->isNotEmpty())
            <div class="mb-16 pt-10 border-t border-slate-800/80">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <x-badge variant="purple" size="sm" class="mb-2">JOKI PUSH RANK</x-badge>
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Paket Jasa Joki {{ $game->name }}</h2>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">Dikerjakan manual oleh Pro Player tier Radiant/Glory dengan garansi winrate.</p>
                    </div>

                    <x-button as="a" :href="route('joki', ['game' => $game->slug])" variant="secondary" size="sm">
                        Lihat Semua Paket Joki
                    </x-button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($game->jokiServices as $srv)
                        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-6 flex flex-col justify-between hover:border-purple-500/40 transition">
                            <div>
                                <span class="text-xs font-bold text-purple-400 uppercase tracking-wider block mb-2">{{ $srv->service_type }}</span>
                                <h4 class="text-base font-bold text-white mb-1">{{ $srv->name }}</h4>
                                <p class="text-xs text-slate-400 line-clamp-2">{{ $srv->description }}</p>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
                                <span class="text-lg font-black text-white">{{ $srv->formatted_price }}</span>
                                <x-button as="a" :href="route('orders.checkout', $srv->slug)" variant="primary" size="sm">
                                    Pesan Joki
                                </x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Related Games Recommendation --}}
        @if($relatedGames->isNotEmpty())
            <div class="pt-10 border-t border-slate-800/80">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-6">Game Lainnya yang Mungkin Kamu Sukai:</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($relatedGames as $rel)
                        <a
                            href="{{ route('games.show', $rel->slug) }}"
                            class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-cyan-500/50 flex items-center gap-3.5 transition"
                        >
                            <img src="{{ $rel->icon_url }}" alt="{{ $rel->name }}" class="w-12 h-12 rounded-xl object-cover shrink-0" />
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $rel->name }}</h4>
                                <span class="text-[10px] text-slate-400">{{ $rel->publisher }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layout>
