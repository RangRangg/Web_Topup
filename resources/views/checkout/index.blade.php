<x-layout title="Konfirmasi Checkout — Aethereal Joki">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Games', 'url' => route('games')],
            ['label' => $product->game->name, 'url' => route('games.show', $product->game->slug)],
            ['label' => 'Checkout', 'url' => route('checkout')],
        ]" />

        <div class="mt-4 mb-8">
            <x-badge variant="primary" size="sm" class="mb-2">RINGKASAN & KONFIRMASI</x-badge>
            <h1 class="text-3xl font-black text-white tracking-tight">Checkout Pesanan Top Up</h1>
            <p class="text-sm text-slate-400 mt-1">Periksa kembali detail pesanan Anda sebelum menyelesaikan pembayaran.</p>
        </div>

        @if(session('error'))
            <div class="mb-6">
                <x-alert type="error" :dismissible="true">
                    {{ session('error') }}
                </x-alert>
            </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}" />

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                {{-- Left: Customer & Account Info Form --}}
                <div class="md:col-span-7 space-y-6">
                    {{-- Account Target Box --}}
                    <div class="rounded-2xl bg-[#131c2e] border border-slate-800 p-5 sm:p-6 space-y-4">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>1.</span> Akun Tujuan Top Up
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label for="account_id" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    User ID <span class="text-rose-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="account_id"
                                    name="account_id"
                                    value="{{ old('account_id', $accountId) }}"
                                    placeholder="Contoh: 12847291"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:border-sky-500 focus:outline-none font-mono"
                                />
                            </div>

                            <div>
                                <label for="account_server" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    Zone / Server ID
                                </label>
                                <input
                                    type="text"
                                    id="account_server"
                                    name="account_server"
                                    value="{{ old('account_server', $accountServer) }}"
                                    placeholder="Contoh: 2048"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:border-sky-500 focus:outline-none font-mono"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="account_nickname" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Nickname Game (Opsional)
                            </label>
                            <input
                                type="text"
                                id="account_nickname"
                                name="account_nickname"
                                value="{{ old('account_nickname', $accountNickname) }}"
                                placeholder="Nickname game kamu"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:border-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    {{-- Customer Contact Information --}}
                    <div class="rounded-2xl bg-[#131c2e] border border-slate-800 p-5 sm:p-6 space-y-4">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>2.</span> Informasi Kontak Pembeli
                        </h3>

                        <div class="space-y-3 pt-1">
                            <div>
                                <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                    Nama Lengkap <span class="text-rose-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="customer_name"
                                    name="customer_name"
                                    value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                                    required
                                    placeholder="Nama Anda"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:border-sky-500 focus:outline-none"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="customer_email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                        Email <span class="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="customer_email"
                                        name="customer_email"
                                        value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                        required
                                        placeholder="nama@email.com"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:border-sky-500 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label for="customer_whatsapp" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                        WhatsApp <span class="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="customer_whatsapp"
                                        name="customer_whatsapp"
                                        value="{{ old('customer_whatsapp', auth()->user()->phone ?? '08') }}"
                                        required
                                        placeholder="08123456789"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:border-sky-500 focus:outline-none font-mono"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method Selection --}}
                    <div class="rounded-2xl bg-[#131c2e] border border-slate-800 p-5 sm:p-6 space-y-4">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>3.</span> Metode Pembayaran
                        </h3>

                        <div class="space-y-2.5 pt-1">
                            @foreach($paymentMethods as $mKey => $mInfo)
                                <label class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-sky-500/50 flex items-center justify-between cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="{{ $mKey }}"
                                            {{ $paymentMethod === $mKey ? 'checked' : '' }}
                                            class="text-sky-500 focus:ring-sky-500"
                                        />
                                        <span class="text-sm font-bold text-white">{{ $mInfo['name'] }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-sky-400">+ Rp {{ number_format($mInfo['fee'], 0, ',', '.') }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label for="promo_code" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Kode Promo Voucher (Opsional)
                            </label>
                            <input
                                type="text"
                                id="promo_code"
                                name="promo_code"
                                value="{{ old('promo_code', 'AETHER2026') }}"
                                placeholder="AETHER2026"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-sky-400 font-mono font-bold uppercase focus:border-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                {{-- Right: Order Summary Sidebar --}}
                <div class="md:col-span-5 sticky top-24 space-y-4">
                    <div class="rounded-2xl p-5 sm:p-6 bg-[#131c2e] border border-slate-800 space-y-5">
                        <div class="flex items-center gap-3.5 border-b border-slate-800 pb-4">
                            <img src="{{ $product->game->icon_url }}" alt="{{ $product->game->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-700" />
                            <div>
                                <h3 class="text-sm font-bold text-white">{{ $product->game->name }}</h3>
                                <p class="text-xs text-slate-400">{{ $product->name }}</p>
                            </div>
                        </div>

                        {{-- Quantity field --}}
                        <div class="flex items-center justify-between text-xs text-slate-300">
                            <span>Jumlah Pembelian:</span>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    name="quantity"
                                    value="{{ $quantity }}"
                                    min="1"
                                    max="100"
                                    class="w-16 px-2 py-1 rounded bg-slate-950 border border-slate-800 text-center font-bold text-white text-xs"
                                />
                                <span>pcs</span>
                            </div>
                        </div>

                        {{-- Price Calculations --}}
                        <div class="space-y-2.5 pt-3 border-t border-slate-800 text-xs">
                            <div class="flex justify-between text-slate-400">
                                <span>Harga Satuan:</span>
                                <span class="font-semibold text-white">Rp {{ number_format($unitPrice, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between text-slate-400">
                                <span>Subtotal:</span>
                                <span class="font-semibold text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between text-slate-400">
                                <span>Biaya Layanan Payment:</span>
                                <span class="font-semibold text-sky-400">Rp {{ number_format($paymentFee, 0, ',', '.') }}</span>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center mt-3">
                                <span class="text-xs font-bold text-slate-400 uppercase">Total Tagihan:</span>
                                <span class="text-xl font-black text-white">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <x-button type="submit" variant="primary" size="lg" class="w-full font-bold">
                            Konfirmasi &amp; Bayar Sekarang &raquo;
                        </x-button>

                        <p class="text-[11px] text-center text-slate-400 leading-relaxed">
                            Order dibuat dengan status PENDING dan diarahkan ke instruksi pembayaran resmi.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>
