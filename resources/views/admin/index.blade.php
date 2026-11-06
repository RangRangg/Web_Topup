<x-layout title="Admin Dashboard — Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Overview']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Top Statistics Cards (7 Core Admin Statistics) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3.5 mb-8">
            {{-- 1. Total Revenue --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-emerald-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Revenue</span>
                    <span class="p-1 rounded-lg bg-emerald-500/10 text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-base sm:text-lg font-black text-emerald-400 truncate">
                    Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Dari pesanan terbayar</span>
            </div>

            {{-- 2. Total Orders --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-cyan-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Orders</span>
                    <span class="p-1 rounded-lg bg-cyan-500/10 text-cyan-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-white">
                    {{ number_format($stats['total_orders']) }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Top Up & Jasa Joki</span>
            </div>

            {{-- 3. Pending Orders --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-amber-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Pending Orders</span>
                    <span class="p-1 rounded-lg bg-amber-500/10 text-amber-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-amber-400">
                    {{ number_format($stats['pending_orders']) }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Menunggu aksi/bayar</span>
            </div>

            {{-- 4. Completed Orders --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Completed</span>
                    <span class="p-1 rounded-lg bg-indigo-500/10 text-indigo-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-indigo-400">
                    {{ number_format($stats['completed_orders']) }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Selesai sukses</span>
            </div>

            {{-- 5. Customers --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-sky-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-400">Customers</span>
                    <span class="p-1 rounded-lg bg-sky-500/10 text-sky-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-sky-400">
                    {{ number_format($stats['customers_count']) }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Pengguna aktif</span>
            </div>

            {{-- 6. Active Jokis --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-purple-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400">Active Jokis</span>
                    <span class="p-1 rounded-lg bg-purple-500/10 text-purple-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-purple-400">
                    {{ number_format($stats['active_jokis_count']) }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Pro booster siap</span>
            </div>

            {{-- 7. Pending Payments --}}
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-rose-500/40 transition relative overflow-hidden group shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Pending Pay</span>
                    <span class="p-1 rounded-lg bg-rose-500/10 text-rose-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                </div>
                <div class="text-xl font-black text-rose-400">
                    {{ number_format($stats['pending_payments_count']) }}
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Menunggu verifikasi</span>
            </div>
        </div>

        {{-- Quick Management Cards Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3 mb-8">
            <a href="{{ route('admin.users') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-rose-300">Kelola User</div>
                <div class="text-[10px] text-slate-500">Role & status aktif</div>
            </a>

            <a href="{{ route('admin.games') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-cyan-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-cyan-300">Katalog Games</div>
                <div class="text-[10px] text-slate-500">Kategori & banner</div>
            </a>

            <a href="{{ route('admin.products') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-indigo-300">Produk Top Up</div>
                <div class="text-[10px] text-slate-500">Harga & stok diamond</div>
            </a>

            <a href="{{ route('admin.joki-services') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-purple-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-purple-300">Layanan Joki</div>
                <div class="text-[10px] text-slate-500">Paket rank & estimasi</div>
            </a>

            <a href="{{ route('admin.promotions') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-amber-300">Voucher Diskon</div>
                <div class="text-[10px] text-slate-500">Kode promo & kuota</div>
            </a>

            <a href="{{ route('admin.audit-logs') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-rose-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-rose-300">Audit Trail</div>
                <div class="text-[10px] text-slate-500">Log histori aktivitas</div>
            </a>

            <a href="{{ route('admin.telegram') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-sky-500/40 transition group">
                <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <div class="text-xs font-bold text-white group-hover:text-sky-300">Telegram API</div>
                <div class="text-[10px] text-slate-500">Koneksi & ganti token</div>
            </a>
        </div>

        {{-- Two-Column Recent Feeds: Latest Orders & Latest Payments --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Latest Orders (2 Cols) --}}
            <div class="lg:col-span-2 rounded-3xl bg-slate-900/90 border border-slate-800 p-6 shadow-xl">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>Pesanan Terbaru</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-500/15 text-cyan-400 font-mono">{{ $recentOrders->count() }} Order</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Antrean transaksi terkini dari customer.</p>
                    </div>

                    <a href="{{ route('admin.orders') }}" class="text-xs font-bold text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                        <span>Kelola Semua</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                @if($recentOrders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800/80">
                                    <th class="pb-3 font-semibold">Kode</th>
                                    <th class="pb-3 font-semibold">Customer</th>
                                    <th class="pb-3 font-semibold">Item</th>
                                    <th class="pb-3 font-semibold">Total</th>
                                    <th class="pb-3 font-semibold">Status Order</th>
                                    <th class="pb-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-slate-200">
                                @foreach($recentOrders as $ord)
                                    <tr class="hover:bg-slate-950/40 transition">
                                        <td class="py-3.5 font-mono font-bold text-cyan-400">
                                            <a href="{{ route('admin.orders.show', $ord->order_code) }}" class="hover:underline">
                                                #{{ $ord->order_code }}
                                            </a>
                                        </td>
                                        <td class="py-3.5">
                                            <div class="font-bold text-white">{{ $ord->customer_name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $ord->customer_whatsapp }}</div>
                                        </td>
                                        <td class="py-3.5 text-slate-300">
                                            @if($ord->orderItems->first())
                                                <span class="truncate max-w-[140px] block font-medium">
                                                    {{ $ord->orderItems->first()->item_name }}
                                                </span>
                                            @else
                                                <span class="text-slate-500">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 font-bold text-white font-mono">
                                            Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5">
                                            <x-badge variant="{{ $ord->status_badge_variant }}" size="sm">
                                                {{ $ord->status_label }}
                                            </x-badge>
                                        </td>
                                        <td class="py-3.5 text-right">
                                            <a
                                                href="{{ route('admin.orders.show', $ord->order_code) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold text-cyan-400 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 transition"
                                            >
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 text-center text-slate-500 text-xs">
                        Belum ada pesanan terbaru.
                    </div>
                @endif
            </div>

            {{-- Right: Recent Payments (1 Col) --}}
            <div class="rounded-3xl bg-slate-900/90 border border-slate-800 p-6 shadow-xl">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>Pembayaran</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-mono">Real-time</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Status verifikasi gateway/manual.</p>
                    </div>

                    <a href="{{ route('admin.payments') }}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                        <span>Semua</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                @if($recentPayments->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentPayments as $pay)
                            <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-white truncate">{{ $pay->payment_code }}</span>
                                        <span class="uppercase text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">{{ $pay->payment_method }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Order #{{ $pay->order?->order_code ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        {{ $pay->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <div class="font-mono text-xs font-bold text-emerald-400">
                                        Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="mt-1">
                                        @if($pay->status === 'paid')
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Paid</span>
                                        @elseif($pay->status === 'pending')
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">Pending</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">{{ ucfirst($pay->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center text-slate-500 text-xs">
                        Belum ada transaksi pembayaran.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
