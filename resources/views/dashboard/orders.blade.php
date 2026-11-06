<x-layout title="Daftar Pesanan — Customer Dashboard">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pesanan Saya'],
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
                    Pesanan Saya
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Kelola dan lacak status seluruh pesanan Top Up dan Joki akun game Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button as="a" :href="route('joki')" variant="primary" size="sm">
                    + Order Joki Baru
                </x-button>
                <x-button as="a" :href="route('topup')" variant="outline" size="sm">
                    + Top Up Game
                </x-button>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('dashboard.partials.nav')

        {{-- Filter & Search Form Card --}}
        <div class="p-4 sm:p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-lg mb-6">
            <form method="GET" action="{{ route('dashboard.orders') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-end">
                {{-- Search Box --}}
                <div class="sm:col-span-6 lg:col-span-7">
                    <label for="search" class="block text-xs font-semibold text-slate-400 mb-1.5">
                        Cari Pesanan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Cari Kode Order (ORD-...), Layanan, atau User ID Game..."
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition"
                        >
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="sm:col-span-4 lg:col-span-3">
                    <label for="status" class="block text-xs font-semibold text-slate-400 mb-1.5">
                        Status Pesanan
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white transition"
                    >
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                        <option value="waiting_assignment" {{ request('status') === 'waiting_assignment' ? 'selected' : '' }}>Menunggu Booster</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                {{-- Submit and Reset Buttons --}}
                <div class="sm:col-span-2 flex items-center gap-2">
                    <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                        Filter
                    </x-button>
                    @if(request()->filled('search') || (request()->filled('status') && request('status') !== 'all'))
                        <a
                            href="{{ route('dashboard.orders') }}"
                            class="px-3 py-2.5 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950 text-slate-400 hover:text-white text-xs transition"
                            title="Reset Filter"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Orders Listing Card --}}
        <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">
                        Riwayat Transaksi
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari total {{ $orders->total() }} pesanan
                    </p>
                </div>

                @if(request()->filled('search') || (request()->filled('status') && request('status') !== 'all'))
                    <x-badge variant="primary" size="sm">Difilter</x-badge>
                @endif
            </div>

            @if($orders->count() > 0)
                {{-- Desktop Table View (>= sm) --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                <th class="pb-3 font-semibold">Kode Order</th>
                                <th class="pb-3 font-semibold">Layanan / Game</th>
                                <th class="pb-3 font-semibold">Akun Game</th>
                                <th class="pb-3 font-semibold">Total Tagihan</th>
                                <th class="pb-3 font-semibold">Status Order</th>
                                <th class="pb-3 font-semibold">Tanggal</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($orders as $order)
                                @php
                                    $item = $order->orderItems->first();
                                    $gameTitle = $item?->jokiService?->game?->name
                                        ?? $item?->gameProduct?->game?->name
                                        ?? $item?->meta['game_name']
                                        ?? 'Game';
                                @endphp
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-mono font-bold text-cyan-400">
                                        #{{ $order->order_code }}
                                    </td>
                                    <td class="py-4">
                                        <div class="font-bold text-white">{{ $item?->item_name ?? 'Layanan' }}</div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                            <span>{{ $gameTitle }}</span>
                                            <span class="text-slate-600">&bull;</span>
                                            <span class="text-slate-400">{{ $item?->item_type === 'joki_service' ? 'Jasa Joki' : 'Top Up' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        @if($order->account_id)
                                            <div class="font-mono text-slate-300 font-semibold">{{ $order->account_id }}</div>
                                            @if($order->account_server)
                                                <div class="text-[10px] text-slate-500">Zone: {{ $order->account_server }}</div>
                                            @endif
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 font-bold text-white">
                                        {{ $order->formatted_total }}
                                    </td>
                                    <td class="py-4">
                                        <x-badge :variant="$order->status_badge_variant" size="sm">
                                            {{ $order->status_label }}
                                        </x-badge>
                                    </td>
                                    <td class="py-4 text-slate-400 text-[11px]">
                                        {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="py-4 text-right">
                                        <x-button as="a" :href="route('dashboard.orders.show', $order->order_code)" variant="secondary" size="sm">
                                            Detail &raquo;
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Touch-Friendly Cards (< sm) --}}
                <div class="block sm:hidden space-y-3">
                    @foreach($orders as $order)
                        @php
                            $item = $order->orderItems->first();
                            $gameTitle = $item?->jokiService?->game?->name
                                ?? $item?->gameProduct?->game?->name
                                ?? $item?->meta['game_name']
                                ?? 'Game';
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-slate-700 transition">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-mono font-bold text-cyan-400 text-xs">#{{ $order->order_code }}</span>
                                <x-badge :variant="$order->status_badge_variant" size="sm">
                                    {{ $order->status_label }}
                                </x-badge>
                            </div>

                            <h4 class="font-bold text-white text-sm">{{ $item?->item_name ?? 'Layanan' }}</h4>
                            <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-1">
                                <span>{{ $gameTitle }}</span>
                                <span>&bull;</span>
                                <span class="text-slate-500">{{ $item?->item_type === 'joki_service' ? 'Jasa Joki' : 'Top Up' }}</span>
                            </div>

                            @if($order->account_id)
                                <div class="mt-2 py-1 px-2.5 rounded-lg bg-slate-900 border border-slate-800/80 text-[11px] font-mono text-slate-300 flex items-center justify-between">
                                    <span>ID: {{ $order->account_id }}</span>
                                    @if($order->account_server)
                                        <span class="text-slate-400">Zone: {{ $order->account_server }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-slate-500 block">Total Biaya</span>
                                    <span class="font-black text-white text-xs">{{ $order->formatted_total }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-500 block mb-1">{{ $order->created_at->format('d M, H:i') }}</span>
                                    <a href="{{ route('dashboard.orders.show', $order->order_code) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-cyan-400 hover:text-cyan-300">
                                        Lihat Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="pt-4 border-t border-slate-800/80">
                    <x-pagination :paginator="$orders" />
                </div>
            @else
                <x-empty-state
                    title="Tidak Ada Pesanan Ditemukan"
                    description="Tidak ada pesanan yang sesuai dengan filter atau kata kunci pencarian Anda."
                    :actionUrl="route('dashboard.orders')"
                    actionText="Reset Pencarian"
                />
            @endif
        </div>
    </div>
</x-layout>
