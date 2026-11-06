@props([])

<footer class="mt-20 border-t border-slate-200 dark:border-[#343343]/60 bg-slate-100/80 dark:bg-[#14131a] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
            {{-- Col 1: Brand Info --}}
            <div class="lg:col-span-2 space-y-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="AETHEREL"
                        class="w-10 h-10 rounded-xl object-contain shadow-lg shadow-cyan-500/10 group-hover:scale-105 transition-transform"
                    />
                    <span class="text-lg font-black tracking-wider text-slate-900 dark:text-white uppercase font-sans">
                        AETHER<span class="text-cyan-500 dark:text-cyan-400">EL</span>
                    </span>
                </a>

                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed max-w-sm">
                    Platform top up game termurah, instan 1 detik, dan jasa joki rank terpercaya di Indonesia. Otomatis 24 jam nonstop dengan garansi 100% legal dan aman anti-banned.
                </p>

                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="px-2 py-1 rounded-lg bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 shadow-sm">
                        ✓ 100% Legal &amp; Resmi
                    </span>
                    <span class="px-2 py-1 rounded-lg bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-semibold text-amber-600 dark:text-yellow-400 shadow-sm">
                        ⚡ Otomatis 1 Detik
                    </span>
                    <span class="px-2 py-1 rounded-lg bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-semibold text-slate-700 dark:text-slate-300 shadow-sm">
                        🔒 Garansi Keamanan
                    </span>
                </div>
            </div>

            {{-- Col 2: Navigasi --}}
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Menu Utama</h4>
                <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                    <li><a href="{{ route('home') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Beranda</a></li>
                    <li><a href="{{ route('topup') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Top Up Game</a></li>
                    <li><a href="{{ route('joki') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Jasa Joki Rank</a></li>
                    <li><a href="{{ route('games') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Semua Game</a></li>
                    <li><a href="{{ route('tracking') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Lacak Pesanan</a></li>
                </ul>
            </div>

            {{-- Col 3: Game Populer --}}
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Game Populer</h4>
                <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                    <li><a href="{{ route('topup.game', 'mobile-legends') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Mobile Legends (MLBB)</a></li>
                    <li><a href="{{ route('topup.game', 'free-fire') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Free Fire MAX</a></li>
                    <li><a href="{{ route('topup.game', 'valorant') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Valorant Points</a></li>
                    <li><a href="{{ route('topup.game', 'genshin-impact') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Genshin Impact Genesis</a></li>
                    <li><a href="{{ route('topup.game', 'honor-of-kings') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Honor of Kings Tokens</a></li>
                </ul>
            </div>

            {{-- Col 4: Bantuan & Legal --}}
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Bantuan & Kontak</h4>
                <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                    <li class="flex items-center gap-1.5">
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">WA:</span>
                        <a href="https://wa.me/6281384849982" target="_blank" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">+62 813-8484-9982</a>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <span class="text-amber-600 dark:text-yellow-400 font-bold">Email:</span>
                        <span>support@aethereal.test</span>
                    </li>
                    <li class="pt-1.5"><a href="{{ route('tracking') }}" class="hover:text-amber-500 dark:hover:text-yellow-400 transition">Cek Status Invoice</a></li>
                    <li><span class="text-slate-500">Jam Operasional: 24 Jam Nonstop</span></li>
                </ul>
            </div>
        </div>

        {{-- Payment Methods Accepted --}}
        <div class="mt-10 pt-6 border-t border-slate-200 dark:border-[#343343]/60 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Metode Pembayaran Resmi:</span>
                <div class="flex flex-wrap items-center justify-center gap-1.5">
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-slate-800 dark:text-white shadow-sm">QRIS</span>
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-sky-600 dark:text-sky-400 shadow-sm">DANA</span>
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-emerald-600 dark:text-emerald-400 shadow-sm">GOPAY</span>
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-indigo-600 dark:text-indigo-400 shadow-sm">OVO</span>
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-amber-600 dark:text-yellow-500 shadow-sm">SHOPEEPAY</span>
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-blue-600 dark:text-blue-400 shadow-sm">BCA VA</span>
                    <span class="px-2 py-1 rounded bg-white dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-[10px] font-bold text-amber-600 dark:text-yellow-400 shadow-sm">MANDIRI VA</span>
                </div>
            </div>

            <div class="text-center md:text-right text-[11px] text-slate-500 dark:text-slate-400">
                <p>&copy; {{ date('Y') }} <span class="font-bold text-slate-900 dark:text-white">Aethereal Joki</span>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
