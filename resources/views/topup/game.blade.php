<x-layout :title="'Top Up ' . $game->name . ' — Instan Resmi Aethereal'">
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
        x-data="{
            products: {{ Js::from($products) }},
            paymentMethods: {{ Js::from($paymentMethods) }},
            selectedProductId: {{ (int) request('product_id', $products->first()->id ?? 0) }},
            selectedMethod: 'qris',
            quantity: 1,
            promoCode: '',
            showGuideModal: false,
            gameSlug: '{{ $game->slug }}',
            accountId: '{{ old('account_id', request('account_id', '')) }}',
            accountServer: '{{ old('account_server', request('account_server', '')) }}',
            accountNickname: '{{ old('account_nickname', request('account_nickname', '')) }}',
            isCheckingNick: false,
            nickChecked: false,
            nickSuccess: false,
            nickMessage: '',
            verifiedCountry: '',
            checkNickname() {
                const id = (this.accountId || '').trim();
                const server = (this.accountServer || '').trim();
                if (!id) {
                    this.nickChecked = false;
                    this.nickMessage = '';
                    return;
                }
                
                // For MLBB, wait until server has at least 3 digits
                if (this.gameSlug === 'mobile-legends' && (!server || server.length < 3)) {
                    return;
                }
                
                this.isCheckingNick = true;
                this.nickChecked = false;
                this.nickMessage = '';
                
                const url = new URL('{{ route('api.check-account') }}', window.location.origin);
                url.searchParams.set('game', this.gameSlug);
                url.searchParams.set('account_id', id);
                if (server) url.searchParams.set('account_server', server);
                
                fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.isCheckingNick = false;
                    this.nickChecked = true;
                    if (data.success && data.nickname) {
                        this.nickSuccess = true;
                        this.accountNickname = data.nickname;
                        this.verifiedCountry = data.country || '';
                        this.nickMessage = 'Akun terverifikasi: ' + data.nickname;
                    } else if (data.success) {
                        this.nickSuccess = true;
                        this.nickMessage = data.message || 'Format ID valid.';
                    } else {
                        this.nickSuccess = false;
                        this.nickMessage = data.message || 'Akun tidak ditemukan. Cek kembali User ID & Server.';
                    }
                })
                .catch(() => {
                    this.isCheckingNick = false;
                    this.nickChecked = true;
                    this.nickSuccess = false;
                    this.nickMessage = 'Gagal memverifikasi akun. Silakan coba lagi.';
                });
            },
            init() {
                if (this.accountId && (this.gameSlug !== 'mobile-legends' || this.accountServer)) {
                    this.checkNickname();
                }
            },
            get selectedProduct() {
                return this.products.find(p => p.id == this.selectedProductId) || this.products[0] || null;
            },
            get unitPrice() {
                return this.selectedProduct ? Number(this.selectedProduct.price) : 0;
            },
            get subtotal() {
                return this.unitPrice * this.quantity;
            },
            get paymentFee() {
                const method = this.paymentMethods[this.selectedMethod];
                if (!method) return 0;
                if (method.fee_type === 'flat') return Number(method.fee);
                if (method.fee_type === 'percentage') return Math.round((this.subtotal * Number(method.fee)) / 100);
                return 0;
            },
            get totalAmount() {
                return Math.max(0, this.subtotal + this.paymentFee);
            },
            calculateMethodTotal(mKey) {
                const method = this.paymentMethods[mKey];
                if (!method) return this.subtotal;
                let fee = 0;
                if (method.fee_type === 'flat') fee = Number(method.fee);
                if (method.fee_type === 'percentage') fee = Math.round((this.subtotal * Number(method.fee)) / 100);
                return Math.max(0, this.subtotal + fee);
            },
            formatRupiah(val) {
                return 'Rp ' + Number(val).toLocaleString('id-ID');
            },
            increaseQty() {
                if (this.quantity < 100) this.quantity++;
            },
            decreaseQty() {
                if (this.quantity > 1) this.quantity--;
            }
        }"
    >
        {{-- Breadcrumb Navigation --}}
        <div class="mb-4 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-900 dark:hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('topup') }}" class="hover:text-slate-900 dark:hover:text-white">Top Up</a>
            <span>/</span>
            <span class="text-slate-900 dark:text-white font-semibold">{{ $game->name }}</span>
        </div>

        {{-- Game Switcher Tabs --}}
        <div class="mb-6 overflow-x-auto scrollbar-none pb-1 flex items-center gap-2">
            @foreach($allGames as $g)
                <a
                    href="{{ route('topup.game', $g->slug) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $g->id === $game->id ? 'bg-yellow-400 text-slate-950 font-black shadow-md shadow-yellow-400/20' : 'bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white shadow-xs' }}"
                >
                    <img src="{{ $g->icon_url }}" alt="{{ $g->name }}" class="w-4 h-4 rounded-md object-cover" />
                    <span>{{ $g->name }}</span>
                </a>
            @endforeach
        </div>

        {{-- Flash messages / Validation errors --}}
        @if(session('error'))
            <div class="mb-6">
                <x-alert type="error" :dismissible="true">
                    {{ session('error') }}
                </x-alert>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6">
                <x-alert type="error" :dismissible="true">
                    <ul class="list-disc pl-4 space-y-1 text-xs">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            </div>
        @endif

        {{-- Main Form Container --}}
        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pb-24 lg:pb-0">
                {{-- Left Column: 5 Top Up Order Steps (Aethereal Step UI with Yellow Box) --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- Game Info Card Header --}}
                    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-5 shadow-sm">
                        <img
                            src="{{ $game->icon_url }}"
                            alt="{{ $game->name }}"
                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shrink-0 border border-slate-200 dark:border-slate-700 bg-slate-950 shadow-md"
                        />
                        <div class="space-y-1 text-center sm:text-left flex-1">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $game->name }}</h1>
                                @if($game->badge)
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-yellow-400/20 text-yellow-600 dark:text-yellow-300 border border-yellow-400/30">
                                        {{ $game->badge }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400">{{ $game->publisher }} &bull; Top Up Legal 100% Bergaransi Resmi Aethereal</p>
                            <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3 text-[11px] text-slate-600 dark:text-slate-300">
                                <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span> Pengiriman Instan 1 Detik
                                </span>
                                <span>&bull;</span>
                                <span class="text-slate-500 dark:text-slate-400">Layanan Otomatis 24 Jam</span>
                            </div>
                        </div>
                    </div>

                    {{-- =========================================================================
                         LANGKAH 1: MASUKKAN DATA AKUN (Matching Image 1: Yellow Box '1')
                         ========================================================================= --}}
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] shadow-sm">
                        {{-- Step Header Bar with Yellow Square Box --}}
                        <div class="bg-slate-50 dark:bg-[#1b1a23] px-5 py-3.5 flex items-center justify-between border-b border-slate-200 dark:border-[#343343]">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-sm bg-yellow-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                    1
                                </span>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Masukkan Data Akun</h2>
                            </div>
                            <button
                                type="button"
                                @click="showGuideModal = true"
                                class="text-xs font-semibold text-amber-600 dark:text-yellow-400 hover:text-amber-500 dark:hover:text-yellow-300 flex items-center gap-1 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Petunjuk ID</span>
                            </button>
                        </div>

                        {{-- Step Body: ID, Server, and Real-Time In-Game Nickname Lookup --}}
                        <div class="p-5 sm:p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- ID Field --}}
                                <div>
                                    <label for="account_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        ID <span class="text-rose-500 dark:text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="account_id"
                                            name="account_id"
                                            x-model="accountId"
                                            @input.debounce.500ms="checkNickname()"
                                            @blur="checkNickname()"
                                            placeholder="241254030"
                                            required
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-400 focus:outline-none font-mono transition"
                                        />
                                    </div>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">ID profil game akun Anda.</span>
                                </div>

                                {{-- Server Field --}}
                                <div>
                                    <label for="account_server" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Server <span class="text-slate-500 dark:text-slate-400 font-normal">(Jika ada)</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="account_server"
                                            name="account_server"
                                            x-model="accountServer"
                                            @input.debounce.500ms="checkNickname()"
                                            @blur="checkNickname()"
                                            placeholder="9259"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-400 focus:outline-none font-mono transition"
                                        />
                                    </div>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Di dalam kurung untuk game seperti Mobile Legends.</span>
                                </div>
                            </div>

                            {{-- Nickname Field with Real-Time Lookup State --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="account_nickname" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Nickname Game <span class="text-slate-500 dark:text-slate-400 font-normal">(Opsional)</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        {{-- Loading Indicator --}}
                                        <div x-show="isCheckingNick" class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-yellow-400">
                                            <svg class="animate-spin w-3 h-3 text-amber-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span class="text-[11px] font-medium">Mengecek akun...</span>
                                        </div>
                                        {{-- Manual Check Button --}}
                                        <button
                                            type="button"
                                            @click="checkNickname()"
                                            :disabled="isCheckingNick || !accountId"
                                            class="text-[11px] font-bold text-amber-600 dark:text-yellow-400 hover:text-amber-500 dark:hover:text-yellow-300 disabled:opacity-40 transition cursor-pointer"
                                        >
                                            Cek Nickname &raquo;
                                        </button>
                                    </div>
                                </div>

                                <div class="relative">
                                    <input
                                        type="text"
                                        id="account_nickname"
                                        name="account_nickname"
                                        x-model="accountNickname"
                                        placeholder="Contoh: AetherealKael"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-400 focus:outline-none transition"
                                    />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center" x-show="nickChecked && nickSuccess && accountNickname">
                                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">✓</span>
                                    </div>
                                </div>

                                {{-- Verification Success Banner --}}
                                <div
                                    x-show="nickChecked && nickSuccess && accountNickname"
                                    x-cloak
                                    class="mt-2.5 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-xs text-emerald-400 animate-fadeIn"
                                >
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <span class="font-bold">Akun Terverifikasi:</span>
                                            <span class="font-black text-white ml-1 text-sm" x-text="accountNickname"></span>
                                            <span class="text-slate-400 text-[11px] ml-1.5" x-show="verifiedCountry" x-text="'(' + verifiedCountry + ')'"></span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-500 text-slate-950 shadow-sm">
                                        ✓ Valid
                                    </span>
                                </div>

                                {{-- Verification Error Banner --}}
                                <div
                                    x-show="nickChecked && !nickSuccess && nickMessage"
                                    x-cloak
                                    class="mt-2.5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center gap-2 text-xs text-rose-400 animate-fadeIn"
                                >
                                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span x-text="nickMessage"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =========================================================================
                         LANGKAH 2: PILIH NOMINAL TOP UP (Yellow Box '2')
                         ========================================================================= --}}
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] shadow-sm">
                        <div class="bg-slate-50 dark:bg-[#1b1a23] px-5 py-3.5 flex items-center justify-between border-b border-slate-200 dark:border-[#343343]">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-sm bg-yellow-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                    2
                                </span>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Pilih Nominal Top Up</h2>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Pilih item yang ingin dibeli</span>
                        </div>

                        {{-- Hidden input product id --}}
                        <input type="hidden" name="product_id" :value="selectedProductId" />

                        <div class="p-5 sm:p-6">
                            @if($products->isEmpty())
                                <x-empty-state
                                    title="Nominal Belum Tersedia"
                                    description="Produk top up untuk game ini sedang dalam pembaruan."
                                />
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($products as $prod)
                                        <button
                                            type="button"
                                            @click="selectedProductId = {{ $prod->id }}"
                                            class="p-3.5 rounded-xl text-left border transition-all cursor-pointer flex flex-col justify-between"
                                            :class="selectedProductId == {{ $prod->id }} 
                                                ? 'bg-yellow-400/10 border-yellow-500 dark:border-yellow-400 ring-1 ring-yellow-400' 
                                                : 'bg-slate-50 dark:bg-[#1b1a23] border-slate-200 dark:border-[#343343] hover:border-slate-300 dark:hover:border-slate-600'"
                                        >
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 truncate">
                                                        {{ $prod->category }}
                                                    </span>
                                                    <span
                                                        class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold"
                                                        :class="selectedProductId == {{ $prod->id }} ? 'bg-yellow-400 text-slate-950' : 'bg-slate-200 dark:bg-slate-800 text-transparent'"
                                                    >
                                                        ✓
                                                    </span>
                                                </div>

                                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white pt-1 leading-snug">
                                                    {{ $prod->name }}
                                                </h4>
                                            </div>

                                            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-[#343343]">
                                                @if($prod->formatted_original_price)
                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 line-through block">
                                                        {{ $prod->formatted_original_price }}
                                                    </span>
                                                @endif
                                                <span class="text-xs sm:text-sm font-black text-amber-600 dark:text-yellow-400">
                                                    {{ $prod->formatted_price }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- =========================================================================
                         LANGKAH 3: JUMLAH PEMBELIAN (Yellow Box '3')
                         ========================================================================= --}}
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] shadow-sm">
                        <div class="bg-slate-50 dark:bg-[#1b1a23] px-5 py-3.5 flex items-center gap-3 border-b border-slate-200 dark:border-[#343343]">
                            <span class="w-7 h-7 rounded-sm bg-yellow-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                3
                            </span>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Jumlah Pembelian</h2>
                        </div>

                        <div class="p-5 sm:p-6 flex items-center gap-4">
                            <div class="inline-flex items-center rounded-xl bg-slate-100 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] p-1">
                                <button
                                    type="button"
                                    @click="decreaseQty()"
                                    class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold flex items-center justify-center transition cursor-pointer shadow-xs border border-slate-200 dark:border-transparent"
                                    aria-label="Kurangi jumlah"
                                >
                                    -
                                </button>
                                <input
                                    type="number"
                                    name="quantity"
                                    x-model="quantity"
                                    min="1"
                                    max="100"
                                    class="w-14 text-center bg-transparent border-0 text-slate-900 dark:text-white font-bold text-sm focus:outline-none"
                                />
                                <button
                                    type="button"
                                    @click="increaseQty()"
                                    class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold flex items-center justify-center transition cursor-pointer shadow-xs border border-slate-200 dark:border-transparent"
                                    aria-label="Tambah jumlah"
                                >
                                    +
                                </button>
                            </div>

                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Total: <strong class="text-slate-900 dark:text-white" x-text="quantity"></strong> pcs
                            </span>
                        </div>
                    </div>

                    {{-- =========================================================================
                         LANGKAH 4: PILIH METODE PEMBAYARAN (Yellow Box '4')
                         ========================================================================= --}}
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] shadow-sm">
                        <div class="bg-slate-50 dark:bg-[#1b1a23] px-5 py-3.5 flex items-center justify-between border-b border-slate-200 dark:border-[#343343]">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-sm bg-yellow-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                    4
                                </span>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Pilih Metode Pembayaran</h2>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Verifikasi otomatis 24 jam</span>
                        </div>

                        {{-- Hidden input for payment method --}}
                        <input type="hidden" name="payment_method" :value="selectedMethod" />

                        <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($paymentMethods as $mKey => $mInfo)
                                <button
                                    type="button"
                                    @click="selectedMethod = '{{ $mKey }}'"
                                    class="p-3.5 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                                    :class="selectedMethod === '{{ $mKey }}' 
                                        ? 'bg-yellow-400/10 border-yellow-500 dark:border-yellow-400 ring-1 ring-yellow-400' 
                                        : 'bg-slate-50 dark:bg-[#1b1a23] border-slate-200 dark:border-[#343343] hover:border-slate-300 dark:hover:border-slate-600'"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] flex items-center justify-center text-sm shrink-0 shadow-xs">
                                            {{ $mInfo['icon'] }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $mInfo['name'] }}</h4>
                                                @if(!empty($mInfo['badge']))
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500/10 dark:bg-yellow-400/20 text-amber-700 dark:text-yellow-300">
                                                        {{ $mInfo['badge'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $mInfo['description'] }}</p>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-black text-amber-600 dark:text-yellow-400 block" x-text="formatRupiah(calculateMethodTotal('{{ $mKey }}'))"></span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block">Biaya Rp {{ number_format($mInfo['fee'], 0, ',', '.') }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- =========================================================================
                         LANGKAH 5: KONTAK PEMBELI & KODE PROMO (Yellow Box '5')
                         ========================================================================= --}}
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] shadow-sm">
                        <div class="bg-slate-50 dark:bg-[#1b1a23] px-5 py-3.5 flex items-center justify-between border-b border-slate-200 dark:border-[#343343]">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-sm bg-yellow-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                    5
                                </span>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Kontak &amp; Kode Promo</h2>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Bukti struk dikirim ke kontak ini</span>
                        </div>

                        <div class="p-5 sm:p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="customer_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Nama Lengkap <span class="text-rose-500 dark:text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="customer_name"
                                        name="customer_name"
                                        value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                                        placeholder="Nama Anda"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-500 dark:focus:border-yellow-400 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label for="customer_email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Email Aktif <span class="text-rose-500 dark:text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="customer_email"
                                        name="customer_email"
                                        value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                        placeholder="nama@email.com"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-500 dark:focus:border-yellow-400 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label for="customer_whatsapp" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Nomor WhatsApp <span class="text-rose-500 dark:text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="customer_whatsapp"
                                        name="customer_whatsapp"
                                        value="{{ old('customer_whatsapp', auth()->user()->phone ?? '08') }}"
                                        placeholder="08123456789"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-yellow-500 dark:focus:border-yellow-400 focus:outline-none font-mono"
                                    />
                                </div>
                            </div>

                            <div class="pt-2">
                                <label for="promo_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Kode Promo Voucher <span class="text-slate-400 dark:text-slate-500 font-normal">(Opsional)</span>
                                </label>
                                <input
                                    type="text"
                                    id="promo_code"
                                    name="promo_code"
                                    x-model="promoCode"
                                    value="{{ old('promo_code', '') }}"
                                    placeholder="Masukkan kode promo (jika ada)"
                                    class="w-full sm:w-72 px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343] text-xs text-amber-600 dark:text-yellow-400 font-mono font-bold uppercase focus:border-yellow-500 dark:focus:border-yellow-400 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Sticky Summary & Buy Button (Desktop) --}}
                <div class="lg:col-span-4 sticky top-24 space-y-4">
                    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] space-y-4 shadow-xl">
                        <div class="border-b border-slate-200 dark:border-[#343343] pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Ringkasan Pesanan</span>
                            </h3>
                        </div>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                <span>Game:</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $game->name }}</span>
                            </div>

                            <div class="flex items-start justify-between text-slate-500 dark:text-slate-400" x-show="accountId">
                                <span>Akun Game:</span>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white block" x-text="accountId + (accountServer ? ' (' + accountServer + ')' : '')"></span>
                                    <template x-if="accountNickname">
                                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block" x-text="'Nick: ' + accountNickname"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-start justify-between text-slate-500 dark:text-slate-400">
                                <span>Item:</span>
                                <span class="font-bold text-right text-amber-600 dark:text-yellow-400 max-w-[160px]" x-text="selectedProduct ? selectedProduct.name : '-'"></span>
                            </div>

                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                <span>Jumlah:</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="quantity + ' item'"></span>
                            </div>

                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                <span>Metode Bayar:</span>
                                <span class="font-bold text-slate-900 dark:text-white uppercase" x-text="selectedMethod"></span>
                            </div>

                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-200 dark:border-[#343343]">
                                <span>Subtotal:</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="formatRupiah(subtotal)"></span>
                            </div>

                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                <span>Biaya Layanan:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300" x-text="formatRupiah(paymentFee)"></span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#1b1a23] border border-slate-200 dark:border-[#343343]">
                            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block">Total Bayar</span>
                            <span class="text-xl font-black text-amber-600 dark:text-amber-400" x-text="formatRupiah(totalAmount)"></span>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3.5 px-4 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-slate-950 font-black text-sm tracking-wide transition cursor-pointer flex items-center justify-center gap-2 shadow-lg shadow-yellow-400/20"
                        >
                            <span>Beli Sekarang</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>

                        <div class="text-[10px] text-center text-slate-500 dark:text-slate-400 pt-1 space-y-1">
                            <p>🔒 Transaksi 100% Aman &amp; Bergaransi</p>
                            <p>Saldo otomatis masuk setelah verifikasi</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Sticky Bottom Checkout Bar --}}
            <div class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-[#1b1a23]/95 backdrop-blur-md border-t border-slate-200 dark:border-[#343343] p-3 sm:px-6 flex items-center justify-between gap-4 shadow-2xl">
                <div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase font-bold">Total Pembayaran</span>
                    <span class="text-base font-black text-amber-600 dark:text-yellow-400" x-text="formatRupiah(totalAmount)"></span>
                </div>

                <button
                    type="submit"
                    class="py-2.5 px-5 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-slate-950 font-black text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0 shadow-md"
                >
                    <span>Beli Sekarang</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </form>

        {{-- Modal Petunjuk ID Akun Game --}}
        <div
            x-show="showGuideModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 dark:bg-black/80 backdrop-blur-sm"
        >
            <div
                @click.away="showGuideModal = false"
                class="w-full max-w-md rounded-2xl bg-white dark:bg-[#23222d] border border-slate-200 dark:border-[#343343] p-6 space-y-4 text-left shadow-2xl"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#343343] pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Petunjuk Menemukan User ID</h3>
                    <button
                        type="button"
                        @click="showGuideModal = false"
                        class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-lg font-bold"
                    >
                        &times;
                    </button>
                </div>

                <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
                    <p>1. Buka game <strong>{{ $game->name }}</strong> di perangkat Anda.</p>
                    <p>2. Klik avatar atau menu profil Anda di pojok kiri atas layar utama.</p>
                    <p>3. Anda akan melihat deretan angka <strong>User ID</strong> (dan <strong>Zone ID</strong> di dalam kurung jika ada).</p>
                    <p>4. Masukkan angka tersebut ke formulir top up tanpa tanda kurung.</p>
                </div>

                <div class="pt-2 text-right">
                    <button
                        type="button"
                        @click="showGuideModal = false"
                        class="px-4 py-2 rounded-xl bg-yellow-400 text-slate-950 text-xs font-black shadow-md"
                    >
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
