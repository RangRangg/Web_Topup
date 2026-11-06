<x-layout :title="'Detail Pesanan #' . $order->order_code . ' — Customer Dashboard'">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <div class="print:hidden">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Pesanan Saya', 'url' => route('dashboard.orders')],
                ['label' => '#' . $order->order_code],
            ]" />
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mt-4 print:hidden">
                <x-alert type="success" :dismissible="true">
                    {{ session('success') }}
                </x-alert>
            </div>
        @endif

        @if(session('error'))
            <div class="mt-4 print:hidden">
                <x-alert type="error" :dismissible="true">
                    {{ session('error') }}
                </x-alert>
            </div>
        @endif

        {{-- Top Navigation & Action Controls --}}
        <div class="mt-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
            <a href="{{ route('dashboard.orders') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Pesanan Saya
            </a>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-300 hover:text-white transition"
                    title="Cetak Invoice"
                >
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Invoice
                </button>

                <a
                    href="{{ route('orders.show', $order->order_code) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-300 hover:text-cyan-400 transition"
                    title="Halaman Lacak Publik"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Lacak Publik
                </a>
            </div>
        </div>

        {{-- Order Header Card --}}
        <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl mb-6 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <x-badge variant="primary" size="sm">
                            {{ $orderType }}
                        </x-badge>
                        <x-badge variant="purple" size="sm">
                            {{ $gameName }}
                        </x-badge>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-white font-mono flex items-center gap-2">
                        <span>#{{ $order->order_code }}</span>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $order->order_code }}'); alert('Kode order #{{ $order->order_code }} disalin!')"
                            class="p-1 rounded text-slate-400 hover:text-cyan-400 transition print:hidden"
                            title="Salin kode pesanan"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">
                        Dibuat pada {{ $order->created_at->translatedFormat('l, d F Y, H:i') }} WIB
                    </p>
                </div>

                <div class="flex flex-col sm:items-end gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Status Order:</span>
                        <x-badge :variant="$order->status_badge_variant" size="md" :dot="true">
                            {{ $order->status_label }}
                        </x-badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Pembayaran:</span>
                        @if($order->payment && $order->payment->isPaid())
                            <x-badge variant="success" size="sm">LUNAS</x-badge>
                        @elseif($order->payment && $order->payment->isPending())
                            <x-badge variant="warning" size="sm">PENDING</x-badge>
                        @else
                            <x-badge variant="danger" size="sm">{{ strtoupper($order->payment?->status ?? 'BELUM BAYAR') }}</x-badge>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Visual Timeline Progress Tracker --}}
            @php
                $isPaid = in_array($order->order_status, ['waiting_assignment', 'paid', 'processing', 'completed']) || ($order->payment && $order->payment->status === 'paid');
                $isAssignedOrProcessing = in_array($order->order_status, ['processing', 'completed']) || $order->joki_profile_id !== null;
                $isCompleted = $order->order_status === 'completed';
            @endphp

            <div class="pt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
                    Timeline Progres Pesanan
                </h3>

                <div class="relative">
                    {{-- Connecting bar --}}
                    <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-slate-800 sm:left-0 sm:right-0 sm:top-5 sm:bottom-auto sm:w-full sm:h-0.5"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 relative">
                        {{-- Step 1: Pesanan Dibuat --}}
                        <div class="flex sm:flex-col items-start sm:items-center gap-4 sm:gap-2 relative pl-10 sm:pl-0 sm:text-center">
                            <div class="absolute left-1.5 sm:static w-6 h-6 rounded-full bg-emerald-500 border-4 border-slate-900 flex items-center justify-center text-slate-950 font-bold text-xs shadow-[0_0_12px_rgba(16,185,129,0.5)]">
                                <svg class="w-3 h-3 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Pesanan Dibuat</h4>
                                <span class="text-[10px] text-slate-400 block">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        {{-- Step 2: Pembayaran --}}
                        <div class="flex sm:flex-col items-start sm:items-center gap-4 sm:gap-2 relative pl-10 sm:pl-0 sm:text-center">
                            <div class="absolute left-1.5 sm:static w-6 h-6 rounded-full {{ $isPaid ? 'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]' : 'bg-slate-800 text-slate-500' }} border-4 border-slate-900 flex items-center justify-center font-bold text-xs">
                                @if($isPaid)
                                    <svg class="w-3 h-3 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold {{ $isPaid ? 'text-white' : 'text-slate-500' }}">Pembayaran Terverifikasi</h4>
                                <span class="text-[10px] text-slate-400 block">
                                    {{ $order->payment?->paid_at ? $order->payment->paid_at->format('d/m/Y H:i') : ($isPaid ? 'Terkonfirmasi' : 'Menunggu Bayar') }}
                                </span>
                            </div>
                        </div>

                        {{-- Step 3: Pengerjaan / Pengisian --}}
                        <div class="flex sm:flex-col items-start sm:items-center gap-4 sm:gap-2 relative pl-10 sm:pl-0 sm:text-center">
                            <div class="absolute left-1.5 sm:static w-6 h-6 rounded-full {{ ($isCompleted || $isAssignedOrProcessing) ? ($order->isTopUp() ? 'bg-cyan-500 shadow-[0_0_12px_rgba(6,182,212,0.5)]' : 'bg-purple-500 shadow-[0_0_12px_rgba(168,85,247,0.5)]') : 'bg-slate-800 text-slate-500' }} border-4 border-slate-900 flex items-center justify-center font-bold text-xs">
                                @if($isCompleted || $isAssignedOrProcessing)
                                    <svg class="w-3 h-3 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold {{ ($isCompleted || $isAssignedOrProcessing) ? 'text-white' : 'text-slate-500' }}">
                                    {{ $order->isTopUp() ? 'Pengisian Diamond' : 'Proses Pengerjaan' }}
                                </h4>
                                <span class="text-[10px] text-slate-400 block">
                                    @if($order->isTopUp())
                                        {{ $isCompleted ? 'Otomatis Terkirim' : ($isPaid ? 'Sedang Diproses' : 'Menunggu Bayar') }}
                                    @elseif($order->jokiProfile)
                                        Booster: {{ $order->jokiProfile->nickname }}
                                    @elseif($isAssignedOrProcessing)
                                        Sedang Diproses
                                    @else
                                        Antrean Pengerjaan
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Step 4: Selesai --}}
                        <div class="flex sm:flex-col items-start sm:items-center gap-4 sm:gap-2 relative pl-10 sm:pl-0 sm:text-center">
                            <div class="absolute left-1.5 sm:static w-6 h-6 rounded-full {{ $isCompleted ? 'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]' : 'bg-slate-800 text-slate-500' }} border-4 border-slate-900 flex items-center justify-center font-bold text-xs">
                                @if($isCompleted)
                                    <svg class="w-3 h-3 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold {{ $isCompleted ? 'text-white' : 'text-slate-500' }}">
                                    {{ $order->isTopUp() ? 'Top Up Selesai' : 'Pesanan Selesai' }}
                                </h4>
                                <span class="text-[10px] text-slate-400 block">
                                    {{ $order->completed_at ? $order->completed_at->format('d/m/Y H:i') : ($isCompleted ? ($order->isTopUp() ? 'Sukses Masuk Akun' : 'Selesai') : 'Estimasi Selesai') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Detail Layout: 2 Columns on Desktop, 1 Column on Mobile --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left 2 Columns: Order Details, Items, Booster, Reviews --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Account Game Information Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Informasi Akun & Game
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nama Game</span>
                            <span class="font-bold text-white text-sm mt-0.5 block">{{ $gameName }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px]">User ID Game</span>
                            <span class="font-mono font-bold text-cyan-400 text-sm mt-0.5 block">{{ $order->account_id ?? '-' }}</span>
                        </div>

                        @if($order->account_server)
                            <div>
                                <span class="text-slate-400 block text-[11px]">Server / Zone ID</span>
                                <span class="font-mono font-bold text-slate-300 text-sm mt-0.5 block">{{ $order->account_server }}</span>
                            </div>
                        @endif

                        @if($order->account_nickname)
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nickname Akun Game</span>
                                <span class="font-bold text-slate-300 text-sm mt-0.5 block">{{ $order->account_nickname }}</span>
                            </div>
                        @endif

                        @if($order->login_via && $order->isJoki())
                            <div>
                                <span class="text-slate-400 block text-[11px]">Login Melalui</span>
                                <span class="font-semibold text-slate-300 mt-0.5 block">{{ ucfirst($order->login_via) }}</span>
                            </div>
                        @endif

                        @if($order->customer_whatsapp)
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nomor WhatsApp Pembeli</span>
                                <span class="font-mono text-slate-300 mt-0.5 block">{{ $order->customer_whatsapp }}</span>
                            </div>
                        @endif
                    </div>

                    @if($order->account_notes)
                        <div class="pt-3 border-t border-slate-800">
                            <span class="text-slate-400 block text-[11px] mb-1">Catatan Pesanan:</span>
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 italic">
                                "{{ $order->account_notes }}"
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Order Items Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Rincian Item / Paket
                    </h3>

                    <div class="space-y-3">
                        @foreach($order->orderItems as $item)
                            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-white text-sm">{{ $item->item_name }}</h4>
                                        <x-badge variant="{{ $item->item_type === 'joki_service' ? 'purple' : 'primary' }}" size="sm">
                                            {{ $item->item_type === 'joki_service' ? 'Joki Service' : 'Top Up' }}
                                        </x-badge>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Harga Satuan: Rp {{ number_format($item->price, 0, ',', '.') }} &times; {{ $item->quantity }}
                                    </p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <span class="text-xs text-slate-400 block">Subtotal</span>
                                    <span class="text-sm font-black text-cyan-400">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Assigned Booster Information or Top Up Auto-Delivery Card --}}
                @if($order->isJoki())
                    <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informasi Pro Booster
                        </h3>

                        @if($order->jokiProfile)
                            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800/80 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-purple-500 to-indigo-600 flex items-center justify-center text-white font-black text-base shadow-lg">
                                        {{ strtoupper(substr($order->jokiProfile->nickname, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-white text-sm">{{ $order->jokiProfile->nickname }}</h4>
                                            <x-badge variant="purple" size="sm">Verified Pro</x-badge>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 text-xs">
                                            <span class="text-amber-400 font-bold flex items-center gap-1">
                                                &#9733; {{ number_format($order->jokiProfile->rating ?? 5.0, 1) }}
                                            </span>
                                            <span class="text-slate-600">&bull;</span>
                                            <span class="text-slate-400">{{ $order->jokiProfile->completed_orders_count ?? 0 }} Order Selesai</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <x-badge variant="success" size="sm" :dot="true">Ditugaskan</x-badge>
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-slate-950/50 border border-dashed border-slate-800 flex items-center gap-3 text-slate-400 text-xs">
                                <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Order dalam antrean penugasan. Sistem & Admin akan segera menugaskan Pro Booster terverifikasi untuk pengerjaan akun Anda.</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Status Pengiriman Top Up Otomatis
                        </h3>
                        <div class="p-4 rounded-xl bg-slate-950/70 border border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $order->order_status === 'completed' ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                                    <h4 class="font-bold text-white text-sm">
                                        {{ $order->order_status === 'completed' ? 'Pengiriman Diamond Sukses (Auto-Completed)' : 'Menunggu Pembayaran' }}
                                    </h4>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ $order->order_status === 'completed' ? 'Diamond telah otomatis terkirim langsung ke ID Game '.$order->account_id.' tanpa login akun.' : 'Sistem otomatis akan langsung menginjeksi diamond setelah pembayaran lunas diverifikasi.' }}
                                </p>
                            </div>
                            <div>
                                <x-badge variant="{{ $order->order_status === 'completed' ? 'success' : 'warning' }}" size="sm">
                                    {{ $order->order_status === 'completed' ? 'OTOMATIS SUKSES' : 'PENDING' }}
                                </x-badge>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Review Section (After COMPLETED) --}}
                @if($order->order_status === 'completed')
                    <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Ulasan Layanan
                        </h3>

                        @if($order->reviews->isNotEmpty())
                            @php $review = $order->reviews->first(); @endphp
                            <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1 text-amber-400 text-sm">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                        @endfor
                                        <span class="text-xs font-bold text-white ml-1.5">({{ $review->rating }}/5)</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500">{{ $review->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed">{{ $review->comment }}</p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('orders.review', $order->order_code) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Beri Rating Bintang (1 - 5)</label>
                                    <select name="rating" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                        <option value="5">★★★★★ (5 Bintang - Sangat Puas)</option>
                                        <option value="4">★★★★☆ (4 Bintang - Puas)</option>
                                        <option value="3">★★★☆☆ (3 Bintang - Cukup)</option>
                                        <option value="2">★★☆☆☆ (2 Bintang - Kurang)</option>
                                        <option value="1">★☆☆☆☆ (1 Bintang - Kecewa)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ulasan / Testimoni Anda</label>
                                    <textarea
                                        name="comment"
                                        rows="3"
                                        placeholder="Bagaimana pengalaman Anda menggunakan layanan Aethereal Joki?"
                                        class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                                        required
                                    ></textarea>
                                </div>

                                <x-button type="submit" variant="primary" size="sm">
                                    Kirim Ulasan & Rating
                                </x-button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right Column: Invoice Summary & Payment Card --}}
            <div class="space-y-6">
                {{-- Official Invoice Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-5">
                    <div class="border-b border-slate-800 pb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">INVOICE RESMI</span>
                            <span class="font-mono text-xs text-cyan-400 font-bold">#{{ $order->order_code }}</span>
                        </div>
                        <h4 class="text-base font-black text-white mt-1">Aethereal Joki Marketplace</h4>
                        <p class="text-[10px] text-slate-500">Platform Top Up & Joki Terpercaya</p>
                    </div>

                    {{-- Price Breakdown --}}
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal</span>
                            <span class="text-white font-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>

                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-400">
                                <span>Potongan Diskon Promo</span>
                                <span class="font-semibold">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-slate-400">
                            <span>Biaya Layanan Sistem</span>
                            <span class="text-white font-semibold">Rp 0 (Gratis)</span>
                        </div>

                        <div class="pt-3 border-t border-slate-800 flex justify-between items-baseline">
                            <span class="font-bold text-white text-sm">Total Tagihan</span>
                            <span class="font-black text-lg text-cyan-400">{{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div class="pt-3 border-t border-slate-800 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Metode Bayar:</span>
                            <span class="text-white font-semibold">{{ strtoupper($order->payment?->payment_method ?? 'QRIS') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status Pembayaran:</span>
                            @if($order->payment && $order->payment->isPaid())
                                <span class="text-emerald-400 font-bold">LUNAS</span>
                            @else
                                <span class="text-amber-400 font-bold">BELUM LUNAS</span>
                            @endif
                        </div>
                        @if($order->payment?->paid_at)
                            <div class="flex justify-between">
                                <span class="text-slate-400">Waktu Bayar:</span>
                                <span class="text-slate-300 font-mono text-[11px]">{{ $order->payment->paid_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Pay button if pending --}}
                    @if($order->payment && $order->payment->isPending())
                        <div class="pt-2 border-t border-slate-800 print:hidden space-y-2">
                            <form method="POST" action="{{ route('orders.simulate-pay', $order->order_code) }}">
                                @csrf
                                <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                                    Simulasi Bayar Sekarang (Mock)
                                </x-button>
                            </form>
                            <p class="text-[10px] text-center text-slate-500">
                                Gunakan tombol di atas untuk menguji konfirmasi pembayaran otomatis.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Help & Customer Support Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-3 print:hidden">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Bantuan & Customer Service
                    </h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Mengalami kendala akun atau ingin menanyakan perkembangan joki lebih lanjut? Tim support Aethereal Joki siap membantu 24/7.
                    </p>
                    <a
                        href="{{ route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'admin']) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold transition"
                    >
                        <span>Chat WhatsApp Admin (0813-8484-9982)</span>
                        &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
