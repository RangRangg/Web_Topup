<x-layout :title="'Pesanan #' . $order->order_code">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Pesanan', 'url' => route('tracking')],
            ['label' => '#' . $order->order_code]
        ]" />

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

        {{-- WhatsApp Auto-Redirect Banner (Fired after checkout) --}}
        @if(session('whatsapp_redirect_url'))
            <div
                x-data="{
                    redirectUrl: '{{ session('whatsapp_redirect_url') }}',
                    countdown: 3,
                    opened: false,
                    init() {
                        let timer = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(timer);
                                if (!this.opened) {
                                    this.opened = true;
                                    window.open(this.redirectUrl, '_blank');
                                }
                            }
                        }, 1000);
                    }
                }"
                class="mt-4 p-5 rounded-2xl bg-gradient-to-r from-emerald-950/70 to-slate-900 border border-emerald-500/40 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Mengarahkan ke WhatsApp Otomatis...</h3>
                        <p class="text-xs text-slate-300">
                            Pesanan berhasil dibuat. Membuka WhatsApp Admin dalam <span class="font-bold text-emerald-400 font-mono" x-text="countdown">3</span> detik.
                        </p>
                    </div>
                </div>
                <a
                    :href="redirectUrl"
                    target="_blank"
                    @click="opened = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shrink-0"
                >
                    <span>Buka WhatsApp Sekarang</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        @endif

        {{-- Order Status Banner --}}
        <div class="mt-6 mb-8 p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl dark:shadow-2xl relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">Nomor Pesanan</span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono flex items-center gap-2">
                        #{{ $order->order_code }}
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $order->order_code }}'); alert('Kode order #{{ $order->order_code }} disalin!')"
                            class="p-1 rounded text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition"
                            title="Salin kode pesanan"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </h1>
                    <span class="text-xs text-slate-500 dark:text-slate-400 mt-1 block">Dibuat pada {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                </div>

                <div class="flex items-center gap-3">
                    <x-badge :variant="$order->status_badge_variant" size="lg" :dot="true">
                        {{ $order->status_label }}
                    </x-badge>
                </div>
            </div>

            {{-- Required Progress Tracker: Payment → Waiting Assignment → Processing → Completed --}}
            @php
                $isPaidOrBeyond = in_array($order->order_status, ['waiting_assignment', 'paid', 'processing', 'completed']) || ($order->payment && $order->payment->status === 'paid');
                $isProcessingOrBeyond = in_array($order->order_status, ['processing', 'completed']);
                $isCompleted = $order->order_status === 'completed';
                $isJoki = $order->isJoki();
                $isWaitingAssignmentOrBeyond = in_array($order->order_status, ['waiting_assignment', 'processing', 'completed']);
            @endphp
            <div class="pt-6">
                @if($isJoki)
                    {{-- Joki Service Progress Tracker (4 Steps) --}}
                    <div class="grid grid-cols-4 gap-2 text-center text-xs relative">
                        <div class="absolute top-4 left-1/8 right-1/8 h-0.5 bg-slate-200 dark:bg-slate-800 -z-0 hidden sm:block"></div>

                        {{-- Step 1: Payment --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isPaidOrBeyond ? 'bg-sky-500 text-white dark:text-slate-950' : 'bg-amber-500 text-slate-950' }}">
                                {{ $isPaidOrBeyond ? '✓' : '1' }}
                            </div>
                            <span class="font-bold {{ $isPaidOrBeyond ? 'text-slate-900 dark:text-white' : 'text-amber-600 dark:text-amber-400' }} block">Payment</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $isPaidOrBeyond ? 'Terverifikasi' : 'Menunggu Bayar' }}</span>
                        </div>

                        {{-- Step 2: Waiting Assignment --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isWaitingAssignmentOrBeyond ? 'bg-cyan-500 text-white dark:text-slate-950 ring-4 ring-cyan-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                {{ $isProcessingOrBeyond ? '✓' : '2' }}
                            </div>
                            <span class="font-bold {{ $isWaitingAssignmentOrBeyond ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }} block">Waiting Assignment</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">
                                @if($order->jokiProfile)
                                    Booster Ditugaskan
                                @elseif($order->order_status === 'waiting_assignment')
                                    Antrean Booster
                                @else
                                    Menunggu Alokasi
                                @endif
                            </span>
                        </div>

                        {{-- Step 3: Processing --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isProcessingOrBeyond ? 'bg-cyan-500 text-white dark:text-slate-950 ring-4 ring-cyan-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                {{ $isCompleted ? '✓' : '3' }}
                            </div>
                            <span class="font-bold {{ $isProcessingOrBeyond ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }} block">Processing</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $order->order_status === 'processing' ? 'Pengerjaan Akun' : ($isCompleted ? 'Selesai Dikerjakan' : 'Belum Dimulai') }}</span>
                        </div>

                        {{-- Step 4: Completed --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isCompleted ? 'bg-emerald-500 text-white dark:text-slate-950 ring-4 ring-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                {{ $isCompleted ? '✓' : '4' }}
                            </div>
                            <span class="font-bold {{ $isCompleted ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }} block">Completed</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $isCompleted ? 'Garansi Sukses' : 'Belum Selesai' }}</span>
                        </div>
                    </div>
                @else
                    {{-- Top Up Diamond / Game Item Progress Tracker (3 Steps: Pembayaran → Pengiriman Otomatis → Selesai) --}}
                    <div class="grid grid-cols-3 gap-2 text-center text-xs relative max-w-xl mx-auto">
                        <div class="absolute top-4 left-1/6 right-1/6 h-0.5 bg-slate-200 dark:bg-slate-800 -z-0 hidden sm:block"></div>

                        {{-- Step 1: Pembayaran --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isPaidOrBeyond ? 'bg-emerald-500 text-white dark:text-slate-950 ring-4 ring-emerald-500/20' : 'bg-amber-500 text-slate-950' }}">
                                {{ $isPaidOrBeyond ? '✓' : '1' }}
                            </div>
                            <span class="font-bold {{ $isPaidOrBeyond ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} block">Pembayaran</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $isPaidOrBeyond ? 'Terverifikasi' : 'Menunggu Bayar' }}</span>
                        </div>

                        {{-- Step 2: Pengisian Diamond (Auto Process) --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isCompleted ? 'bg-emerald-500 text-white dark:text-slate-950 ring-4 ring-emerald-500/20' : ($isPaidOrBeyond ? 'bg-cyan-500 text-white dark:text-slate-950 ring-4 ring-cyan-500/20 animate-pulse' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500') }}">
                                {{ $isCompleted ? '✓' : '2' }}
                            </div>
                            <span class="font-bold {{ $isCompleted ? 'text-emerald-600 dark:text-emerald-400' : ($isPaidOrBeyond ? 'text-cyan-600 dark:text-cyan-400' : 'text-slate-400 dark:text-slate-500') }} block">Pengisian Diamond</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $isCompleted ? 'Otomatis Terkirim' : ($isPaidOrBeyond ? 'Sistem Memproses' : 'Antrean Sistem') }}</span>
                        </div>

                        {{-- Step 3: Selesai --}}
                        <div class="space-y-2 relative z-10">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold text-xs {{ $isCompleted ? 'bg-emerald-500 text-white dark:text-slate-950 ring-4 ring-emerald-500/20 shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                {{ $isCompleted ? '✓' : '3' }}
                            </div>
                            <span class="font-bold {{ $isCompleted ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }} block">Selesai</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $isCompleted ? 'Berhasil Masuk Akun' : 'Menunggu Selesai' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Left: Payment Instructions / Booster Tracking / Review Section --}}
            <div class="lg:col-span-7 space-y-6">
                @php
                    $payment = $order->payment;
                    $isPending = ! $payment || $payment->status === 'pending';
                    $isPaid = $payment && $payment->status === 'paid';
                    $payload = $payment->payload ?? [];
                @endphp

                {{-- Action Box 1: Booster Claim (For Joki Booster when Waiting Assignment) --}}
                @if($isJoki && auth()->check() && auth()->user()->isJoki() && is_null($order->joki_profile_id) && in_array($order->order_status, ['waiting_assignment', 'paid', 'pending']))
                    <div class="p-6 rounded-2xl bg-purple-50 dark:bg-purple-950/20 border border-purple-300 dark:border-purple-500/40 space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Order Ini Tersedia di Pool Booster!</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400">Anda dapat mengambil pesanan ini dan mulai proses pengerjaan sekarang.</p>
                            </div>
                        </div>

                        <form action="{{ route('orders.claim', $order->order_code) }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="primary" size="md" class="w-full">
                                Ambil Pesanan Ini (Claim Order)
                            </x-button>
                        </form>
                    </div>
                @endif

                {{-- Action Box 2: Admin Booster Assignment (For Admin on Joki orders) --}}
                @if($isJoki && auth()->check() && auth()->user()->isAdmin() && is_null($order->joki_profile_id))
                    @php
                        $boostersList = \App\Models\JokiProfile::with('user')->get();
                    @endphp
                    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-cyan-300 dark:border-cyan-500/40 shadow-sm space-y-4">
                        <div class="flex items-center gap-2">
                            <x-badge variant="primary" size="sm">ADMIN ASSIGNMENT</x-badge>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tugaskan Pro Booster untuk Order Ini</h3>
                        </div>

                        <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pilih Booster Tersedia:</label>
                                <select name="joki_profile_id" required class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white p-2.5 text-xs focus:border-cyan-500 focus:outline-none">
                                    <option value="">-- Pilih Booster Resmi --</option>
                                    @foreach($boostersList as $bst)
                                        <option value="{{ $bst->id }}">
                                            {{ $bst->nickname }} (Rating: {{ $bst->rating }} ★ | {{ $bst->completed_orders_count }} Pesanan Selesai)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <x-button type="submit" variant="primary" size="sm" class="w-full">
                                Konfirmasi Penugasan Booster
                            </x-button>
                        </form>
                    </div>
                @endif

                {{-- Action Box 3: Assigned Booster Card & Progress Update (For Processing Joki Orders) --}}
                @if($isJoki && $order->jokiProfile)
                    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-purple-300 dark:border-purple-500/30 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm border border-purple-200 dark:border-purple-500/40">
                                    ⚔️
                                </div>
                                <div>
                                    <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold uppercase tracking-wider block">Pro Booster Ditugaskan</span>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $order->jokiProfile->nickname }}</h4>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-xs font-black text-amber-500 dark:text-amber-400">★ {{ number_format($order->jokiProfile->rating, 1) }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $order->jokiProfile->completed_orders_count }} Selesai</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ $order->jokiProfile->bio }}
                        </p>

                        {{-- If Assigned Booster views this order and it's processing --}}
                        @if(auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->jokiProfile && auth()->user()->jokiProfile->id === $order->joki_profile_id)) && $order->order_status === 'processing')
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                                <form action="{{ route('orders.progress', $order->order_code) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed" />
                                    <x-button type="submit" variant="primary" size="md" class="w-full">
                                        Tandai Pengerjaan Selesai (Completed)
                                    </x-button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Top Up Instant Auto-Delivery Status Banner --}}
                @if(!$isJoki && $isPaid)
                    <div class="p-6 rounded-3xl bg-emerald-50/60 dark:bg-gradient-to-r dark:from-emerald-950/40 dark:to-teal-950/30 border border-emerald-300 dark:border-emerald-500/40 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-xl border border-emerald-300 dark:border-emerald-500/40 shrink-0">
                                ⚡
                            </div>
                            <div>
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider block">Sistem Otomatis 24 Jam</span>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $isCompleted ? 'Top Up Berhasil Dikirim ke Akun!' : 'Sedang Mengirimkan Diamond ke Akun...' }}
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    {{ $isCompleted ? 'Diamond telah otomatis terinjeksi ke ID Akun game Anda tanpa perlu menunggu verifikasi manual admin.' : 'Pembayaran terverifikasi, sistem sedang memproses pengisian diamond secara instan.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Payment Section (Instructions or Verified) --}}
                @if($isPending)
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#131c2e] border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                Instruksi Pembayaran Resmi
                            </h3>
                            <x-badge variant="warning" size="sm">MENUNGGU PEMBAYARAN</x-badge>
                        </div>

                        {{-- Payment instructions by channel --}}
                        @if(($payment->payment_method ?? 'qris') === 'qris')
                            <div class="text-center space-y-4">
                                <p class="text-xs text-slate-600 dark:text-slate-300">Scan QRIS menggunakan GoPay, OVO, DANA, BCA Mobile, Livin, atau Mobile Banking pilihan Anda:</p>
                                
                                <div class="inline-block p-4 rounded-3xl bg-white shadow-2xl mx-auto border border-slate-200 dark:border-transparent">
                                    <img
                                        src="{{ !empty($payload['qr_url']) ? $payload['qr_url'] : ('https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($payload['qr_string'] ?? 'https://aethereal.test/order/' . $order->order_code)) }}"
                                        alt="QRIS Pembayaran"
                                        class="w-48 h-48 mx-auto"
                                    />
                                    <div class="text-[10px] font-mono text-slate-800 font-bold mt-2">NMID: {{ $payload['nmid'] ?? 'ID1020039201948' }}</div>
                                </div>

                                @if(!empty($payload['qr_string']))
                                    <div class="flex justify-center">
                                        <button
                                            type="button"
                                            onclick="navigator.clipboard.writeText('{{ $payload['qr_string'] }}'); alert('String QRIS berhasil disalin!')"
                                            class="text-[11px] text-cyan-600 dark:text-cyan-400 hover:underline font-mono cursor-pointer"
                                        >
                                            Salin String QRIS
                                        </button>
                                    </div>
                                @endif

                                @if(config('services.payment.driver') === 'midtrans' && !config('services.midtrans.is_production'))
                                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-[11px] text-amber-800 dark:text-amber-300 text-left space-y-1">
                                        <span class="font-bold flex items-center gap-1.5">
                                            <span>🧪</span> Mode Midtrans Sandbox (Testing)
                                        </span>
                                        <p class="text-slate-600 dark:text-slate-300 text-[10px]">
                                            Untuk menguji pembayaran tanpa uang sungguhan, salin string QRIS di atas lalu bayar di
                                            <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="text-cyan-600 dark:text-cyan-400 underline font-semibold">Midtrans QRIS Simulator &nearr;</a>
                                        </p>
                                    </div>
                                @endif

                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400">
                                    Batas Pembayaran: <strong class="text-cyan-600 dark:text-cyan-400">2 Jam</strong> sejak order diterbitkan.
                                </div>
                            </div>
                        @elseif(in_array($payment->payment_method ?? '', ['bca', 'mandiri', 'briva', 'bni']))
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Nomor Virtual Account Resmi ({{ strtoupper($payment->payment_method) }})</span>
                                <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xs">
                                    <span class="text-lg font-mono font-black text-cyan-600 dark:text-cyan-400">{{ $payload['va_number'] ?? '8801293847291038' }}</span>
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $payload['va_number'] ?? '8801293847291038' }}'); alert('Nomor VA berhasil disalin!')"
                                        class="px-3 py-1.5 rounded-lg bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 text-xs font-bold hover:bg-cyan-500/30 transition cursor-pointer"
                                    >
                                        Salin VA
                                    </button>
                                </div>
                            </div>
                        @elseif(in_array($payment->payment_method ?? '', ['alfamart', 'indomaret']))
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Kode Pembayaran Kasir ({{ strtoupper($payment->payment_method) }})</span>
                                <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xs">
                                    <span class="text-lg font-mono font-black text-amber-600 dark:text-amber-400">{{ $payload['va_number'] ?? 'PAY-GERAI-8829' }}</span>
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $payload['va_number'] ?? 'PAY-GERAI-8829' }}'); alert('Kode pembayaran berhasil disalin!')"
                                        class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-bold hover:bg-amber-500/30 transition cursor-pointer"
                                    >
                                        Salin Kode
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Tunjukkan kode ini kepada kasir {{ ucfirst($payment->payment_method) }} terdekat untuk melakukan pembayaran.</p>
                            </div>
                        @else
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-2">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $payment->payment_method ?? 'E-Wallet' }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Buka aplikasi dompet digital Anda dan selesaikan pembayaran tagihan Aethereal Joki.</p>
                            </div>
                        @endif

                        {{-- Auto Payment Verification Button --}}
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <form action="{{ route('orders.simulate-pay', $order->order_code) }}" method="POST">
                                @csrf
                                <x-button type="submit" variant="primary" size="md" class="w-full justify-center text-xs">
                                    ⚡ Cek &amp; Verifikasi Pembayaran Otomatis
                                </x-button>
                            </form>
                        </div>

                        {{-- Manual Proof Upload Form --}}
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Unggah Bukti Transfer Manual (Opsional)</h4>
                            <form action="{{ route('orders.upload-proof', $order->order_code) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <input
                                    type="file"
                                    name="payment_proof"
                                    accept="image/*"
                                    required
                                    class="w-full text-xs text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500/10 file:text-cyan-600 dark:file:text-cyan-400 hover:file:bg-cyan-500/20 file:cursor-pointer border border-slate-200 dark:border-slate-800 rounded-xl p-2 bg-slate-50 dark:bg-slate-950"
                                />
                                <x-button type="submit" variant="outline" size="sm" class="w-full">
                                    Kirim Bukti Transfer
                                </x-button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Paid Confirmation Box --}}
                    <div class="p-6 rounded-3xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-300 dark:border-emerald-500/40 space-y-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-xl border border-emerald-300 dark:border-emerald-500/30 shrink-0">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembayaran Telah Diverifikasi &amp; Lunas</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    Dibayar pada: <strong class="text-emerald-600 dark:text-emerald-300">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-emerald-200 dark:border-emerald-500/20 text-xs text-slate-700 dark:text-slate-300 space-y-1 shadow-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Status Gateway:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 uppercase">{{ $payment->status ?? 'PAID' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Kode Transaksi:</span>
                                <span class="font-mono text-slate-900 dark:text-white">{{ $payment->payment_code ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Metode:</span>
                                <span class="uppercase text-slate-900 dark:text-white font-semibold">{{ $payment->payment_method ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Review Section: Only after order COMPLETED --}}
                @if($isCompleted)
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-500/30 shadow-xl space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/20 text-amber-500 dark:text-amber-400 flex items-center justify-center font-bold text-base border border-amber-200 dark:border-amber-500/30">
                                ★
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Ulasan & Rating Pesanan</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $isJoki ? 'Pesanan telah selesai! Berikan penilaian untuk performa booster kami.' : 'Pesanan telah selesai! Berikan ulasan untuk kecepatan layanan top up kami.' }}</p>
                            </div>
                        </div>

                        {{-- If already reviewed --}}
                        @if($order->reviews->isNotEmpty())
                            @foreach($order->reviews as $rev)
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $rev->customer_name }}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold">Ulasan Terverifikasi</span>
                                        </div>
                                        <span class="text-amber-500 dark:text-amber-400 text-xs font-bold tracking-widest">{{ str_repeat('★', $rev->rating) }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $rev->comment }}</p>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block">{{ $rev->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>
                            @endforeach
                        {{-- If not yet reviewed and user is customer --}}
                        @elseif(auth()->check() && auth()->id() === $order->user_id)
                            <form action="{{ route('orders.review', $order->order_code) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Pilih Rating Bintang:</label>
                                    <div class="flex items-center gap-3">
                                        @for($i = 5; $i >= 1; $i--)
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" name="rating" value="{{ $i }}" {{ $i === 5 ? 'checked' : '' }} class="text-amber-500 focus:ring-0" />
                                                <span class="text-xs font-bold text-amber-500 dark:text-amber-400">{{ $i }} ★</span>
                                            </label>
                                        @endfor
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Tuliskan Ulasan / Testimoni:</label>
                                    <textarea
                                        name="comment"
                                        rows="3"
                                        required
                                        minlength="5"
                                        placeholder="{{ $isJoki ? 'Ceritakan pengalaman Anda, kecepatan booster, winrate, dan kepuasan pelayanan...' : 'Ceritakan kepuasan Anda, kecepatan diamond masuk, dan kemudahan transaksi...' }}"
                                        class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:outline-none p-3.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                    ></textarea>
                                </div>

                                <x-button type="submit" variant="primary" size="md" class="w-full">
                                    Kirim Ulasan &amp; Selesaikan
                                </x-button>
                            </form>
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">{{ $isJoki ? 'Pesanan ini telah sukses diselesaikan oleh booster Aethereal Joki.' : 'Pesanan top up ini telah sukses diproses dan dikirim otomatis.' }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right: Order Summary Breakdown --}}
            <div class="lg:col-span-5">
                <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-6 shadow-xl dark:shadow-2xl">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-3">Rincian Order</h3>

                    {{-- Items list --}}
                    <div class="space-y-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                        @forelse($order->orderItems as $item)
                            <div class="flex items-start justify-between text-xs">
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">{{ $item->item_name }}</h4>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                </div>
                                <span class="font-bold text-cyan-600 dark:text-cyan-400">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 dark:text-slate-400">Detail item tidak ditemukan.</p>
                        @endforelse
                    </div>

                    {{-- Account Target Details --}}
                    <div class="space-y-2.5 text-xs text-slate-700 dark:text-slate-300">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">User ID Akun:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $order->account_id ?? '-' }}</span>
                        </div>
                        @if($order->account_server)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Server / Zone:</span>
                                <span class="font-mono text-slate-900 dark:text-white">{{ $order->account_server }}</span>
                            </div>
                        @endif
                        @if($order->account_nickname)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Nickname:</span>
                                <span class="text-slate-900 dark:text-white">{{ $order->account_nickname }}</span>
                            </div>
                        @endif
                        @if($order->login_via && $isJoki)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Login Melalui:</span>
                                <span class="text-cyan-600 dark:text-cyan-400 font-semibold">{{ $order->login_via }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Pemesan:</span>
                            <span class="text-slate-900 dark:text-white">{{ $order->customer_name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Email:</span>
                            <span class="text-slate-900 dark:text-white">{{ $order->customer_email }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">WhatsApp:</span>
                            <span class="font-mono text-slate-900 dark:text-white">{{ $order->customer_whatsapp }}</span>
                        </div>
                        @if($order->account_notes)
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-slate-500 dark:text-slate-400 block text-[11px] mb-1">Catatan Khusus:</span>
                                <p class="text-[11px] text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/80">{{ $order->account_notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Price Breakdown --}}
                    <div class="space-y-2 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs">
                        <div class="flex justify-between text-slate-500 dark:text-slate-400">
                            <span>Subtotal:</span>
                            <span class="text-slate-900 dark:text-white font-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>

                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span>Potongan Diskon:</span>
                                <span class="font-semibold">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @php
                            $fee = $order->total_amount - ($order->subtotal - $order->discount_amount);
                        @endphp
                        @if($fee > 0)
                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                <span>Biaya Layanan Payment:</span>
                                <span class="text-cyan-600 dark:text-cyan-400 font-semibold">+ Rp {{ number_format($fee, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Tagihan</span>
                            <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    <div class="pt-2 flex gap-2">
                        <x-button as="a" :href="route('tracking', ['code' => $order->order_code])" variant="secondary" size="sm" class="w-full">
                            Refresh Status
                        </x-button>
                    </div>
                </div>

                {{-- WhatsApp Notification Center Card --}}
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-500/30 shadow-xl space-y-4 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Layanan WhatsApp</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-mono">
                            Auto Redirect
                        </span>
                    </div>

                    <p class="text-slate-600 dark:text-slate-400 text-xs leading-relaxed">
                        Klik tombol di bawah untuk mengirim pesan otomatis berisi kode pesanan, data akun, dan status langsung ke nomor WhatsApp terkait:
                    </p>

                    <div class="space-y-2.5">
                        {{-- 1. WA Admin (DM & Joki) --}}
                        <a
                            href="{{ $waUrls['admin'] ?? route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'admin']) }}"
                            target="_blank"
                            class="flex items-center justify-between p-3 rounded-2xl bg-cyan-50 dark:bg-cyan-950/30 hover:bg-cyan-100 dark:hover:bg-cyan-950/50 border border-cyan-300 dark:border-cyan-500/30 text-slate-900 dark:text-white font-semibold transition group"
                        >
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-cyan-700 dark:text-cyan-400 font-bold">WA Admin:</span>
                                    <span class="text-xs text-slate-900 dark:text-white">{{ $isJoki ? 'Konfirmasi & Notifikasi Pesanan' : 'Konfirmasi Pesanan Top Up' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">0813-8484-9982 (Aethereal Admin)</div>
                            </div>
                            <span class="text-cyan-600 dark:text-cyan-400 group-hover:translate-x-0.5 transition shrink-0">&rarr;</span>
                        </a>

                        {{-- 2. WA Pembeli (Pending & Sukses) --}}
                        @php
                            $isPaid = ($order->payment && $order->payment->status === 'paid') || in_array($order->order_status, ['paid', 'processing', 'completed']);
                        @endphp
                        <a
                            href="{{ $isPaid ? ($waUrls['customer_success'] ?? route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'customer-success'])) : ($waUrls['customer_pending'] ?? route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'customer-pending'])) }}"
                            target="_blank"
                            class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 hover:bg-emerald-100 dark:hover:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-500/30 text-slate-900 dark:text-white font-semibold transition group"
                        >
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">WA Pembeli:</span>
                                    <span class="text-xs text-slate-900 dark:text-white">{{ $isPaid ? ($isJoki ? 'Kirim Konfirmasi Sukses' : 'Kirim Bukti Top Up Sukses') : 'Kirim Detail Tagihan Pending' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $waUrls['customer_phone'] ?? $order->customer_whatsapp }}</div>
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-400 group-hover:translate-x-0.5 transition shrink-0">&rarr;</span>
                        </a>

                        {{-- 3. WA Penjoki (Khusus Jokian Masuk) --}}
                        @if($isJoki)
                            <a
                                href="{{ $waUrls['booster'] ?? route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'booster']) }}"
                                target="_blank"
                                class="flex items-center justify-between p-3 rounded-2xl bg-purple-50 dark:bg-purple-950/30 hover:bg-purple-100 dark:hover:bg-purple-950/50 border border-purple-300 dark:border-purple-500/30 text-slate-900 dark:text-white font-semibold transition group"
                            >
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-purple-700 dark:text-purple-400 font-bold">WA Penjoki:</span>
                                        <span class="text-xs text-slate-900 dark:text-white">Notifikasi Order Joki Masuk</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">0822-9856-1286 (Booster Workstation)</div>
                                </div>
                                <span class="text-purple-600 dark:text-purple-400 group-hover:translate-x-0.5 transition shrink-0">&rarr;</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
