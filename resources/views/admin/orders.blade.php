<x-layout title="Admin - Kelola Pesanan & Jasa Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        statusModal: false,
        assignModal: false,
        activeOrderCode: '',
        activeOrderId: null,
        statusActionUrl: '',
        assignActionUrl: '',
        currentOrderStatus: '',
        currentPaymentStatus: '',
        currentProgressNote: '',
        selectedBoosterId: ''
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Kelola Pesanan']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5 mb-6">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Pesanan</span>
                <span class="text-xl font-black text-white mt-1 block">{{ number_format($stats['total_orders']) }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">Menunggu Bayar</span>
                <span class="text-xl font-black text-amber-400 mt-1 block">{{ number_format($stats['pending_orders']) }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-400 block">Sedang Diproses</span>
                <span class="text-xl font-black text-cyan-400 mt-1 block">{{ number_format($stats['processing_orders']) }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Total Selesai</span>
                <span class="text-xl font-black text-emerald-400 mt-1 block">{{ number_format($stats['completed_orders']) }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400 block">Total Omset</span>
                <span class="text-base sm:text-lg font-black text-indigo-400 mt-1 block truncate">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.orders') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode order (#ORD-...), nama customer, email, atau nomor WA..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full md:w-44">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Status Order</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="waiting_assignment" {{ request('status') === 'waiting_assignment' ? 'selected' : '' }}>Waiting Assignment</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>In Review</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="w-full md:w-36">
                    <select
                        name="type"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="joki" {{ request('type') === 'joki' ? 'selected' : '' }}>Jasa Joki</option>
                        <option value="topup" {{ request('type') === 'topup' ? 'selected' : '' }}>Top Up</option>
                    </select>
                </div>

                <x-button type="submit" variant="primary" size="sm" class="w-full md:w-auto">
                    Cari
                </x-button>
            </form>
        </div>

        {{-- Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Order ID</th>
                                <th class="pb-3 font-semibold">Customer</th>
                                <th class="pb-3 font-semibold">Layanan / Item</th>
                                <th class="pb-3 font-semibold">Total Bayar</th>
                                <th class="pb-3 font-semibold">Status Bayar</th>
                                <th class="pb-3 font-semibold">Status Order</th>
                                <th class="pb-3 font-semibold">Pro Booster</th>
                                <th class="pb-3 font-semibold text-right">Aksi & Kontrol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($orders as $ord)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-mono font-bold text-cyan-400">
                                        <a href="{{ route('admin.orders.show', $ord->order_code) }}" class="hover:underline flex items-center gap-1">
                                            #{{ $ord->order_code }}
                                            <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                        <div class="text-[10px] text-slate-500 font-sans mt-0.5">
                                            {{ $ord->created_at->format('d/m/y H:i') }}
                                        </div>
                                    </td>

                                    <td class="py-4">
                                        <div class="font-bold text-white">{{ $ord->customer_name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $ord->customer_whatsapp }}</div>
                                    </td>

                                    <td class="py-4 text-slate-300">
                                        @if($ord->orderItems->first())
                                            <div class="font-semibold text-white max-w-[150px] truncate">
                                                {{ $ord->orderItems->first()->item_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 uppercase font-mono">
                                                {{ $ord->orderItems->first()->joki_service_id ? 'JASA JOKI' : 'TOP UP GAME' }}
                                            </div>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>

                                    <td class="py-4 font-mono font-bold text-emerald-400 text-sm">
                                        Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-4">
                                        @if($ord->payment?->status === 'paid')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">PAID</span>
                                        @elseif($ord->payment?->status === 'pending')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">PENDING</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">{{ strtoupper($ord->payment?->status ?? 'UNPAID') }}</span>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        <x-badge variant="{{ $ord->status_badge_variant }}" size="sm">
                                            {{ $ord->status_label }}
                                        </x-badge>
                                    </td>

                                    <td class="py-4">
                                        @if($ord->jokiProfile)
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-5 h-5 rounded-full bg-purple-500/20 text-purple-300 font-bold text-[10px] flex items-center justify-center">
                                                    {{ strtoupper(substr($ord->jokiProfile->nickname, 0, 1)) }}
                                                </div>
                                                <span class="font-semibold text-purple-300 text-xs">{{ $ord->jokiProfile->nickname }}</span>
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                @click="
                                                    activeOrderCode = '{{ $ord->order_code }}';
                                                    activeOrderId = {{ $ord->id }};
                                                    assignActionUrl = '{{ route('admin.orders.assign', $ord->order_code) }}';
                                                    assignModal = true;
                                                "
                                                class="px-2 py-0.5 rounded-lg text-[10px] font-bold text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 transition"
                                            >
                                                + Tugaskan
                                            </button>
                                        @endif
                                    </td>

                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a
                                                href="{{ route('admin.orders.show', $ord->order_code) }}"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-cyan-400 hover:text-white bg-cyan-500/10 hover:bg-cyan-600 border border-cyan-500/30 transition"
                                            >
                                                Detail
                                            </a>

                                            <button
                                                type="button"
                                                @click="
                                                    activeOrderCode = '{{ $ord->order_code }}';
                                                    currentOrderStatus = '{{ $ord->order_status }}';
                                                    currentPaymentStatus = '{{ $ord->payment?->status ?? 'pending' }}';
                                                    currentProgressNote = '{{ addslashes($ord->progress_note ?? '') }}';
                                                    selectedBoosterId = '{{ $ord->joki_profile_id ?? '' }}';
                                                    statusActionUrl = '{{ route('admin.orders.status', $ord->order_code) }}';
                                                    statusModal = true;
                                                "
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition"
                                            >
                                                Status
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada pesanan ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Coba gunakan kriteria filter status atau pencarian yang lain.</p>
                </div>
            @endif
        </div>

        {{-- Status Update Modal --}}
        <div x-show="statusModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="statusModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Update Status Pesanan</h3>
                    <button type="button" @click="statusModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="statusActionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PATCH')

                    <div>
                        <span class="text-slate-400">Kode Pesanan:</span>
                        <span class="font-mono font-bold text-cyan-400 text-sm ml-1" x-text="'#' + activeOrderCode"></span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Status Pengerjaan (Order Status)</label>
                        <select name="order_status" x-model="currentOrderStatus" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            <option value="pending">Pending (Menunggu Pembayaran)</option>
                            <option value="paid">Paid (Terbayar)</option>
                            <option value="waiting_assignment">Waiting Assignment (Menunggu Booster)</option>
                            <option value="processing">Processing (Sedang Dikerjakan)</option>
                            <option value="in_review">In Review (Pengecekan Customer)</option>
                            <option value="completed">Completed (Selesai)</option>
                            <option value="cancelled">Cancelled (Dibatalkan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Status Pembayaran</label>
                        <select name="payment_status" x-model="currentPaymentStatus" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid (Lunas)</option>
                            <option value="failed">Failed (Gagal)</option>
                            <option value="expired">Expired (Kadaluarsa)</option>
                            <option value="refunded">Refunded (Dikembalikan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tugaskan / Ganti Booster</label>
                        <select name="joki_profile_id" x-model="selectedBoosterId" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            <option value="">-- Tetap / Jangan Ubah Booster --</option>
                            @foreach($boosters as $b)
                                <option value="{{ $b->id }}">{{ $b->nickname }} ({{ $b->user->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Catatan Progress (Opsional)</label>
                        <textarea name="progress_note" rows="2" x-model="currentProgressNote" placeholder="Update progress pengerjaan..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="statusModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-cyan-400 hover:bg-cyan-300 transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Booster Assignment Modal --}}
        <div x-show="assignModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="assignModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Tugaskan Pro Booster</h3>
                    <button type="button" @click="assignModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="assignActionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <p class="text-slate-300 mb-2">Pilih booster untuk order <strong class="text-cyan-400" x-text="'#' + activeOrderCode"></strong>:</p>
                        <select name="joki_profile_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            <option value="">Pilih Pro Booster</option>
                            @foreach($boosters as $b)
                                <option value="{{ $b->id }}">{{ $b->nickname }} — (⭐ {{ number_format($b->rating, 1) }} • {{ $b->completed_orders_count }} Selesai)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="assignModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-purple-400 hover:bg-purple-300 transition">Tugaskan Booster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
