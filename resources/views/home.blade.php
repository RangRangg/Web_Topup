<x-layout title="AETHEREAL — Top Up Game Termurah & Tercepat Resmi">
    {{-- Flash Notifications / Alerts --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <x-alert type="success" :dismissible="true">
                {{ session('success') }}
            </x-alert>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <x-alert type="error" :dismissible="true">
                {{ session('error') }}
            </x-alert>
        </div>
    @endif

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-10"
        x-data="{
            activeCategory: 'all',
            searchQuery: '',
            filterGame(category, name) {
                const matchesCat = this.activeCategory === 'all' || 
                    (this.activeCategory === 'mlbb' && category === 'moba') ||
                    (this.activeCategory === 'roblox' && name.toLowerCase().includes('roblox')) ||
                    (this.activeCategory === 'ff' && name.toLowerCase().includes('free fire')) ||
                    (this.activeCategory === 'pubg' && name.toLowerCase().includes('pubg')) ||
                    (this.activeCategory === 'hok' && name.toLowerCase().includes('honor')) ||
                    (this.activeCategory === 'voucher' && name.toLowerCase().includes('steam')) ||
                    category === this.activeCategory;
                const matchesSearch = !this.searchQuery || name.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchesCat && matchesSearch;
            }
        }"
    >
        {{-- =========================================================================
             SECTION 1: HERO CAROUSEL BANNER (EXQUISITE COLLECTION - Image 2 Style)
             ========================================================================= --}}
        <section class="relative overflow-hidden rounded-3xl bg-[#1b1a23] border border-[#343343] shadow-2xl">
            <div class="relative w-full h-64 sm:h-80 md:h-96 lg:h-[420px] overflow-hidden group">
                {{-- Banner Hero Image --}}
                <img
                    src="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1600&q=80"
                    alt="Exquisite Collection Banner"
                    class="w-full h-full object-cover object-center filter brightness-90 contrast-110"
                />

                {{-- Dark Gradients & Caution Tape Accents --}}
                <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/40 to-black/80"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#14131a] via-transparent to-transparent"></div>

                {{-- Left & Right Carousel Arrows --}}
                <button
                    type="button"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-yellow-400 hover:text-slate-950 text-white flex items-center justify-center transition border border-white/20 backdrop-blur-sm"
                    aria-label="Previous Banner"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-yellow-400 hover:text-slate-950 text-white flex items-center justify-center transition border border-white/20 backdrop-blur-sm"
                    aria-label="Next Banner"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                {{-- Banner Content Overlay --}}
                <div class="absolute inset-0 p-6 sm:p-12 flex flex-col justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-md text-[11px] font-black uppercase tracking-wider bg-yellow-400 text-slate-950 shadow-md">
                            COLLECTOR EDITION
                        </span>
                        <span class="text-xs text-yellow-300 font-bold">Aethereal Joki &bull; Platform Resmi Aethereal</span>
                    </div>

                    <div class="space-y-3 max-w-xl">
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-none uppercase drop-shadow-lg">
                            EXQUISITE<br><span class="text-yellow-400">COLLECTION</span>
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-200 font-medium tracking-wide uppercase">
                            SUN "CELESTIAL REBEL" &bull; TOP UP DIAMOND &amp; JASA JOKI INSTAN 1 DETIK
                        </p>
                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <a
                                href="#pilihan-game"
                                class="px-5 py-2.5 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-slate-950 font-black text-xs sm:text-sm transition shadow-lg shadow-yellow-400/25 flex items-center gap-2"
                            >
                                <span>Top Up Sekarang</span>
                                <span>&raquo;</span>
                            </a>
                            <a
                                href="{{ route('joki') }}"
                                class="px-5 py-2.5 rounded-xl bg-[#23222d]/90 hover:bg-[#2c2b3a] text-white font-bold text-xs sm:text-sm transition border border-[#343343] backdrop-blur-sm"
                            >
                                Cari Jasa Joki
                            </a>
                            <a
                                href="{{ route('ai-assistant') }}"
                                class="px-4 py-2.5 rounded-xl bg-black/60 hover:bg-black/80 text-yellow-300 font-semibold text-xs transition border border-yellow-400/30"
                            >
                                AI Assistant &raquo;
                            </a>
                        </div>
                    </div>

                    {{-- Bottom Indicators --}}
                    <div class="flex items-center gap-1.5 self-center sm:self-start">
                        <span class="w-8 h-1.5 rounded-full bg-yellow-400"></span>
                        <span class="w-2.5 h-1.5 rounded-full bg-slate-600"></span>
                        <span class="w-2.5 h-1.5 rounded-full bg-slate-600"></span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Promo Vouchers Bar --}}
        @if(isset($promotions) && $promotions->isNotEmpty())
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] flex flex-wrap items-center justify-between gap-3 shadow-sm dark:shadow-md transition-colors">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-yellow-400/10 border border-yellow-400/30 flex items-center justify-center text-yellow-600 dark:text-yellow-400 font-bold text-sm">
                        🎟️
                    </span>
                    <div>
                        <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">Promo & Voucher Spesial Hari Ini</h3>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400">Gunakan kode voucher saat checkout untuk mendapatkan potongan harga ekstra!</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($promotions as $promoItem)
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#23222d] border border-yellow-400/40 text-xs font-mono font-bold text-amber-600 dark:text-yellow-300">
                            <span>{{ $promoItem->code }}</span>
                            <span class="text-[10px] font-sans font-semibold text-slate-600 dark:text-slate-400">
                                (-{{ $promoItem->discount_type === 'percentage' ? (int) $promoItem->discount_value . '%' : 'Rp ' . number_format($promoItem->discount_value, 0, ',', '.') }})
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- =========================================================================
             SECTION 2: 🔥 REKOMENDASI (Matching Image 2 Style)
             ========================================================================= --}}
        <section class="space-y-3">
            <div>
                <h2 class="text-sm sm:text-base font-black uppercase tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="text-yellow-500 dark:text-yellow-400">🔥</span> REKOMENDASI
                </h2>
                <p class="text-[11px] text-slate-600 dark:text-slate-400">Berikut adalah beberapa produk yang kami rekomendasikan untuk kamu.</p>
            </div>

            {{-- Featured Recommendation Card --}}
            <a
                href="{{ route('topup.game', 'mobile-legends') }}"
                class="block p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition group relative overflow-hidden shadow-sm dark:shadow-lg"
            >
                <div class="absolute inset-0 bg-gaming-dots opacity-30"></div>
                <div class="relative flex items-center gap-4 z-10">
                    <img
                        src="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=400&q=80"
                        alt="Mobile Legends"
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-slate-200 dark:border-[#343343] group-hover:scale-105 transition shrink-0"
                    />
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-yellow-400 text-slate-950 inline-block mb-1">
                            PALING DIREKOMENDASIKAN
                        </span>
                        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition truncate">
                            Mobile Legends: Bang Bang
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 truncate">Moonton &bull; Top Up Diamond Kilat &amp; Joki Rank Bergaransi Resmi</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-yellow-400 shrink-0">
                        <span>Top Up Sekarang</span>
                        <span>&raquo;</span>
                    </div>
                </div>
            </a>
        </section>

        {{-- =========================================================================
             SECTION 3: 🔥 POPULER SEKARANG! (7 Wide Horizontal Cards with Yellow Bar)
             ========================================================================= --}}
        <section id="trending" class="space-y-3">
            <div>
                <h2 class="text-sm sm:text-base font-black uppercase tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="text-yellow-500 dark:text-yellow-400">🔥</span> POPULER SEKARANG!
                </h2>
                <p class="text-[11px] text-slate-600 dark:text-slate-400">Berikut adalah beberapa produk yang paling populer saat ini.</p>
            </div>

            {{-- 7 Cards in a Grid Matching Image 2 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                {{-- Card 1: WDP Mobile Legends --}}
                <a
                    href="{{ route('topup.game', 'mobile-legends') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80"
                            alt="WDP Mobile Legends"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">WDP Mobile Legends</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Moonton</span>
                    </div>
                </a>

                {{-- Card 2: Mobile Legends --}}
                <a
                    href="{{ route('topup.game', 'mobile-legends') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1563206767-5b18f218e8de?auto=format&fit=crop&w=600&q=80"
                            alt="Mobile Legends"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">Mobile Legends</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Moonton</span>
                    </div>
                </a>

                {{-- Card 3: Joki Rank Mobile Legends --}}
                <a
                    href="{{ route('joki') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80"
                            alt="Joki Rank Mobile Legends"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">{{ $jokiServices->first()?->name ?? 'Joki Rank Mobile Legends' }}</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Aethereal</span>
                    </div>
                </a>

                {{-- Card 4: Jasa Mabar Push --}}
                <a
                    href="{{ route('joki') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=600&q=80"
                            alt="Jasa Mabar Push"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">Jasa Mabar Push</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Aethereal</span>
                    </div>
                </a>

                {{-- Card 5: Free Fire --}}
                <a
                    href="{{ route('topup.game', 'free-fire') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=600&q=80"
                            alt="Free Fire"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">Free Fire</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Garena</span>
                    </div>
                </a>

                {{-- Card 6: PUBG Mobile --}}
                <a
                    href="{{ route('topup.game', 'pubg-mobile') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80"
                            alt="PUBG Mobile"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">PUBG Mobile</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Tencent Games</span>
                    </div>
                </a>

                {{-- Card 7: Honor Of Kings --}}
                <a
                    href="{{ route('topup.game', 'honor-of-kings') }}"
                    class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition flex flex-col justify-between group shadow-sm"
                >
                    <div class="h-20 sm:h-24 relative overflow-hidden bg-slate-900">
                        <img
                            src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80"
                            alt="Honor Of Kings"
                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider text-white/70">AETHEREAL</span>
                    </div>
                    <div class="bg-yellow-400 px-3 py-2 flex items-center justify-between text-slate-950">
                        <h4 class="text-xs sm:text-sm font-black truncate">Honor Of Kings</h4>
                        <span class="text-[10px] font-bold uppercase shrink-0">Tencent Games</span>
                    </div>
                </a>
            </div>
        </section>

        {{-- =========================================================================
             SECTION 4: PILIHAN GAME TERFAVORIT (Category Pills + 6-Col Game Grid)
             ========================================================================= --}}
        <section id="pilihan-game" class="space-y-4 pt-4 border-t border-slate-200 dark:border-[#343343]">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-yellow-400">KATALOG LENGKAP</span>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">Pilihan Game Terfavorit</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Pilih game favoritmu untuk langsung mengisi User ID dan memilih nominal.</p>
                </div>

                {{-- Live Search Box --}}
                <div class="w-full sm:w-64 relative">
                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Cari Game atau Voucher..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-yellow-400 transition"
                    />
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Category Filter Pills (Matching Image 2) --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button
                    type="button"
                    @click="activeCategory = 'all'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'all' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Top Up Games
                </button>
                <button
                    type="button"
                    @click="activeCategory = 'mlbb'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'mlbb' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Spesialis MLBB
                </button>
                <button
                    type="button"
                    @click="activeCategory = 'roblox'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'roblox' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Spesialis Roblox
                </button>
                <button
                    type="button"
                    @click="activeCategory = 'ff'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'ff' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Spesialis Free Fire
                </button>
                <button
                    type="button"
                    @click="activeCategory = 'pubg'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'pubg' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Spesialis PUBG
                </button>
                <button
                    type="button"
                    @click="activeCategory = 'hok'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'hok' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Spesialis HOK
                </button>
                <button
                    type="button"
                    @click="activeCategory = 'voucher'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer"
                    :class="activeCategory === 'voucher' ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs'"
                >
                    Voucher
                </button>
            </div>

            {{-- 6-Column Game Grid with Image 2 Signature Curved Yellow Wave Accent --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($popularGames as $game)
                    <a
                        href="{{ route('topup.game', $game->slug) }}"
                        x-show="filterGame('{{ $game->category }}', '{{ addslashes($game->name) }}')"
                        class="group rounded-2xl bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 hover:-translate-y-1 transition duration-200 overflow-hidden flex flex-col justify-between shadow-sm dark:shadow-md relative"
                    >
                        {{-- Game Thumbnail Artwork --}}
                        <div class="aspect-[4/5] relative overflow-hidden bg-slate-950">
                            <img
                                src="{{ $game->icon_url }}"
                                alt="{{ $game->name }}"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            {{-- Curved Shadow Accent at Bottom --}}
                            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/90 via-black/60 to-transparent"></div>
                            <div class="absolute -bottom-2 inset-x-0 h-6 bg-yellow-400/20 rounded-full blur-md"></div>

                            {{-- Corner Badge --}}
                            @if($game->badge)
                                <span class="absolute top-2 left-2 text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-yellow-400 text-slate-950 shadow-md">
                                    {{ $game->badge }}
                                </span>
                            @else
                                <span class="absolute top-2 left-2 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-md bg-black/70 text-yellow-300 border border-yellow-400/30">
                                    ⚡ 1 Detik
                                </span>
                            @endif

                            {{-- Game Name on Bottom Overlay --}}
                            <div class="absolute bottom-2 inset-x-2 text-center">
                                <h3 class="text-xs sm:text-sm font-black text-white group-hover:text-yellow-400 transition truncate drop-shadow">
                                    {{ $game->name }}
                                </h3>
                                <p class="text-[10px] text-slate-300 truncate mt-0.5">
                                    {{ $game->publisher ?? 'Official Game' }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- "Tampilkan Lainnya..." Button (As shown in Image 2) --}}
            <div class="text-center pt-2">
                <a
                    href="{{ route('games') }}"
                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-white dark:bg-[#23222d] hover:bg-slate-100 dark:hover:bg-[#2c2b3a] border border-slate-200 dark:border-[#343343] text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition shadow-sm"
                >
                    <span>Tampilkan Lainnya...</span>
                </a>
            </div>
        </section>

        {{-- =========================================================================
             SECTION 5: ARTIKEL TERBARU & BERITA GAME (Matching Image 2)
             ========================================================================= --}}
        <section id="artikel" class="space-y-4 pt-6 border-t border-slate-200 dark:border-[#343343]">
            <div>
                <h2 class="text-base sm:text-lg font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    ARTIKEL TERBARU &amp; BERITA GAME
                </h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 max-w-3xl leading-relaxed">
                    Dapatkan informasi terbaru seputar dunia game! Temukan panduan lengkap untuk meningkatkan pengalaman bermain, serta berita terkini mengenai promo, update top up, dan komunitas gamer.
                </p>
            </div>

            {{-- 3 Article Cards Grid matching Image 2 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Article 1 --}}
                <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition group flex flex-col justify-between shadow-sm dark:shadow-md">
                    <div class="h-44 relative overflow-hidden bg-slate-900 p-4 flex flex-col justify-between">
                        <img
                            src="https://images.unsplash.com/photo-1542751110-97427bbecf20?auto=format&fit=crop&w=600&q=80"
                            alt="Cara Daftar Akun"
                            class="absolute inset-0 w-full h-full object-cover filter brightness-75 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                        <div class="relative z-10">
                            <h3 class="text-lg font-black text-white uppercase tracking-tight leading-tight">
                                CARA DAFTAR DAN AKTIVASI<br>AKUN DI WEBSITE <span class="text-yellow-400">AETHEREAL</span>
                            </h3>
                        </div>
                    </div>
                    <div class="p-4 space-y-1 bg-white dark:bg-[#23222d]">
                        <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-yellow-400">Headline</span>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition line-clamp-1">
                            Cara Daftar dan Aktivasi Akun di Website Aethereal
                        </h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2">
                            Panduan langkah demi langkah cara membuat akun, login instan, dan menikmati diskon member spesial.
                        </p>
                    </div>
                </div>

                {{-- Article 2 --}}
                <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition group flex flex-col justify-between shadow-sm dark:shadow-md">
                    <div class="h-44 relative overflow-hidden bg-slate-900 p-4 flex flex-col justify-between">
                        <img
                            src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80"
                            alt="Limit WDP Pass"
                            class="absolute inset-0 w-full h-full object-cover filter brightness-75 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                        <div class="relative z-10">
                            <h3 class="text-lg font-black text-white uppercase tracking-tight leading-tight">
                                CARA MELIHAT LIMIT<br><span class="text-yellow-400">WEEKLY DIAMOND PASS</span>
                            </h3>
                        </div>
                    </div>
                    <div class="p-4 space-y-1 bg-white dark:bg-[#23222d]">
                        <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-yellow-400">Headline</span>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition line-clamp-1">
                            Cara Melihat Limit Weekly Diamond Pass (WDP)
                        </h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2">
                            Ketahui sisa hari aktif WDP kamu dan tips stacking pass hingga maksimal 70 hari hemat diamond.
                        </p>
                    </div>
                </div>

                {{-- Article 3 --}}
                <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] hover:border-yellow-400 transition group flex flex-col justify-between shadow-sm dark:shadow-md">
                    <div class="h-44 relative overflow-hidden bg-slate-900 p-4 flex flex-col justify-between">
                        <img
                            src="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80"
                            alt="Update Items Ori"
                            class="absolute inset-0 w-full h-full object-cover filter brightness-75 group-hover:scale-105 transition duration-300"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                        <div class="relative z-10">
                            <h3 class="text-lg font-black text-white uppercase tracking-tight leading-tight">
                                UPDATE ITEMS ORI<br>VERSI <span class="text-yellow-400">2.1.30</span>
                            </h3>
                        </div>
                    </div>
                    <div class="p-4 space-y-1 bg-white dark:bg-[#23222d]">
                        <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-yellow-400">Headline</span>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition line-clamp-1">
                            Update Patch Notes Original Server Terbaru Versi 2.1.30
                        </h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2">
                            Rincian buff, nerf, dan penyesuaian equipment physical serta magical defense terbaru untuk meta push rank.
                        </p>
                    </div>
                </div>
            </div>

            {{-- "Lihat Semua Artikel" Button --}}
            <div class="pt-2">
                <a
                    href="{{ route('home') }}#artikel"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white dark:bg-[#23222d] hover:bg-slate-100 dark:hover:bg-[#2c2b3a] border border-slate-200 dark:border-[#343343] text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition shadow-sm"
                >
                    <span>Lihat Semua Artikel</span>
                </a>
            </div>
        </section>
    </div>
</x-layout>
