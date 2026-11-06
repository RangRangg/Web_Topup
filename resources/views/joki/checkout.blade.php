<x-layout title="Checkout Jasa Joki Resmi - Aethereal Joki">
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
        x-data="{
            services: {{ Js::from($allServices) }},
            paymentMethods: {{ Js::from($paymentMethods) }},
            selectedServiceId: {{ (int) old('service_id', $service->id) }},
            selectedMethod: '{{ old('payment_method', 'qris') }}',
            promoCode: '{{ old('promo_code', '') }}',
            get currentService() {
                return this.services.find(s => s.id == this.selectedServiceId) || this.services[0];
            },
            get unitPrice() {
                return this.currentService ? Number(this.currentService.price) : 0;
            },
            get paymentFee() {
                const method = this.paymentMethods[this.selectedMethod];
                if (!method) return 0;
                if (method.fee_type === 'flat') return Number(method.fee);
                if (method.fee_type === 'percentage') return Math.round((this.unitPrice * Number(method.fee)) / 100);
                return 0;
            },
            get totalAmount() {
                return Math.max(0, this.unitPrice + this.paymentFee);
            },
            formatRupiah(val) {
                return 'Rp ' + Number(val).toLocaleString('id-ID');
            }
        }"
    >
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Jasa Joki', 'url' => route('joki')],
            ['label' => $service->title, 'url' => route('joki.show', $service->slug)],
            ['label' => 'Checkout Layanan']
        ]" />

        {{-- Page Header --}}
        <div class="mt-4 mb-8">
            <x-badge variant="purple" size="sm" class="mb-2">FORMULIR CHECKOUT RESMI</x-badge>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Checkout Jasa Joki & Push Rank</h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">Konfirmasi layanan, masukkan detail akun dengan aman, dan selesaikan pembayaran untuk alokasi booster.</p>
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

        <form action="{{ route('joki.checkout.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Left Column: 4 Order Steps --}}
                <div class="lg:col-span-8 space-y-8">
                    {{-- Step 1: Layanan Joki Terpilih --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                            <span class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-600 dark:text-purple-400 font-bold flex items-center justify-center text-sm border border-purple-300 dark:border-purple-500/30">1</span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pilih Paket Jasa Joki</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pilih paket layanan boosting dan periksa target rank serta estimasi durasi.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Pilih / Ganti Layanan Joki:</label>
                            <select
                                name="service_id"
                                x-model="selectedServiceId"
                                class="w-full rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-3 text-xs font-bold focus:border-cyan-500 focus:outline-none"
                            >
                                @foreach($allServices as $s)
                                    <option value="{{ $s->id }}" {{ $s->id === $service->id ? 'selected' : '' }}>
                                        [{{ $s->game->name ?? 'Game' }}] {{ $s->title }} - {{ $s->formatted_starting_price }} (Target: {{ $s->target }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Active Service Highlight Banner --}}
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gradient-to-r dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/60 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400" x-text="currentService?.game?.name || 'Game'"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-600 dark:text-amber-300 font-bold">★ 4.98</span>
                                </div>
                                <h4 class="text-base font-black text-slate-900 dark:text-white" x-text="currentService?.name"></h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400" x-text="currentService?.description"></p>
                            </div>

                            <div class="text-right sm:border-l sm:border-slate-200 dark:sm:border-slate-800 sm:pl-4 shrink-0">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase tracking-wider">Target Rank</span>
                                <span class="text-xs font-black text-cyan-600 dark:text-cyan-300 font-mono" x-text="currentService?.rank_to ? (currentService.rank_from + ' → ' + currentService.rank_to) : (currentService?.rank_from || 'Selesai')"></span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-1" x-text="'Durasi: ' + currentService?.estimated_duration"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Data Akun & Login Game --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                            <span class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-bold flex items-center justify-center text-sm border border-cyan-300 dark:border-cyan-500/30">2</span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Data Akun Game</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Data akun diperlukan agar booster dapat login dan mulai push rank.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">User ID Game / Tag / Riot ID <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <input
                                    type="text"
                                    name="account_id"
                                    value="{{ old('account_id') }}"
                                    required
                                    placeholder="Contoh: 12847291 atau RiotTag#9999"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Server / Zone ID / Region</label>
                                <input
                                    type="text"
                                    name="account_server"
                                    value="{{ old('account_server') }}"
                                    placeholder="Contoh: 2194 atau Asia / APAC"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Nickname Akun</label>
                                <input
                                    type="text"
                                    name="account_nickname"
                                    value="{{ old('account_nickname') }}"
                                    placeholder="Contoh: ShadowBlade"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Metode Login Akun <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <select
                                    name="login_via"
                                    required
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white p-3 text-xs focus:border-cyan-500 focus:outline-none"
                                >
                                    <option value="Moonton ID" {{ old('login_via') === 'Moonton ID' ? 'selected' : '' }}>Moonton ID (Mobile Legends)</option>
                                    <option value="Riot Games" {{ old('login_via') === 'Riot Games' ? 'selected' : '' }}>Riot Games Account (Valorant)</option>
                                    <option value="Google Play" {{ old('login_via') === 'Google Play' ? 'selected' : '' }}>Google Play / Gmail</option>
                                    <option value="Facebook" {{ old('login_via') === 'Facebook' ? 'selected' : '' }}>Facebook Account</option>
                                    <option value="HoYoverse" {{ old('login_via') === 'HoYoverse' ? 'selected' : '' }}>HoYoverse (Genshin Impact)</option>
                                    <option value="Mabar / Party" {{ old('login_via') === 'Mabar / Party' ? 'selected' : '' }}>Mabar / Party (Tanpa Login Akun)</option>
                                    <option value="Lainnya" {{ old('login_via') === 'Lainnya' ? 'selected' : '' }}>Metode Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Catatan Khusus untuk Booster</label>
                            <textarea
                                name="account_notes"
                                rows="3"
                                placeholder="Contoh: Request hero Gusion/Hayabusa, tolong mode offline saat bermain, jangan main jam 12-1 siang, dll..."
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                            >{{ old('account_notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Step 3: Metode Pembayaran --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center text-sm border border-emerald-300 dark:border-emerald-500/30">3</span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pilih Metode Pembayaran</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Verifikasi instan otomatis 24 jam via QRIS, Virtual Account, & E-Wallet.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($paymentMethods as $key => $method)
                                <label
                                    class="relative p-4 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between"
                                    :class="selectedMethod === '{{ $key }}' ? 'bg-cyan-500/10 border-cyan-500 shadow-sm ring-1 ring-cyan-500' : 'bg-slate-50 dark:bg-slate-950/80 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                                >
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="{{ $key }}"
                                        x-model="selectedMethod"
                                        class="sr-only"
                                    />
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <span>{{ $method['icon'] ?? '💳' }}</span>
                                                <span>{{ $method['name'] }}</span>
                                            </span>
                                            @if(!empty($method['badge']))
                                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 uppercase">
                                                    {{ $method['badge'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $method['description'] }}</p>
                                    </div>

                                    <div class="mt-3 pt-2 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500 dark:text-slate-400">Biaya Admin:</span>
                                        <span class="font-bold text-cyan-600 dark:text-cyan-400">
                                            {{ $method['fee_type'] === 'flat' ? 'Rp ' . number_format($method['fee'], 0, ',', '.') : $method['fee'] . '%' }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Step 4: Informasi Kontak Pemesan --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                            <span class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-600 dark:text-blue-400 font-bold flex items-center justify-center text-sm border border-blue-300 dark:border-blue-500/30">4</span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Informasi Kontak Pemesan</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Invoice dan konfirmasi penugasan booster akan dikirimkan ke nomor dan email ini.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    value="{{ old('customer_name', auth()->user()?->name ?? '') }}"
                                    required
                                    placeholder="Nama Anda"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Alamat Email <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <input
                                    type="email"
                                    name="customer_email"
                                    value="{{ old('customer_email', auth()->user()?->email ?? '') }}"
                                    required
                                    placeholder="nama@email.com"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Nomor WhatsApp <span class="text-rose-500 dark:text-rose-400">*</span></label>
                                <input
                                    type="tel"
                                    name="customer_whatsapp"
                                    value="{{ old('customer_whatsapp', auth()->user()?->phone ?? '') }}"
                                    required
                                    placeholder="081234567890"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-mono"
                                />
                            </div>
                        </div>

                        {{-- Promo Voucher Code --}}
                        <div class="pt-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Punya Kode Promo / Voucher?</label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    name="promo_code"
                                    x-model="promoCode"
                                    placeholder="Contoh: AETHER2026 atau WELCOME30"
                                    class="w-full sm:w-80 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3 text-xs text-cyan-600 dark:text-cyan-400 font-mono placeholder-slate-400 dark:placeholder-slate-500 uppercase font-bold"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Sticky Order Summary --}}
                <div class="lg:col-span-4 sticky top-28 space-y-6">
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl dark:shadow-2xl space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Ringkasan Pesanan</h3>
                            <x-badge variant="purple" size="sm">JASA JOKI</x-badge>
                        </div>

                        {{-- Selected Service Card Preview --}}
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-cyan-600 dark:text-cyan-400" x-text="currentService?.game?.name || 'Game'"></span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400" x-text="currentService?.estimated_duration"></span>
                            </div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white" x-text="currentService?.name"></h4>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200 dark:border-slate-800/80">
                                <span class="text-slate-500 dark:text-slate-400">Target Rank:</span>
                                <span class="font-bold text-cyan-700 dark:text-cyan-300 font-mono" x-text="currentService?.rank_to ? (currentService.rank_from + ' → ' + currentService.rank_to) : (currentService?.rank_from || 'Target Paket')"></span>
                            </div>
                        </div>

                        {{-- Calculation Breakdown --}}
                        <div class="space-y-2.5 text-xs text-slate-700 dark:text-slate-300">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Biaya Jasa Joki:</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="formatRupiah(unitPrice)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Biaya Payment Gateway:</span>
                                <span class="font-bold text-cyan-600 dark:text-cyan-400" x-text="formatRupiah(paymentFee)"></span>
                            </div>

                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Total Pembayaran</span>
                                <span class="text-2xl font-black text-slate-900 dark:text-white" x-text="formatRupiah(totalAmount)"></span>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2 space-y-3">
                            <x-button type="submit" variant="primary" size="lg" class="w-full justify-center text-sm shadow-xl shadow-cyan-500/20">
                                Lanjut ke Pembayaran &raquo;
                            </x-button>

                            <p class="text-[10px] text-slate-500 text-center leading-relaxed">
                                Dengan menekan tombol di atas, pesanan akan diterbitkan dengan status <strong class="text-amber-600 dark:text-amber-400">PENDING</strong> dan Anda diarahkan ke gateway pembayaran resmi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>
