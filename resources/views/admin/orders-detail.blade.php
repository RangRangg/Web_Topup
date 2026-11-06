<x-layout title="Detail Order #{{ $order->order_code }} — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ cancelModal: false }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Admin Panel', 'url' => route('admin.dashboard')],
            ['label' => 'Kelola Pesanan', 'url' => route('admin.orders')],
            ['label' => '#' . $order->order_code]
        ]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Top Title & Status Banner --}}
        <div class="mb-8 p-6 rounded-3xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs font-bold text-slate-500 uppercase">INSPEKSI PESANAN</span>
                    <span class="text-xs text-slate-500">•</span>
                    <span class="text-xs text-slate-400">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white font-mono flex items-center gap-3">
                    <span>#{{ $order->order_code }}</span>
                    <x-badge variant="{{ $order->status_badge_variant }}" size="md">
                        {{ $order->status_label }}
                    </x-badge>
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('orders.show', $order->order_code) }}"
                    target="_blank"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-cyan-400 bg-cyan-500/10 border border-cyan-500/30 hover:bg-cyan-500/20 transition flex items-center gap-1.5"
                >
                    <span>Tampilan Customer</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left 2 Columns: Order Details, Items, Credentials --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- 1. Items List --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Item & Layanan Pesanan</span>
                    </h3>

                    <div class="divide-y divide-slate-800/80">
                        @foreach($order->orderItems as $item)
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $item->item_name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        @if($item->jokiService)
                                            <span class="text-purple-400 font-semibold">Layanan Joki:</span> {{ $item->jokiService->game?->name }} • {{ $item->jokiService->target }}
                                        @elseif($item->gameProduct)
                                            <span class="text-cyan-400 font-semibold">Top Up Game:</span> {{ $item->gameProduct->game?->name }} (SKU: {{ $item->gameProduct->code ?? '-' }})
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-mono font-bold text-white text-sm">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $item->quantity }}x @ Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Financial Calculation --}}
                    <div class="mt-4 pt-4 border-t border-slate-800 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-mono text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-400">
                                <span>Diskon Promo ({{ $order->promotion?->code ?? 'VOUCHER' }})</span>
                                <span class="font-mono">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm font-bold text-white pt-2 border-t border-slate-800/80">
                            <span>Total Pembayaran</span>
                            <span class="font-mono text-emerald-400 text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- 2. Game Account Credentials --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Data Akun Game Customer</span>
                    </h3>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase font-mono block">User ID Game</span>
                            <span class="font-mono font-bold text-white text-sm mt-0.5 block select-all">{{ $order->account_id ?? '-' }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase font-mono block">Server / Zone ID</span>
                            <span class="font-mono font-bold text-white text-sm mt-0.5 block select-all">{{ $order->account_server ?? '-' }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase font-mono block">Nickname Game</span>
                            <span class="font-bold text-white text-sm mt-0.5 block">{{ $order->account_nickname ?? '-' }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <span class="text-[10px] text-slate-500 uppercase font-mono block">Login Via</span>
                            <span class="font-bold text-purple-300 text-sm mt-0.5 block uppercase font-mono">{{ $order->login_via ?? '-' }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 sm:col-span-2">
                            <span class="text-[10px] text-slate-500 uppercase font-mono block">Catatan Tambahan Customer</span>
                            <span class="text-slate-300 mt-0.5 block">{{ $order->account_notes ?? 'Tidak ada catatan tambahan.' }}</span>
                        </div>
                    </div>
                </div>

                {{-- 3. Customer Info --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Informasi Customer</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Lengkap:</span>
                            <span class="font-bold text-white text-sm">{{ $order->customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">WhatsApp:</span>
                            <span class="font-mono font-bold text-emerald-400 text-sm">{{ $order->customer_whatsapp }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Email:</span>
                            <span class="text-slate-200">{{ $order->customer_email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right 1 Column: Status Controller, Booster Assignment, Payment Status --}}
            <div class="space-y-6">
                {{-- Control Card: Update Status --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Update Status & Progress</span>
                    </h3>

                    <form action="{{ route('admin.orders.status', $order->order_code) }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Status Order</label>
                            <select name="order_status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                                <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->order_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="waiting_assignment" {{ $order->order_status === 'waiting_assignment' ? 'selected' : '' }}>Waiting Assignment</option>
                                <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="in_review" {{ $order->order_status === 'in_review' ? 'selected' : '' }}>In Review</option>
                                <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Status Pembayaran</label>
                            <select name="payment_status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                                <option value="pending" {{ $order->payment?->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment?->status === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                                <option value="failed" {{ $order->payment?->status === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                                <option value="expired" {{ $order->payment?->status === 'expired' ? 'selected' : '' }}>Expired (Kadaluarsa)</option>
                                <option value="refunded" {{ $order->payment?->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tugaskan Booster</label>
                            <select name="joki_profile_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                                <option value="">-- Tanpa / Lepas Booster --</option>
                                @foreach($boosters as $b)
                                    <option value="{{ $b->id }}" {{ $order->joki_profile_id == $b->id ? 'selected' : '' }}>
                                        {{ $b->nickname }} ({{ $b->user->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Catatan Progress Terkini</label>
                            <textarea name="progress_note" rows="3" placeholder="Contoh: Saat ini di Legend II bintang 3..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">{{ $order->progress_note }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 rounded-xl font-bold text-slate-950 bg-gradient-to-r from-cyan-400 to-indigo-400 hover:from-cyan-300 hover:to-indigo-300 transition shadow-lg shadow-cyan-950/40"
                        >
                            Simpan Update Order
                        </button>
                    </form>
                </div>

                {{-- Payment Oversight Card --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl text-xs">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Data Pembayaran</span>
                    </h3>

                    @if($order->payment)
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Kode Transaksi</span>
                                <span class="font-mono font-bold text-white">{{ $order->payment->payment_code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Metode Bayar</span>
                                <span class="uppercase font-bold text-slate-200">{{ $order->payment->payment_method }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Nominal</span>
                                <span class="font-mono font-bold text-emerald-400">Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Status Pembayaran</span>
                                <span class="font-bold text-white">{{ strtoupper($order->payment->status) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Waktu Bayar</span>
                                <span class="text-slate-300">{{ $order->payment->paid_at ? $order->payment->paid_at->format('d/m/y H:i') : '-' }}</span>
                            </div>

                            @if($order->payment->payment_proof)
                                <div class="mt-3 pt-3 border-t border-slate-800">
                                    <span class="text-slate-400 block mb-1">Bukti Transfer:</span>
                                    <a href="{{ asset($order->payment->payment_proof) }}" target="_blank" class="text-cyan-400 underline font-semibold">
                                        Lihat Bukti Transfer Customer &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-slate-500 py-3 text-center">Data pembayaran belum tercatat.</div>
                    @endif
                </div>

                {{-- Pro Booster Assigned Card --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl text-xs">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Pro Booster Ditugaskan</span>
                    </h3>

                    @if($order->jokiProfile)
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-600 flex items-center justify-center text-slate-950 font-black text-base">
                                {{ strtoupper(substr($order->jokiProfile->nickname, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">{{ $order->jokiProfile->nickname }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">Booster: {{ $order->jokiProfile->user->name }}</div>
                                <div class="text-[10px] text-purple-400 font-semibold mt-0.5">
                                    ⭐ {{ number_format($order->jokiProfile->rating, 1) }} • {{ $order->jokiProfile->completed_orders_count }} Pesanan Selesai
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-slate-500 text-center py-2">
                            Belum ada booster yang ditugaskan untuk order ini. Gunakan form di atas untuk menugaskan.
                        </div>
                    @endif
                </div>

                {{-- WhatsApp Notification Center Card --}}
                <div class="p-6 rounded-3xl bg-slate-900 border border-emerald-500/30 shadow-xl text-xs space-y-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider pb-3 border-b border-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Notifikasi & Dispatch WhatsApp</span>
                    </h3>

                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Kirim notifikasi atau invoice secara langsung via WhatsApp dengan pesan otomatis terformat rapi.
                    </p>

                    <div class="space-y-2 pt-1">
                        @if(!empty($waUrls['customer_pending']))
                            <a
                                href="{{ $waUrls['customer_pending'] }}"
                                target="_blank"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-300 font-semibold transition"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span>WA Pembeli (Invoice Pending)</span>
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $waUrls['customer_phone'] }} &rarr;</span>
                            </a>
                        @endif

                        @if(!empty($waUrls['customer_success']))
                            <a
                                href="{{ $waUrls['customer_success'] }}"
                                target="_blank"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-semibold transition"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span>WA Pembeli (Konfirmasi Sukses)</span>
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $waUrls['customer_phone'] }} &rarr;</span>
                            </a>
                        @endif

                        @if(!empty($waUrls['is_joki']) && !empty($waUrls['booster']))
                            <a
                                href="{{ $waUrls['booster'] }}"
                                target="_blank"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/30 text-purple-300 font-semibold transition"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                    <span>Notif ke WA Penjoki (Tugas Baru)</span>
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $waUrls['booster_phone'] }} &rarr;</span>
                            </a>
                        @endif

                        @if(!empty($waUrls['admin']))
                            <a
                                href="{{ $waUrls['admin'] }}"
                                target="_blank"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 font-semibold transition"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                                    <span>Buka Notif di WA Admin</span>
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $waUrls['admin_phone'] }} &rarr;</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
