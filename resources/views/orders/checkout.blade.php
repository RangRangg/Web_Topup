<x-layout title="Form Pemesanan Joki">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{
        quantity: 1,
        unitPrice: {{ $package->price }},
        get total() {
            return this.quantity * this.unitPrice;
        },
        formatRupiah(val) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        }
    }">
        <x-breadcrumb :items="[
            ['label' => 'Paket Joki', 'url' => route('joki')],
            ['label' => $package->name, 'url' => route('joki.show', $package->slug)],
            ['label' => 'Checkout']
        ]" />

        <div class="mt-4 mb-8">
            <x-badge variant="primary" size="sm" class="mb-2">FORMULIR CHECKOUT RESMI</x-badge>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Lengkapi Data Pesanan</h1>
            <p class="text-sm text-slate-400 mt-1">Pastikan data akun dan nomor WhatsApp yang dimasukkan sudah benar.</p>
        </div>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <input type="hidden" name="package_id" value="{{ $package->id }}" />

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                {{-- Form Fields --}}
                <div class="lg:col-span-8 space-y-8">
                    {{-- 1. Data Akun Game / Tugas --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                            <span class="w-7 h-7 rounded-lg bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">1</span>
                            <div>
                                <h3 class="text-base font-bold text-white">Data Akun / Kebutuhan</h3>
                                <p class="text-[11px] text-slate-400">Diperlukan agar booster dapat mulai memproses order Anda.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-input
                                label="User ID Game / Tag / Repo Link"
                                name="account_id"
                                placeholder="Contoh: 12847291 atau RiotTag#9999"
                                required="true"
                                hint="ID akun game kamu"
                            />

                            <x-input
                                label="Server / Zone ID / Region"
                                name="account_server"
                                placeholder="Contoh: 2194 atau APAC"
                                hint="Server akun (opsional jika tidak ada)"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-input
                                label="Nickname Akun"
                                name="account_nickname"
                                placeholder="Contoh: ShadowBlade"
                                hint="Untuk mempermudah verifikasi akun"
                            />

                            <x-select
                                label="Login Melalui"
                                name="login_via"
                                :options="[
                                    'Moonton' => 'Moonton ID',
                                    'Riot' => 'Riot Games',
                                    'Google' => 'Google Play / Email',
                                    'VK' => 'VK Account',
                                    'HoYoverse' => 'HoYoverse Account',
                                    'Mabar' => 'Tidak Perlu Login (Joki Gendong / Mabar)',
                                    'GitHub' => 'GitHub / ZIP File (Tugas RPL)',
                                ]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Catatan Khusus untuk Booster</label>
                            <textarea
                                name="account_notes"
                                rows="3"
                                placeholder="Contoh: Request hero Gusion/Hayabusa, tolong jangan main di jam 12-1 siang, dll..."
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 focus:border-cyan-500 focus:outline-none p-3.5 text-xs text-slate-100 placeholder-slate-500"
                            ></textarea>
                        </div>
                    </div>

                    {{-- 2. Data Pemesan --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                            <span class="w-7 h-7 rounded-lg bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">2</span>
                            <div>
                                <h3 class="text-base font-bold text-white">Informasi Kontak Pemesan</h3>
                                <p class="text-[11px] text-slate-400">Bukti pembayaran, invoice, dan laporan rank dikirim ke sini.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-input
                                label="Nama Lengkap"
                                name="customer_name"
                                :value="auth()->check() ? auth()->user()->name : ''"
                                placeholder="Nama Anda"
                                required="true"
                            />

                            <x-input
                                label="Alamat Email"
                                name="customer_email"
                                type="email"
                                :value="auth()->check() ? auth()->user()->email : ''"
                                placeholder="email@anda.com"
                                required="true"
                            />

                            <x-input
                                label="Nomor WhatsApp"
                                name="customer_whatsapp"
                                placeholder="08xxxxxxxxxx"
                                required="true"
                                hint="Wajib aktif untuk koordinasi booster"
                            />
                        </div>
                    </div>

                    {{-- 3. Metode Pembayaran --}}
                    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                            <span class="w-7 h-7 rounded-lg bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">3</span>
                            <div>
                                <h3 class="text-base font-bold text-white">Metode Pembayaran</h3>
                                <p class="text-[11px] text-slate-400">Pilih salah satu metode pembayaran yang kamu sukai.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            <label class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-cyan-500 flex flex-col justify-between cursor-pointer transition has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-500/10 has-[:checked]:ring-1 has-[:checked]:ring-cyan-400">
                                <input type="radio" name="payment_method" value="qris" checked class="sr-only" />
                                <div>
                                    <span class="text-xs font-black text-cyan-400 block">QRIS</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Semua E-Wallet</span>
                                </div>
                                <span class="text-[9px] font-bold text-emerald-400 mt-3">Proses Otomatis</span>
                            </label>

                            <label class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-cyan-500 flex flex-col justify-between cursor-pointer transition has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-500/10 has-[:checked]:ring-1 has-[:checked]:ring-cyan-400">
                                <input type="radio" name="payment_method" value="bca" class="sr-only" />
                                <div>
                                    <span class="text-xs font-black text-blue-400 block">Bank BCA</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Transfer Bank</span>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 mt-3">Manual Check</span>
                            </label>

                            <label class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-cyan-500 flex flex-col justify-between cursor-pointer transition has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-500/10 has-[:checked]:ring-1 has-[:checked]:ring-cyan-400">
                                <input type="radio" name="payment_method" value="mandiri" class="sr-only" />
                                <div>
                                    <span class="text-xs font-black text-amber-400 block">Mandiri</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Livin Mandiri</span>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 mt-3">Manual Check</span>
                            </label>

                            <label class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-cyan-500 flex flex-col justify-between cursor-pointer transition has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-500/10 has-[:checked]:ring-1 has-[:checked]:ring-cyan-400">
                                <input type="radio" name="payment_method" value="dana" class="sr-only" />
                                <div>
                                    <span class="text-xs font-black text-sky-400 block">DANA</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">E-Wallet</span>
                                </div>
                                <span class="text-[9px] font-bold text-sky-400 mt-3">Instan</span>
                            </label>

                            <label class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-cyan-500 flex flex-col justify-between cursor-pointer transition has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-500/10 has-[:checked]:ring-1 has-[:checked]:ring-cyan-400">
                                <input type="radio" name="payment_method" value="gopay" class="sr-only" />
                                <div>
                                    <span class="text-xs font-black text-emerald-400 block">GoPay</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">E-Wallet</span>
                                </div>
                                <span class="text-[9px] font-bold text-emerald-400 mt-3">Instan</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Order Review & Checkout Button --}}
                <div class="lg:col-span-4">
                    <div class="sticky top-28 p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6 shadow-2xl">
                        <div class="border-b border-slate-800 pb-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 block mb-1">Paket Dipilih</span>
                            <h3 class="text-lg font-bold text-white">{{ $package->name }}</h3>
                            <span class="text-xs text-slate-400">{{ $package->game->name ?? 'Layanan' }} &bull; {{ $package->unit }}</span>
                        </div>

                        {{-- Quantity selector if applicable --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-300">Jumlah / Kuantitas</span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="if(quantity > 1) quantity--"
                                    class="w-7 h-7 rounded-lg bg-slate-950 border border-slate-800 text-white font-bold flex items-center justify-center hover:border-slate-700"
                                >
                                    -
                                </button>
                                <input
                                    type="number"
                                    name="quantity"
                                    x-model="quantity"
                                    min="1"
                                    class="w-12 text-center text-xs font-bold text-white bg-slate-950 border border-slate-800 rounded-lg py-1 focus:outline-none"
                                />
                                <button
                                    type="button"
                                    @click="quantity++"
                                    class="w-7 h-7 rounded-lg bg-slate-950 border border-slate-800 text-white font-bold flex items-center justify-center hover:border-slate-700"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Cost Breakdown --}}
                        <div class="space-y-2.5 pt-4 border-t border-slate-800 text-xs text-slate-300">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Harga Satuan</span>
                                <span>{{ $package->formatted_price }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Biaya Layanan</span>
                                <span class="text-emerald-400 font-bold">GRATIS (Rp 0)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Estimasi Pengerjaan</span>
                                <span class="text-cyan-400 font-bold">{{ $package->estimated_duration }}</span>
                            </div>
                        </div>

                        {{-- Total Amount --}}
                        <div class="p-4 rounded-2xl bg-slate-950 border border-cyan-500/30 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Tagihan</span>
                            <span class="text-2xl font-black text-white" x-text="formatRupiah(total)"></span>
                        </div>

                        <div class="pt-2">
                            <x-button type="submit" variant="primary" size="lg" class="w-full">
                                Buat Pesanan Sekarang &raquo;
                            </x-button>
                        </div>

                        <p class="text-[11px] text-center text-slate-400">
                            Dengan mengklik tombol, Anda menyetujui Ketentuan Layanan & Garansi Aethereal Joki.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>
