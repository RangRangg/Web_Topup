<x-layout title="Laporan & Analitik — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Laporan & Analitik Penjualan']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Omset Platform</span>
                <span class="text-2xl font-black text-emerald-400 mt-1 block">
                    Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Akumulasi seluruh transaksi lunas</span>
            </div>

            <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-400 block">Total Transaksi</span>
                <span class="text-2xl font-black text-white mt-1 block">
                    {{ number_format($summary['total_orders']) }} Pesanan
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Top up dan jasa joki digabung</span>
            </div>

            <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400 block">Rata-Rata Nilai Order</span>
                <span class="text-2xl font-black text-indigo-400 mt-1 block">
                    Rp {{ number_format($summary['avg_order_value'], 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Average Order Value (AOV)</span>
            </div>

            <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400 block">Completion Rate</span>
                <span class="text-2xl font-black text-purple-400 mt-1 block">
                    {{ $summary['completion_rate'] }}%
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">Persentase order sukses selesai</span>
            </div>
        </div>

        {{-- 1. Daily Revenue Chart / Visual Bars (Last 14 Days) --}}
        <div class="mb-8 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-800 mb-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Pendapatan Harian (14 Hari Terakhir)</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Grafik omset harian pesanan terbayar.</p>
                </div>

                @php
                    $maxDaily = max(1, ...array_values($dailyRevenue));
                @endphp
                <span class="text-xs text-slate-500 font-mono">Puncak Tertinggi: Rp {{ number_format($maxDaily, 0, ',', '.') }}</span>
            </div>

            <div class="grid grid-cols-7 sm:grid-cols-14 gap-2 items-end h-48 pt-6 pb-2">
                @foreach($dailyRevenue as $date => $amount)
                    @php
                        $pct = $maxDaily > 0 ? max(8, round(($amount / $maxDaily) * 100)) : 8;
                    @endphp
                    <div class="flex flex-col items-center h-full justify-end group relative">
                        {{-- Tooltip hover --}}
                        <div class="absolute -top-10 opacity-0 group-hover:opacity-100 transition pointer-events-none z-10 px-2 py-1 rounded-lg bg-slate-950 border border-slate-700 text-[10px] text-white font-mono whitespace-nowrap shadow-xl">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </div>

                        {{-- Bar --}}
                        <div
                            class="w-full rounded-t-lg transition-all duration-300 {{ $amount > 0 ? 'bg-gradient-to-t from-emerald-600 to-cyan-400 group-hover:brightness-125' : 'bg-slate-800/60' }}"
                            style="height: {{ $pct }}%;"
                        ></div>

                        {{-- Date Label --}}
                        <span class="text-[9px] text-slate-500 font-mono mt-2 truncate w-full text-center">
                            {{ $dailyDates[$date] ?? substr($date, 5) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2. Monthly Revenue (Current Year) & Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Monthly Revenue Grid (2 Cols) --}}
            <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-base font-bold text-white mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Pendapatan Bulanan Tahun {{ date('Y') }}</span>
                    </span>
                    <span class="text-xs text-slate-500 font-mono">12 Bulan</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($monthlyRevenue as $month => $amount)
                        <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800/80 hover:border-slate-700 transition">
                            <span class="text-[10px] uppercase font-bold text-slate-400 font-mono">{{ $month }} {{ date('Y') }}</span>
                            <div class="text-sm font-black text-white mt-1 font-mono {{ $amount > 0 ? 'text-emerald-400' : 'text-slate-500' }}">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order Distribution by Type & Status (1 Col) --}}
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl text-xs space-y-6">
                <div>
                    <h3 class="text-base font-bold text-white mb-4 pb-3 border-b border-slate-800">
                        Distribusi Tipe Pesanan
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-purple-400">Jasa Joki Game</span>
                                <span class="font-mono text-white">{{ $jokiOrdersCount }} Order</span>
                            </div>
                            @php
                                $totalTypes = max(1, $jokiOrdersCount + $topupOrdersCount);
                                $jokiPct = round(($jokiOrdersCount / $totalTypes) * 100);
                            @endphp
                            <div class="w-full h-2 rounded-full bg-slate-950 overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full" style="width: {{ $jokiPct }}%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-cyan-400">Top Up Game</span>
                                <span class="font-mono text-white">{{ $topupOrdersCount }} Order</span>
                            </div>
                            @php
                                $topupPct = round(($topupOrdersCount / $totalTypes) * 100);
                            @endphp
                            <div class="w-full h-2 rounded-full bg-slate-950 overflow-hidden">
                                <div class="h-full bg-cyan-400 rounded-full" style="width: {{ $topupPct }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800">
                    <h4 class="font-bold text-white mb-3">Status Pesanan</h4>
                    <div class="space-y-2">
                        @foreach($ordersByStatus as $status => $count)
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950 border border-slate-800/80">
                                <span class="capitalize text-slate-400 font-medium">{{ str_replace('_', ' ', $status) }}</span>
                                <span class="font-mono font-bold text-white">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Top Selling Games --}}
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
            <h3 class="text-base font-bold text-white mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>Game Terpopuler Berdasarkan Total Pesanan</span>
                </span>
                <span class="text-xs text-slate-500">Top 5 Games</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                @foreach($topGames as $tg)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 mb-2">
                                @if($tg->icon_url)
                                    <img src="{{ $tg->icon_url }}" alt="{{ $tg->name }}" class="w-8 h-8 rounded-lg object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">
                                        {{ substr($tg->name, 0, 2) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-white text-xs truncate">{{ $tg->name }}</div>
                                    <div class="text-[10px] text-slate-500 uppercase font-mono">{{ $tg->category }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400">Total Order</span>
                            <span class="font-mono font-bold text-cyan-400 text-xs">{{ $tg->total_orders_count }} Transaksi</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>
