<x-layout title="Booster Workstation — Dashboard Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
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

        {{-- Booster Header --}}
        <div class="mt-4 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <x-badge variant="purple" size="sm" class="mb-2">PRO BOOSTER WORKSTATION</x-badge>
                <h1 class="text-3xl font-black text-white tracking-tight">Halo, {{ $user->jokiProfile->nickname ?? $user->name }}!</h1>
                <p class="text-xs text-slate-400 mt-1">Kelola pesanan yang ditugaskan kepada Anda dan ambil order joki yang tersedia dari antrean.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-bold text-white">Status: {{ ucfirst($user->jokiProfile->status ?? 'Aktif') }}</span>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-10">
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Tugas Aktif</span>
                <span class="text-3xl font-black text-purple-400 mt-2 block">{{ $activeOrdersCount }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Sedang dalam pengerjaan</span>
            </div>

            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Tersedia di Pool</span>
                <span class="text-3xl font-black text-cyan-400 mt-2 block">{{ $availableOrders->count() }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Order menunggu booster</span>
            </div>

            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Pesanan Selesai</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block">{{ $completedOrdersCount }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Total pesanan sukses</span>
            </div>

            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Rating Booster</span>
                <span class="text-3xl font-black text-amber-400 mt-2 block flex items-center gap-1">
                    ★ {{ number_format($user->jokiProfile->rating ?? 5.0, 1) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-1 block">Ulasan kepuasan gamers</span>
            </div>
        </div>

        {{-- Section 1: Assigned Orders (Tugas Saya) --}}
        <div class="mb-10 p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                        Tugas Saya yang Sedang Aktif
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Order yang secara resmi ditugaskan kepada Anda.</p>
                </div>
                <span class="text-xs text-purple-400 font-bold">{{ $assignedOrders->count() }} Order</span>
            </div>

            @if($assignedOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Kode Order</th>
                                <th class="pb-3 font-semibold">Layanan & Game</th>
                                <th class="pb-3 font-semibold">Akun / Login Via</th>
                                <th class="pb-3 font-semibold">Catatan Khusus</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($assignedOrders as $ord)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-mono font-bold text-cyan-400">
                                        <a href="{{ route('orders.show', $ord->order_code) }}" class="hover:underline">
                                            #{{ $ord->order_code }}
                                        </a>
                                    </td>
                                    <td class="py-4">
                                        <div class="font-bold text-white">{{ $ord->orderItems->first()?->item_name ?? 'Joki Service' }}</div>
                                        <div class="text-[11px] text-slate-400">Target: {{ $ord->orderItems->first()?->meta['target'] ?? '-' }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="font-mono text-white">{{ $ord->account_id }}</div>
                                        <div class="text-[10px] text-cyan-400">{{ $ord->login_via ?? 'Login Game' }}</div>
                                    </td>
                                    <td class="py-4 text-slate-400 max-w-xs truncate">
                                        {{ $ord->account_notes ?: 'Tidak ada catatan' }}
                                    </td>
                                    <td class="py-4">
                                        <x-badge :variant="$ord->status_badge_variant" size="sm">
                                            {{ $ord->status_label }}
                                        </x-badge>
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button as="a" :href="route('orders.show', $ord->order_code)" variant="secondary" size="sm">
                                                Detail
                                            </x-button>

                                            @if($ord->order_status === 'processing')
                                                <form action="{{ route('orders.progress', $ord->order_code) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="completed" />
                                                    <x-button type="submit" variant="primary" size="sm">
                                                        Tandai Selesai
                                                    </x-button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center rounded-2xl bg-slate-950/40 border border-slate-800">
                    <p class="text-xs text-slate-400">Belum ada order yang ditugaskan kepada Anda saat ini. Silakan periksa antrean pool di bawah.</p>
                </div>
            @endif
        </div>

        {{-- Section 2: Available Pool Orders (Tersedia untuk Diambil) --}}
        <div class="p-5 sm:p-6 rounded-2xl bg-[#131c2e] border border-slate-800 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        Pool Order Tersedia untuk Diambil
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Order yang sudah lunas dan menunggu booster yang siap mengerjakan.</p>
                </div>
                <span class="text-xs text-sky-400 font-bold">{{ $availableOrders->count() }} Tersedia</span>
            </div>

            @if($availableOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Kode Order</th>
                                <th class="pb-3 font-semibold">Layanan & Game</th>
                                <th class="pb-3 font-semibold">Server / Nickname</th>
                                <th class="pb-3 font-semibold">Waktu Masuk</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($availableOrders as $avail)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-mono font-bold text-cyan-400">#{{ $avail->order_code }}</td>
                                    <td class="py-4">
                                        <div class="font-bold text-white">{{ $avail->orderItems->first()?->item_name ?? 'Layanan Joki' }}</div>
                                        <div class="text-[11px] text-slate-400">Target: {{ $avail->orderItems->first()?->meta['target'] ?? '-' }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="text-white font-medium">{{ $avail->account_nickname ?: '-' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $avail->account_server ?: 'Server default' }}</div>
                                    </td>
                                    <td class="py-4 text-slate-400">{{ $avail->created_at->diffForHumans() }}</td>
                                    <td class="py-4 text-right">
                                        <form action="{{ route('orders.claim', $avail->order_code) }}" method="POST" class="inline">
                                            @csrf
                                            <x-button type="submit" variant="primary" size="sm">
                                                Ambil Pesanan (Claim)
                                            </x-button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center rounded-2xl bg-slate-950/40 border border-slate-800">
                    <p class="text-xs text-slate-400">Saat ini tidak ada order di pool. Semua pesanan sudah ditugaskan ke booster.</p>
                </div>
            @endif
        </div>
    </div>
</x-layout>
