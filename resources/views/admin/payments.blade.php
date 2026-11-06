<x-layout title="Otorisasi & Pembayaran — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        statusModal: false,
        paymentCode: '',
        currentStatus: '',
        paymentActionUrl: ''
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Otorisasi Pembayaran']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Stats Summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mb-6">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Dana Terverifikasi</span>
                <span class="text-xl font-black text-emerald-400 mt-1 block">Rp {{ number_format($paymentStats['total_paid'], 0, ',', '.') }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Transaksi Paid</span>
                <span class="text-xl font-black text-emerald-400 mt-1 block">{{ number_format($paymentStats['paid_count']) }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">Menunggu Bayar</span>
                <span class="text-xl font-black text-amber-400 mt-1 block">{{ number_format($paymentStats['pending_count']) }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block">Gagal / Kadaluarsa</span>
                <span class="text-xl font-black text-rose-400 mt-1 block">{{ number_format($paymentStats['failed_count']) }}</span>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.payments') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode transaksi bayar atau kode order..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full sm:w-44">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div class="w-full sm:w-40">
                    <select
                        name="payment_method"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Metode</option>
                        <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="bca" {{ request('payment_method') === 'bca' ? 'selected' : '' }}>BCA Virtual</option>
                        <option value="mandiri" {{ request('payment_method') === 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                        <option value="dana" {{ request('payment_method') === 'dana' ? 'selected' : '' }}>DANA</option>
                        <option value="gopay" {{ request('payment_method') === 'gopay' ? 'selected' : '' }}>GoPay</option>
                    </select>
                </div>

                <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto">
                    Cari
                </x-button>
            </form>
        </div>

        {{-- Payments Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Kode Transaksi</th>
                                <th class="pb-3 font-semibold">Pesanan Terkait</th>
                                <th class="pb-3 font-semibold">Customer</th>
                                <th class="pb-3 font-semibold">Metode</th>
                                <th class="pb-3 font-semibold">Nominal</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold">Waktu Dibuat / Lunas</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($payments as $pay)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-mono font-bold text-white text-sm">
                                        {{ $pay->payment_code }}
                                    </td>

                                    <td class="py-4 font-mono">
                                        @if($pay->order)
                                            <a href="{{ route('admin.orders.show', $pay->order->order_code) }}" class="text-cyan-400 hover:underline font-bold">
                                                #{{ $pay->order->order_code }}
                                            </a>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        <div class="font-bold text-white">{{ $pay->order?->customer_name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $pay->order?->customer_whatsapp ?? '-' }}</div>
                                    </td>

                                    <td class="py-4">
                                        <span class="px-2.5 py-1 rounded-xl bg-slate-950 border border-slate-800 font-mono font-bold uppercase text-[10px] text-slate-300">
                                            {{ $pay->payment_method }}
                                        </span>
                                    </td>

                                    <td class="py-4 font-mono font-bold text-emerald-400 text-sm">
                                        Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-4">
                                        @if($pay->status === 'paid')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                PAID
                                            </span>
                                        @elseif($pay->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                                PENDING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                                {{ strtoupper($pay->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 text-slate-400 text-[11px]">
                                        <div>{{ $pay->created_at->format('d/m/Y H:i') }}</div>
                                        @if($pay->paid_at)
                                            <div class="text-[10px] text-emerald-400 font-mono">Paid: {{ $pay->paid_at->format('d/m H:i') }}</div>
                                        @endif
                                    </td>

                                    <td class="py-4 text-right">
                                        <button
                                            type="button"
                                            @click="
                                                paymentCode = '{{ $pay->payment_code }}';
                                                currentStatus = '{{ $pay->status }}';
                                                paymentActionUrl = '{{ route('admin.payments.status', $pay->id) }}';
                                                statusModal = true;
                                            "
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition"
                                        >
                                            Ubah Status
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada data pembayaran ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Gunakan kata kunci atau filter status yang lain.</p>
                </div>
            @endif
        </div>

        {{-- Payment Status Modal --}}
        <div x-show="statusModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="statusModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Otorisasi Status Pembayaran</h3>
                    <button type="button" @click="statusModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="paymentActionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PATCH')

                    <div>
                        <span class="text-slate-400">Kode Pembayaran:</span>
                        <span class="font-mono font-bold text-white text-sm ml-1" x-text="paymentCode"></span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Status Pembayaran Baru</label>
                        <select name="status" x-model="currentStatus" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            <option value="pending">Pending (Menunggu Bayar)</option>
                            <option value="paid">Paid (Telah Diterima / Lunas)</option>
                            <option value="failed">Failed (Gagal)</option>
                            <option value="expired">Expired (Kadaluarsa)</option>
                            <option value="refunded">Refunded (Dana Dikembalikan)</option>
                        </select>
                    </div>

                    <p class="text-[11px] text-slate-400 bg-slate-950 p-3 rounded-xl border border-slate-800">
                        Catatan: Menandai status sebagai <strong>Paid</strong> akan otomatis memperbarui status order customer menjadi aktif dan mengirimkan notifikasi.
                    </p>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="statusModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 transition">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
