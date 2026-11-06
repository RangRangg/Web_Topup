<x-layout title="Antrean & Tugas — Booster Dashboard">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Booster Workstation', 'url' => route('joki.dashboard')],
            ['label' => 'Antrean & Tugas'],
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
                    Manajemen Tugas & Pool Order
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Ambil pesanan baru dari pool antrean dan kelola progres pengerjaan akun klien Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                    Pool Tersedia: <strong class="text-cyan-400 font-bold">{{ $availableCount }}</strong>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                    Tugas Saya: <strong class="text-purple-400 font-bold">{{ $assignedCount }}</strong>
                </div>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('joki.dashboard.partials.nav')

        {{-- Tab Switcher Bar --}}
        <div class="flex items-center gap-2 border-b border-slate-800 mb-6 pb-2">
            <a
                href="{{ route('joki.dashboard.orders', ['tab' => 'assigned']) }}"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 {{ $tab === 'assigned' ? 'bg-purple-600 text-white shadow-[0_0_15px_rgba(147,51,234,0.3)]' : 'bg-slate-900 text-slate-400 hover:text-white' }}"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Tugas Saya</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'assigned' ? 'bg-purple-800 text-purple-200' : 'bg-slate-800 text-slate-400' }}">{{ $assignedCount }}</span>
            </a>

            <a
                href="{{ route('joki.dashboard.orders', ['tab' => 'available']) }}"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 {{ $tab === 'available' ? 'bg-cyan-500 text-slate-950 shadow-[0_0_15px_rgba(6,182,212,0.3)]' : 'bg-slate-900 text-slate-400 hover:text-white' }}"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Pool Tersedia</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'available' ? 'bg-cyan-700 text-cyan-100' : 'bg-slate-800 text-slate-400' }}">{{ $availableCount }}</span>
            </a>
        </div>

        {{-- Filter & Search Form --}}
        <div class="p-4 sm:p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-lg mb-6">
            <form method="GET" action="{{ route('joki.dashboard.orders') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="{{ $tab === 'assigned' ? 'sm:col-span-6 lg:col-span-7' : 'sm:col-span-9 lg:col-span-10' }}">
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
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition"
                        >
                    </div>
                </div>

                @if($tab === 'assigned')
                    <div class="sm:col-span-4 lg:col-span-3">
                        <label for="status" class="block text-xs font-semibold text-slate-400 mb-1.5">
                            Status Pengerjaan
                        </label>
                        <select
                            name="status"
                            id="status"
                            class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl text-xs sm:text-sm text-white transition"
                        >
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Sedang Dikerjakan (PROCESSING)</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (COMPLETED)</option>
                            <option value="waiting_assignment" {{ request('status') === 'waiting_assignment' ? 'selected' : '' }}>Menunggu Pengerjaan</option>
                        </select>
                    </div>
                @endif

                <div class="sm:col-span-2 flex items-center gap-2">
                    <x-button type="submit" variant="primary" size="md" class="w-full justify-center bg-purple-600 hover:bg-purple-500 border-none">
                        Filter
                    </x-button>
                    @if(request()->filled('search') || (request()->filled('status') && request('status') !== 'all'))
                        <a
                            href="{{ route('joki.dashboard.orders', ['tab' => $tab]) }}"
                            class="px-3 py-2.5 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950 text-slate-400 hover:text-white text-xs transition"
                            title="Reset Filter"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tab Content: Assigned Orders --}}
        @if($tab === 'assigned')
            <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                            <span>Daftar Tugas Anda</span>
                            <x-badge variant="purple" size="sm">{{ $assignedOrders->total() }} Order</x-badge>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Seluruh order joki yang telah ditugaskan dan menjadi tanggung jawab Anda</p>
                    </div>
                </div>

                @if($assignedOrders->count() > 0)
                    {{-- Desktop Table View --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                    <th class="pb-3 font-semibold">Order ID</th>
                                    <th class="pb-3 font-semibold">Layanan / Game</th>
                                    <th class="pb-3 font-semibold">Akun Game</th>
                                    <th class="pb-3 font-semibold">Komisi Booster</th>
                                    <th class="pb-3 font-semibold">Status Pengerjaan</th>
                                    <th class="pb-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80 text-slate-200">
                                @foreach($assignedOrders as $order)
                                    @php
                                        $item = $order->orderItems->first();
                                        $gameTitle = $item?->jokiService?->game?->name
                                            ?? $item?->gameProduct?->game?->name
                                            ?? $item?->meta['game_name']
                                            ?? 'Game';
                                        $boosterCut = (float) $order->total_amount * 0.80;
                                    @endphp
                                    <tr class="hover:bg-slate-950/40 transition">
                                        <td class="py-4 font-mono font-bold text-cyan-400">
                                            #{{ $order->order_code }}
                                        </td>
                                        <td class="py-4">
                                            <div class="font-bold text-white">{{ $item?->item_name ?? 'Layanan' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $gameTitle }}</div>
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
                                        <td class="py-4">
                                            <div class="font-bold text-emerald-400">Rp {{ number_format($boosterCut, 0, ',', '.') }}</div>
                                            <div class="text-[10px] text-slate-500">Omset: {{ $order->formatted_total }}</div>
                                        </td>
                                        <td class="py-4">
                                            <x-badge :variant="$order->status_badge_variant" size="sm">
                                                {{ $order->status_label }}
                                            </x-badge>
                                        </td>
                                        <td class="py-4 text-right">
                                            <x-button as="a" :href="route('joki.dashboard.orders.show', $order->order_code)" variant="primary" size="sm" class="bg-purple-600 hover:bg-purple-500 border-none">
                                                Detail & Update &raquo;
                                            </x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards View --}}
                    <div class="block sm:hidden space-y-3">
                        @foreach($assignedOrders as $order)
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
                                    <x-badge :variant="$order->status_badge_variant" size="sm">
                                        {{ $order->status_label }}
                                    </x-badge>
                                </div>
                                <h4 class="font-bold text-white text-sm">{{ $item?->item_name ?? 'Layanan' }}</h4>
                                <p class="text-xs text-slate-400">{{ $gameTitle }} @if($order->account_id) &bull; ID: {{ $order->account_id }} @endif</p>
                                
                                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-slate-500 block">Komisi Bersih</span>
                                        <span class="font-black text-emerald-400 text-xs">Rp {{ number_format($boosterCut, 0, ',', '.') }}</span>
                                    </div>
                                    <x-button as="a" :href="route('joki.dashboard.orders.show', $order->order_code)" variant="primary" size="sm" class="bg-purple-600 hover:bg-purple-500 border-none text-xs">
                                        Update &raquo;
                                    </x-button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-slate-800/80">
                        <x-pagination :paginator="$assignedOrders" />
                    </div>
                @else
                    <x-empty-state
                        title="Belum Ada Tugas Ditugaskan"
                        description="Anda belum memiliki order yang ditugaskan. Ambil pesanan baru dari pool antrean."
                        :actionUrl="route('joki.dashboard.orders', ['tab' => 'available'])"
                        actionText="Buka Pool Tersedia"
                    />
                @endif
            </div>

        {{-- Tab Content: Available Pool Orders --}}
        @else
            <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                            <span>Pool Antrean Order Tersedia</span>
                            <x-badge variant="primary" size="sm">{{ $availableOrders->total() }} Siap Diambil</x-badge>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Order yang telah terbayar dan menunggu booster terverifikasi untuk pengerjaan</p>
                    </div>
                </div>

                @if($availableOrders->count() > 0)
                    {{-- Desktop Table View --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                    <th class="pb-3 font-semibold">Order ID</th>
                                    <th class="pb-3 font-semibold">Layanan / Game</th>
                                    <th class="pb-3 font-semibold">Target & Estimasi</th>
                                    <th class="pb-3 font-semibold">Komisi Booster</th>
                                    <th class="pb-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80 text-slate-200">
                                @foreach($availableOrders as $order)
                                    @php
                                        $item = $order->orderItems->first();
                                        $gameTitle = $item?->jokiService?->game?->name
                                            ?? $item?->gameProduct?->game?->name
                                            ?? $item?->meta['game_name']
                                            ?? 'Game';
                                        $boosterCut = (float) $order->total_amount * 0.80;
                                    @endphp
                                    <tr class="hover:bg-slate-950/40 transition">
                                        <td class="py-4 font-mono font-bold text-cyan-400">
                                            #{{ $order->order_code }}
                                        </td>
                                        <td class="py-4">
                                            <div class="font-bold text-white">{{ $item?->item_name ?? 'Layanan' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $gameTitle }}</div>
                                        </td>
                                        <td class="py-4">
                                            <div class="text-slate-200 font-medium">{{ $item?->jokiService?->target ?? 'Target Custom' }}</div>
                                            <div class="text-[10px] text-slate-500">Estimasi: {{ $item?->jokiService?->estimated_time ?? '1-2 Hari' }}</div>
                                        </td>
                                        <td class="py-4">
                                            <div class="font-bold text-emerald-400">Rp {{ number_format($boosterCut, 0, ',', '.') }}</div>
                                            <div class="text-[10px] text-slate-500">Nilai Order: {{ $order->formatted_total }}</div>
                                        </td>
                                        <td class="py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
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
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards View --}}
                    <div class="block sm:hidden space-y-3">
                        @foreach($availableOrders as $order)
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
                                    <x-badge variant="success" size="sm">Siap Diambil</x-badge>
                                </div>
                                <h4 class="font-bold text-white text-sm">{{ $item?->item_name ?? 'Layanan' }}</h4>
                                <p class="text-xs text-slate-400">{{ $gameTitle }} &bull; Target: {{ $item?->jokiService?->target ?? '-' }}</p>

                                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-slate-500 block">Komisi Booster</span>
                                        <span class="font-black text-emerald-400 text-xs">Rp {{ number_format($boosterCut, 0, ',', '.') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('joki.dashboard.orders.claim', $order->order_code) }}">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm" class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold border-none text-xs">
                                            Ambil Order
                                        </x-button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-slate-800/80">
                        <x-pagination :paginator="$availableOrders" />
                    </div>
                @else
                    <x-empty-state
                        title="Antrean Pool Kosong"
                        description="Saat ini belum ada pesanan joki yang menunggu booster di pool."
                    />
                @endif
            </div>
        @endif
    </div>
</x-layout>
