<x-layout title="Booster Workstation — Dashboard Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Booster Workstation']]" />

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mt-4">
                <x-alert type="success" :dismissible="true">
                    {{ session('success') }}
                </x-alert>
            </div>
        @endif

        @if(session('error'))
            <div class="mt-4">
                <x-alert type="error" :dismissible="true">
                    {{ session('error') }}
                </x-alert>
            </div>
        @endif

        {{-- Booster Header Section --}}
        <div class="mt-6 mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <x-badge variant="purple" size="sm" :dot="true">BOOSTER COMMAND CENTER</x-badge>
                    <x-badge variant="neutral" size="sm">
                        Status: <strong class="capitalize ml-1 {{ $jokiProfile->status === 'active' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $jokiProfile->status ?? 'active' }}</strong>
                    </x-badge>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Halo, <span class="bg-gradient-to-r from-purple-400 via-indigo-300 to-cyan-400 bg-clip-text text-transparent">{{ $jokiProfile->nickname ?? $user->name }}</span>!
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Kelola pengerjaan pesanan joki, ambil antrean order dari pool, dan pantau akumulasi komisi Anda.
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <div class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2">
                    <span class="text-amber-400 font-bold text-xs flex items-center gap-1">
                        &#9733; {{ number_format($jokiProfile->rating ?? 5.0, 1) }}
                    </span>
                    <span class="text-slate-600">&bull;</span>
                    <span class="text-xs text-slate-300 font-semibold">{{ $completedOrdersCount }} Sukses</span>
                </div>

                <x-button as="a" :href="route('joki.dashboard.orders', ['tab' => 'available'])" variant="primary" size="sm" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 border-none shadow-[0_0_20px_rgba(147,51,234,0.3)]">
                    <svg class="w-4 h-4 text-purple-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ambil Order dari Pool
                </x-button>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('joki.dashboard.partials.nav')

        {{-- 4 Metric Cards Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8 sm:mb-10">
            {{-- 1. Available Orders in Pool --}}
            <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg relative overflow-hidden group hover:border-cyan-500/40 transition">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-cyan-500/10 rounded-full blur-xl group-hover:bg-cyan-500/20 transition"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Available Orders</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-cyan-400 block">{{ $availableOrdersCount }}</span>
                <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1 block">Siap diambil di pool</span>
            </div>

            {{-- 2. Active Orders --}}
            <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg relative overflow-hidden group hover:border-purple-500/40 transition">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Active Orders</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-purple-400 block">{{ $activeOrdersCount }}</span>
                <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1 block">Sedang Anda kerjakan</span>
            </div>

            {{-- 3. Completed Orders --}}
            <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg relative overflow-hidden group hover:border-emerald-500/40 transition">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Completed Orders</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 block">{{ $completedOrdersCount }}</span>
                <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1 block">Pesanan sukses selesai</span>
            </div>

            {{-- 4. Total Earnings (Net 80%) --}}
            <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Total Earnings</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="text-xl sm:text-2xl font-black text-white block truncate">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span>
                <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1 block">80% komisi bersih booster</span>
            </div>
        </div>

        {{-- Section 1: Active Orders Being Handled --}}
        <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6 mb-8">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Tugas Aktif Sedang Dikerjakan</span>
                        <x-badge variant="purple" size="sm">{{ $activeOrdersCount }} Aktif</x-badge>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Segera selesaikan target rank akun klien untuk klaim komisi</p>
                </div>
                <x-button as="a" :href="route('joki.dashboard.orders', ['tab' => 'assigned'])" variant="ghost" size="sm" class="text-xs text-purple-400 hover:text-purple-300">
                    Semua Tugas &rarr;
                </x-button>
            </div>

            @if($recentAssignedOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($recentAssignedOrders as $order)
                        @php
                            $item = $order->orderItems->first();
                            $gameTitle = $item?->jokiService?->game?->name
                                ?? $item?->gameProduct?->game?->name
                                ?? $item?->meta['game_name']
                                ?? 'Game';
                            $boosterCut = (float) $order->total_amount * 0.80;
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-950/70 border border-purple-500/20 hover:border-purple-500/40 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-bold text-cyan-400 text-xs">#{{ $order->order_code }}</span>
                                    <x-badge variant="purple" size="sm">{{ $gameTitle }}</x-badge>
                                    <x-badge :variant="$order->status_badge_variant" size="sm">
                                        {{ $order->status_label }}
                                    </x-badge>
                                </div>
                                <h4 class="font-bold text-white text-sm">{{ $item?->item_name ?? 'Layanan Joki' }}</h4>
                                <div class="text-xs text-slate-400 flex items-center gap-2 flex-wrap">
                                    <span>Akun ID: <strong class="text-slate-200 font-mono">{{ $order->account_id ?? '-' }}</strong></span>
                                    @if($order->account_server)
                                        <span>&bull;</span>
                                        <span>Zone: <strong class="text-slate-200 font-mono">{{ $order->account_server }}</strong></span>
                                    @endif
                                    @if($order->account_nickname)
                                        <span>&bull;</span>
                                        <span>Nick: <strong class="text-slate-200">{{ $order->account_nickname }}</strong></span>
                                    @endif
                                </div>
                                @if($order->progress_note)
                                    <p class="text-[11px] text-purple-300 italic mt-1 bg-purple-950/30 px-2.5 py-1 rounded-lg border border-purple-900/50">
                                        Note: "{{ $order->progress_note }}"
                                    </p>
                                @endif
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 border-slate-800/80 pt-3 sm:pt-0 gap-2 shrink-0">
                                <div>
                                    <span class="text-[10px] text-slate-500 block text-left sm:text-right">Komisi Booster</span>
                                    <span class="font-black text-emerald-400 text-sm">Rp {{ number_format($boosterCut, 0, ',', '.') }}</span>
                                </div>
                                <x-button as="a" :href="route('joki.dashboard.orders.show', $order->order_code)" variant="primary" size="sm" class="bg-purple-600 hover:bg-purple-500 border-none">
                                    Detail & Update &raquo;
                                </x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state
                    title="Tidak Ada Tugas Aktif"
                    description="Anda sedang tidak memegang pesanan aktif. Ambil order baru dari antrean pool untuk mulai menghasilkan pendapatan."
                    :actionUrl="route('joki.dashboard.orders', ['tab' => 'available'])"
                    actionText="Buka Pool Order Tersedia"
                />
            @endif
        </div>

        {{-- Section 2: Available Pool Orders --}}
        <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Antrean Order Tersedia di Pool</span>
                        <x-badge variant="primary" size="sm">{{ $availableOrdersCount }} Tersedia</x-badge>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Order yang telah dibayar dan menunggu diambil oleh Pro Booster terverifikasi</p>
                </div>
                <x-button as="a" :href="route('joki.dashboard.orders', ['tab' => 'available'])" variant="ghost" size="sm" class="text-xs text-cyan-400 hover:text-cyan-300">
                    Lihat Semua Pool &rarr;
                </x-button>
            </div>

            @if($recentAvailableOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($recentAvailableOrders as $order)
                        @php
                            $item = $order->orderItems->first();
                            $gameTitle = $item?->jokiService?->game?->name
                                ?? $item?->gameProduct?->game?->name
                                ?? $item?->meta['game_name']
                                ?? 'Game';
                            $boosterCut = (float) $order->total_amount * 0.80;
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-cyan-500/40 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-bold text-cyan-400 text-xs">#{{ $order->order_code }}</span>
                                    <x-badge variant="primary" size="sm">{{ $gameTitle }}</x-badge>
                                    <x-badge variant="success" size="sm">Siap Diambil</x-badge>
                                </div>
                                <h4 class="font-bold text-white text-sm">{{ $item?->item_name ?? 'Layanan Joki' }}</h4>
                                <p class="text-xs text-slate-400">
                                    Target: <span class="text-slate-300">{{ $item?->jokiService?->target ?? 'Sesuai Request' }}</span>
                                    &bull; Estimasi: <span class="text-slate-300">{{ $item?->jokiService?->estimated_time ?? '1-2 Hari' }}</span>
                                </p>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 border-slate-800/80 pt-3 sm:pt-0 gap-2 shrink-0">
                                <div>
                                    <span class="text-[10px] text-slate-500 block text-left sm:text-right">Komisi Booster</span>
                                    <span class="font-black text-emerald-400 text-sm">Rp {{ number_format($boosterCut, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('joki.dashboard.orders.show', $order->order_code) }}" class="text-xs text-slate-400 hover:text-white px-2 py-1">
                                        Review
                                    </a>
                                    <form method="POST" action="{{ route('joki.dashboard.orders.claim', $order->order_code) }}">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm" class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold border-none">
                                            Ambil Order
                                        </x-button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state
                    title="Antrean Pool Kosong"
                    description="Saat ini belum ada order baru yang menunggu booster di pool. Cek kembali secara berkala."
                />
            @endif
        </div>
    </div>
</x-layout>
