<x-layout title="Dashboard — Aethereal Joki">
    @php
        $topUpOrders = $recentOrders->filter(function ($ord) {
            $first = $ord->orderItems->first();
            if ($first && ($first->item_type === 'game_product' || $first->game_product_id !== null)) {
                return true;
            }
            $isJoki = $ord->joki_profile_id !== null || ($first && ($first->item_type === 'joki_service' || $first->joki_service_id !== null));
            return ! $isJoki;
        });

        $jokiOrders = $recentOrders->filter(function ($ord) {
            $first = $ord->orderItems->first();
            return $ord->joki_profile_id !== null || ($first && ($first->item_type === 'joki_service' || $first->joki_service_id !== null));
        });
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <x-alert type="success" :dismissible="true">
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="error" :dismissible="true">
                {{ session('error') }}
            </x-alert>
        @endif

        {{-- Simple Clean Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    Halo, <span class="text-sky-400">{{ $user->name }}</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    Kelola riwayat pesanan Top Up dan Jasa Joki kamu di satu tempat.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button as="a" :href="route('topup')" variant="outline" size="sm">
                    + Top Up Game
                </x-button>
                <x-button as="a" :href="route('joki')" variant="primary" size="sm">
                    + Order Joki
                </x-button>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('dashboard.partials.nav')

        {{-- Compact Minimalist Stats Bar --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-xl bg-[#131c2e] border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Total Orders</span>
                    <span class="text-lg font-black text-white mt-0.5 block">{{ $totalOrdersCount }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold text-xs">
                    📦
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-[#131c2e] border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Active Orders</span>
                    <span class="text-lg font-black text-amber-400 mt-0.5 block">{{ $activeOrdersCount }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs">
                    ⏳
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-[#131c2e] border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Completed Orders</span>
                    <span class="text-lg font-black text-emerald-400 mt-0.5 block">{{ $completedOrdersCount }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs">
                    ✓
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-[#131c2e] border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Total Spending</span>
                    <span class="text-base font-black text-white mt-0.5 block truncate">Rp {{ number_format($totalSpent, 0, ',', '.') }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-900 text-slate-300 flex items-center justify-center font-bold text-xs">
                    💳
                </div>
            </div>
        </div>

        {{-- Main Content: Separated Top Up vs Joki (Recent Orders) --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm sm:text-base font-bold text-white">Recent Orders</h2>
                    <span class="text-[11px] text-slate-400">&bull; Dipisah per Kategori</span>
                </div>
                <a href="{{ route('dashboard.orders') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300">
                    Lihat Semua Riwayat &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                {{-- 1. BAGIAN TOP UP GAME --}}
                <div class="rounded-2xl bg-[#131c2e] border border-slate-800 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold text-xs">
                                💎
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Top Up Game</h3>
                                <p class="text-[11px] text-slate-400">Diamond, UC, &amp; Voucher Instan</p>
                            </div>
                        </div>
                        <a href="{{ route('topup') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300">
                            + Top Up &raquo;
                        </a>
                    </div>

                    @if($topUpOrders->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach($topUpOrders as $order)
                                @php
                                    $item = $order->orderItems->first();
                                    $gameName = $item?->gameProduct?->game?->name ?? $item?->meta['game_name'] ?? 'Game Top Up';
                                    $itemName = $item?->item_name ?? 'Item Top Up';
                                @endphp
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/80 hover:border-slate-700 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono text-xs font-bold text-sky-400">#{{ $order->order_code }}</span>
                                            <x-badge :variant="$order->status_badge_variant" size="sm">
                                                {{ $order->status_label }}
                                            </x-badge>
                                        </div>
                                        <h4 class="text-xs sm:text-sm font-bold text-white truncate">{{ $itemName }}</h4>
                                        <p class="text-[11px] text-slate-400 truncate">
                                            {{ $gameName }} @if($order->account_id) &bull; ID: <span class="font-mono text-slate-300">{{ $order->account_id }}</span> @endif
                                        </p>
                                    </div>

                                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 border-slate-800/60 pt-2 sm:pt-0 shrink-0">
                                        <span class="text-xs sm:text-sm font-black text-white">{{ $order->formatted_total }}</span>
                                        <a href="{{ route('dashboard.orders.show', $order->order_code) }}" class="text-[11px] font-semibold text-sky-400 hover:text-sky-300 mt-0.5">
                                            Detail &raquo;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 px-4 rounded-xl bg-slate-950/60 border border-slate-800/60 space-y-2">
                            <span class="text-2xl block">💎</span>
                            <p class="text-xs font-semibold text-slate-300">Belum ada transaksi Top Up</p>
                            <p class="text-[11px] text-slate-400">Isi ulang diamond dan koin game favoritmu secara instan 24 jam.</p>
                            <div class="pt-2">
                                <x-button as="a" :href="route('topup')" variant="outline" size="sm">
                                    Mulai Top Up Game &raquo;
                                </x-button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 2. BAGIAN JASA JOKI RANK --}}
                <div class="rounded-2xl bg-[#131c2e] border border-slate-800 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-xs">
                                ⚔️
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Jasa Joki Rank</h3>
                                <p class="text-[11px] text-slate-400">Push Rank, Tier, &amp; Winrate</p>
                            </div>
                        </div>
                        <a href="{{ route('joki') }}" class="text-xs font-semibold text-purple-400 hover:text-purple-300">
                            + Order Joki &raquo;
                        </a>
                    </div>

                    @if($jokiOrders->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach($jokiOrders as $order)
                                @php
                                    $item = $order->orderItems->first();
                                    $gameName = $item?->jokiService?->game?->name ?? $item?->meta['game_name'] ?? 'Game Joki';
                                    $itemName = $item?->item_name ?? 'Layanan Joki';
                                @endphp
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/80 hover:border-slate-700 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono text-xs font-bold text-purple-400">#{{ $order->order_code }}</span>
                                            <x-badge :variant="$order->status_badge_variant" size="sm">
                                                {{ $order->status_label }}
                                            </x-badge>
                                        </div>
                                        <h4 class="text-xs sm:text-sm font-bold text-white truncate">{{ $itemName }}</h4>
                                        <p class="text-[11px] text-slate-400 truncate">
                                            {{ $gameName }} @if($order->account_nickname) &bull; Nick: <span class="text-slate-300">{{ $order->account_nickname }}</span> @elseif($order->account_id) &bull; ID: <span class="font-mono text-slate-300">{{ $order->account_id }}</span> @endif
                                        </p>
                                    </div>

                                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 border-slate-800/60 pt-2 sm:pt-0 shrink-0">
                                        <span class="text-xs sm:text-sm font-black text-white">{{ $order->formatted_total }}</span>
                                        <a href="{{ route('dashboard.orders.show', $order->order_code) }}" class="text-[11px] font-semibold text-purple-400 hover:text-purple-300 mt-0.5">
                                            Detail &raquo;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 px-4 rounded-xl bg-slate-950/60 border border-slate-800/60 space-y-2">
                            <span class="text-2xl block">⚔️</span>
                            <p class="text-xs font-semibold text-slate-300">Belum ada pesanan Joki</p>
                            <p class="text-[11px] text-slate-400">Push rank bersama pro player terverifikasi dengan garansi keamanan akun.</p>
                            <div class="pt-2">
                                <x-button as="a" :href="route('joki')" variant="primary" size="sm">
                                    Pesan Joki Sekarang &raquo;
                                </x-button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
