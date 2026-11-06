<x-layout title="Komisi & Pendapatan — Booster Dashboard">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Booster Workstation', 'url' => route('joki.dashboard')],
            ['label' => 'Komisi & Pendapatan'],
        ]" />

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

        {{-- Header Section --}}
        <div class="mt-6 mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Laporan Pendapatan & Komisi
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Pantau rincian pembagian hasil pengerjaan joki, omset transaksi, dan pencairan komisi Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                    Bagi Hasil: <strong class="text-emerald-400 font-bold">80% Booster</strong> / 20% Sistem
                </div>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('joki.dashboard.partials.nav')

        {{-- Financial Metrics Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
            {{-- 1. Total Komisi Bersih --}}
            <div class="p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-emerald-500/30 shadow-lg relative overflow-hidden group">
                <div class="absolute -right-3 -top-3 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Komisi Bersih (80%)</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 block truncate">
                    Rp {{ number_format($totalEarnings, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Akumulasi seluruh pesanan sukses</span>
            </div>

            {{-- 2. Total Nilai Omset Pengerjaan --}}
            <div class="p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omset Diselesaikan</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-white block truncate">
                    Rp {{ number_format($grossRevenue, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Nilai kotor 100% order diselesaikan</span>
            </div>

            {{-- 3. Potensi Komisi Berjalan --}}
            <div class="p-6 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-lg relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Potensi Komisi Berjalan</span>
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-cyan-400 block truncate">
                    Rp {{ number_format($pendingPotentialEarnings, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Dari order aktif yang sedang berjalan</span>
            </div>
        </div>

        {{-- Payout Information Card --}}
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-950/70 border border-slate-800 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Pencairan Komisi Pro Booster
                </h3>
                <p class="text-xs text-slate-400">
                    Pencairan komisi otomatis diproses setiap hari Senin, atau Anda dapat mengajukan pencairan manual kapan saja melalui admin finance.
                </p>
            </div>
            <a
                href="https://wa.me/6281234567890?text=Halo%20Admin%20Finance,%20saya%20booster%20{{ urlencode($jokiProfile->nickname) }}%20ingin%20mengajukan%20pencairan%20komisi."
                target="_blank"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/35 text-emerald-400 text-xs font-bold transition shrink-0"
            >
                Ajukan Pencairan Dana &rarr;
            </a>
        </div>

        {{-- Completed Orders Earnings Breakdown Table --}}
        <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Riwayat Order Selesai & Rincian Komisi</span>
                        <x-badge variant="emerald" size="sm">{{ $completedOrders->total() }} Selesai</x-badge>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar transaksi yang telah selesai Anda kerjakan beserta pembagian 80%</p>
                </div>
            </div>

            @if($completedOrders->count() > 0)
                {{-- Desktop Table View --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                <th class="pb-3 font-semibold">Tanggal Selesai</th>
                                <th class="pb-3 font-semibold">Order ID</th>
                                <th class="pb-3 font-semibold">Layanan / Game</th>
                                <th class="pb-3 font-semibold">Nilai Transaksi (100%)</th>
                                <th class="pb-3 font-semibold">Komisi Booster (80%)</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($completedOrders as $order)
                                @php
                                    $item = $order->orderItems->first();
                                    $gameTitle = $item?->jokiService?->game?->name
                                        ?? $item?->gameProduct?->game?->name
                                        ?? $item?->meta['game_name']
                                        ?? 'Game';
                                    $boosterCut = (float) $order->total_amount * 0.80;
                                @endphp
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 text-slate-400 font-mono text-[11px]">
                                        {{ $order->completed_at ? $order->completed_at->format('d/m/Y H:i') : $order->updated_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-4 font-mono font-bold text-cyan-400">
                                        #{{ $order->order_code }}
                                    </td>
                                    <td class="py-4">
                                        <div class="font-bold text-white">{{ $item?->item_name ?? 'Layanan' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $gameTitle }}</div>
                                    </td>
                                    <td class="py-4 font-semibold text-slate-300">
                                        {{ $order->formatted_total }}
                                    </td>
                                    <td class="py-4 font-black text-emerald-400">
                                        Rp {{ number_format($boosterCut, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 text-right">
                                        <x-button as="a" :href="route('joki.dashboard.orders.show', $order->order_code)" variant="secondary" size="sm">
                                            Rincian &raquo;
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards View --}}
                <div class="block sm:hidden space-y-3">
                    @foreach($completedOrders as $order)
                        @php
                            $item = $order->orderItems->first();
                            $gameTitle = $item?->jokiService?->game?->name
                                ?? $item?->gameProduct?->game?->name
                                ?? $item?->meta['game_name']
                                ?? 'Game';
                            $boosterCut = (float) $order->total_amount * 0.80;
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-cyan-400 text-xs">#{{ $order->order_code }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">
                                    {{ $order->completed_at ? $order->completed_at->format('d M, H:i') : '-' }}
                                </span>
                            </div>
                            <h4 class="font-bold text-white text-sm">{{ $item?->item_name ?? 'Layanan' }}</h4>
                            <p class="text-xs text-slate-400">{{ $gameTitle }}</p>

                            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-slate-500 block">Komisi Bersih (80%)</span>
                                    <span class="font-black text-emerald-400 text-sm">Rp {{ number_format($boosterCut, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('joki.dashboard.orders.show', $order->order_code) }}" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
                                    Rincian &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-800/80">
                    <x-pagination :paginator="$completedOrders" />
                </div>
            @else
                <x-empty-state
                    title="Belum Ada Riwayat Komisi"
                    description="Anda belum menyelesaikan pesanan joki. Selesaikan order aktif Anda untuk mulai mengumpulkan komisi."
                    :actionUrl="route('joki.dashboard.orders', ['tab' => 'available'])"
                    actionText="Ambil Order Pertama"
                />
            @endif
        </div>
    </div>
</x-layout>
