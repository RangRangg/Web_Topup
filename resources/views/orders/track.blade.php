<x-layout title="Lacak Status Pesanan & Invoice">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        {{-- Breadcrumb --}}
        <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-900 dark:hover:text-white">Beranda</a>
            <span>/</span>
            <span class="text-slate-900 dark:text-white font-semibold">Lacak Pesanan</span>
        </div>

        <div class="text-center max-w-lg mx-auto space-y-1">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-wide">Lacak Status Pesanan</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400">Masukkan kode invoice pesanan Anda untuk memantau status pembayaran dan pengiriman.</p>
        </div>

        {{-- Search Input Box --}}
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#111927] border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
            <form action="{{ route('tracking') }}" method="GET" class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500 font-bold font-mono">
                        #
                    </span>
                    <input
                        type="text"
                        name="code"
                        value="{{ request('code', $orderCode) }}"
                        placeholder="Contoh: AJ-8821 atau AJ-9923"
                        required
                        class="w-full pl-8 pr-4 py-2.5 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-sm font-mono tracking-wider text-slate-900 dark:text-white uppercase placeholder-slate-400 dark:placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                    />
                </div>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-400 text-white dark:text-slate-950 font-bold text-xs tracking-wide transition cursor-pointer shadow-sm"
                >
                    Lacak Pesanan
                </button>
            </form>

            <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                <span>Contoh nomor order:</span>
                <a href="{{ route('tracking', ['code' => 'AJ-8821']) }}" class="text-sky-600 dark:text-sky-400 font-mono hover:underline">AJ-8821</a>,
                <a href="{{ route('tracking', ['code' => 'AJ-9923']) }}" class="text-sky-600 dark:text-sky-400 font-mono hover:underline">AJ-9923</a>,
                <a href="{{ route('tracking', ['code' => 'AJ-7410']) }}" class="text-sky-600 dark:text-sky-400 font-mono hover:underline">AJ-7410</a>
            </div>
        </div>

        {{-- Tracking Result --}}
        @if($order)
            @php
                $firstItem = $order->orderItems->first();
                $itemName = $firstItem?->item_name ?? $order->package->name ?? 'Produk Game';
                $gameTitle = $firstItem?->gameProduct?->game?->name ?? $firstItem?->jokiService?->game?->name ?? $order->package?->game?->name ?? '';
                $paymentStatus = $order->payment?->status ?? $order->payment_status ?? 'pending';
            @endphp
            <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#111927] border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Kode Pesanan:</span>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white font-mono">#{{ $order->order_code }}</h2>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $itemName }}{{ $gameTitle ? ' • ' . $gameTitle : '' }}</span>
                    </div>

                    <div>
                        <x-badge :variant="$order->status_badge_variant" size="md">
                            {{ $order->status_label }}
                        </x-badge>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Nama Pemesan</span>
                        <span class="font-bold text-slate-900 dark:text-white mt-1 block truncate">{{ $order->customer_name }}</span>
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Total Pembayaran</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400 mt-1 block">{{ $order->formatted_total }}</span>
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Layanan / Produk</span>
                        <span class="font-bold text-slate-900 dark:text-white mt-1 block truncate">{{ $itemName }}</span>
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Status Bayar</span>
                        <span class="font-bold uppercase mt-1 block {{ $paymentStatus === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $paymentStatus }}
                        </span>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <a
                        href="{{ route('orders.show', $order->order_code) }}"
                        class="px-4 py-2 rounded-lg bg-sky-500 hover:bg-sky-400 text-white dark:text-slate-950 font-bold text-xs transition"
                    >
                        Buka Rincian Invoice &raquo;
                    </a>
                </div>
            </div>
        @elseif($orderCode)
            <div class="p-8 rounded-2xl bg-white dark:bg-[#111927] border border-slate-200 dark:border-slate-800 text-center space-y-3 shadow-sm">
                <div class="text-2xl">🔍</div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Nomor Pesanan Tidak Ditemukan</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 max-w-sm mx-auto">
                    Pesanan dengan kode "{{ $orderCode }}" tidak ditemukan di sistem kami. Mohon cek kembali invoice atau email konfirmasi Anda.
                </p>
                <div class="pt-2">
                    <a href="{{ route('tracking') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-semibold text-slate-800 dark:text-white transition">
                        Coba Lagi
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layout>
