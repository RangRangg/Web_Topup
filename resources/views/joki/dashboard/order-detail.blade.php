<x-layout :title="'Pengerjaan Order #' . $order->order_code . ' — Booster Dashboard'">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Booster Workstation', 'url' => route('joki.dashboard')],
            ['label' => 'Antrean & Tugas', 'url' => route('joki.dashboard.orders')],
            ['label' => '#' . $order->order_code],
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

        {{-- Top Navigation & Action Controls --}}
        <div class="mt-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <a href="{{ route('joki.dashboard.orders') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Antrean & Tugas
            </a>

            <div class="flex items-center gap-2">
                @if($isAvailableInPool)
                    <form method="POST" action="{{ route('joki.dashboard.orders.claim', $order->order_code) }}">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm" class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold border-none shadow-[0_0_15px_rgba(6,182,212,0.3)]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Ambil Tugas Ini Sekarang
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Order Header Card --}}
        <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl mb-6 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <x-badge variant="purple" size="sm">
                            {{ $gameName }}
                        </x-badge>
                        @if($isAssignedToMe)
                            <x-badge variant="success" size="sm" :dot="true">Ditugaskan Kepada Anda</x-badge>
                        @elseif($isAvailableInPool)
                            <x-badge variant="primary" size="sm">Tersedia di Pool</x-badge>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-white font-mono flex items-center gap-2">
                        <span>#{{ $order->order_code }}</span>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $order->order_code }}'); alert('Kode order #{{ $order->order_code }} disalin!')"
                            class="p-1 rounded text-slate-400 hover:text-purple-400 transition"
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
                        <span class="text-xs text-slate-400">Status Saat Ini:</span>
                        <x-badge :variant="$order->status_badge_variant" size="md" :dot="true">
                            {{ $order->status_label }}
                        </x-badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Status Bayar:</span>
                        <span class="text-xs font-bold text-emerald-400">LUNAS (VERIFIED)</span>
                    </div>
                </div>
            </div>

            {{-- Booster Commission Banner --}}
            @php
                $boosterCut = (float) $order->total_amount * 0.80;
                $platformCut = (float) $order->total_amount * 0.20;
            @endphp
            <div class="pt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                    <span class="text-slate-400 block text-[11px]">Total Nilai Order</span>
                    <span class="text-base font-bold text-white mt-0.5 block">{{ $order->formatted_total }}</span>
                </div>
                <div class="p-4 rounded-xl bg-emerald-950/30 border border-emerald-500/30">
                    <span class="text-emerald-400 font-semibold block text-[11px]">Komisi Bersih Booster (80%)</span>
                    <span class="text-lg font-black text-emerald-400 mt-0.5 block">Rp {{ number_format($boosterCut, 0, ',', '.') }}</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                    <span class="text-slate-400 block text-[11px]">Potongan Platform (20%)</span>
                    <span class="text-base font-bold text-slate-300 mt-0.5 block">Rp {{ number_format($platformCut, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Main Detail Layout: 2 Columns on Desktop --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left 2 Columns: Credentials, Service Target, Update Progress Form --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Account Game Credentials Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Data Akun Game Klien
                        </h3>
                        <span class="text-[10px] text-amber-400 font-bold bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/30">CONFIDENTIAL</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Jaga kerahasiaan informasi akun klien. Dilarang keras membagikan kredensial login atau mengubah data akun tanpa izin.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
                        <div>
                            <span class="text-slate-400 block text-[11px]">User ID Game</span>
                            <span class="font-mono font-bold text-cyan-400 text-sm mt-0.5 block">{{ $order->account_id ?? '-' }}</span>
                        </div>

                        @if($order->account_server)
                            <div>
                                <span class="text-slate-400 block text-[11px]">Server / Zone ID</span>
                                <span class="font-mono font-bold text-slate-200 text-sm mt-0.5 block">{{ $order->account_server }}</span>
                            </div>
                        @endif

                        @if($order->account_nickname)
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nickname Akun Game</span>
                                <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $order->account_nickname }}</span>
                            </div>
                        @endif

                        @if($order->login_via)
                            <div>
                                <span class="text-slate-400 block text-[11px]">Metode Login</span>
                                <span class="font-semibold text-slate-200 mt-0.5 block">{{ ucfirst($order->login_via) }}</span>
                            </div>
                        @endif
                    </div>

                    @if($order->account_notes)
                        <div class="pt-3 border-t border-slate-800">
                            <span class="text-slate-400 block text-[11px] mb-1">Catatan Khusus dari Klien:</span>
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 italic">
                                "{{ $order->account_notes }}"
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Service & Target Information Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/>
                        </svg>
                        Target Pengerjaan Joki
                    </h3>

                    @foreach($order->orderItems as $item)
                        <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800/80 space-y-2">
                            <h4 class="font-bold text-white text-sm">{{ $item->item_name }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-400 pt-1">
                                <div>
                                    <span class="block text-[11px] text-slate-500">Target Rank:</span>
                                    <span class="font-semibold text-slate-200">{{ $item->jokiService?->target ?? 'Sesuai Request Klien' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[11px] text-slate-500">Estimasi Waktu Pengerjaan:</span>
                                    <span class="font-semibold text-slate-200">{{ $item->jokiService?->estimated_time ?? '1 - 2 Hari' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Action Form: Update Progress (Only if assigned to this booster) --}}
                @if($isAssignedToMe)
                    <div class="p-6 rounded-2xl bg-slate-900/90 border border-purple-500/40 shadow-xl space-y-5">
                        <div class="border-b border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Update Progres & Status Pengerjaan
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Perubahan status akan otomatis dicatat pada Audit Log sistem dan dikirimkan ke notifikasi klien.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('joki.dashboard.orders.progress', $order->order_code) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- Status Radio/Select --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-2">
                                    Pilih Status Pengerjaan <span class="text-rose-400">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-center gap-3 {{ $order->order_status === 'processing' ? 'bg-purple-950/40 border-purple-500 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                        <input
                                            type="radio"
                                            name="order_status"
                                            value="processing"
                                            {{ $order->order_status === 'processing' ? 'checked' : '' }}
                                            class="text-purple-600 focus:ring-purple-500"
                                        >
                                        <div>
                                            <span class="block font-bold text-xs text-white">PROCESSING</span>
                                            <span class="text-[10px] text-slate-400">Sedang Dikerjakan / Push Rank</span>
                                        </div>
                                    </label>

                                    <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-center gap-3 {{ $order->order_status === 'completed' ? 'bg-emerald-950/40 border-emerald-500 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                        <input
                                            type="radio"
                                            name="order_status"
                                            value="completed"
                                            {{ $order->order_status === 'completed' ? 'checked' : '' }}
                                            class="text-emerald-600 focus:ring-emerald-500"
                                        >
                                        <div>
                                            <span class="block font-bold text-xs text-emerald-400">COMPLETED</span>
                                            <span class="text-[10px] text-slate-400">Selesai (Target Tercapai)</span>
                                        </div>
                                    </label>
                                </div>
                                @error('order_status')
                                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Progress Note --}}
                            <div>
                                <label for="progress_note" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Catatan Progres (Progress Note untuk Klien)
                                </label>
                                <textarea
                                    name="progress_note"
                                    id="progress_note"
                                    rows="3"
                                    placeholder="Contoh: Sedang push bintang di Mythic 15. Win rate aman 80%. Estimasi selesai nanti malam."
                                    class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition @error('progress_note') border-rose-500 @enderror"
                                >{{ old('progress_note', $order->progress_note) }}</textarea>
                                @error('progress_note')
                                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Klien dapat melihat catatan ini di halaman pelacakan pesanan mereka.
                                </p>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <x-button type="submit" variant="primary" size="md" class="bg-purple-600 hover:bg-purple-500 border-none shadow-[0_0_20px_rgba(147,51,234,0.3)]">
                                    Simpan & Perbarui Progres
                                </x-button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Right Column: Status Log & Help --}}
            <div class="space-y-6">
                {{-- Current Progress Note Card --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        Catatan Progres Saat Ini
                    </h4>

                    @if($order->progress_note)
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/90 text-xs text-purple-200 leading-relaxed italic">
                            "{{ $order->progress_note }}"
                        </div>
                    @else
                        <p class="text-xs text-slate-500 italic">Belum ada catatan progres yang ditambahkan.</p>
                    @endif
                </div>

                {{-- Client Contact & Emergency Assistance --}}
                <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Bantuan & Koordinasi
                    </h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Jika terdapat kendala login (verifikasi 2FA atau kode OTP), hubungi admin pusat untuk koordinasi dengan klien.
                    </p>
                    @if($order->customer_whatsapp)
                        <div class="pt-2">
                            <span class="text-[11px] text-slate-500 block">WhatsApp Klien:</span>
                            <span class="font-mono text-xs text-slate-300 font-semibold">{{ $order->customer_whatsapp }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-800 space-y-2">
                        @if(!empty($waUrls['client']))
                            <a
                                href="{{ $waUrls['client'] }}"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-2 w-full px-3.5 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold transition"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>Hubungi Klien via WhatsApp</span>
                            </a>
                        @endif

                        @if(!empty($waUrls['admin']))
                            <a
                                href="{{ $waUrls['admin'] }}"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-2 w-full px-3.5 py-2 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 text-xs font-bold transition"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                </svg>
                                <span>Lapor Admin via WA (0813-8484-9982)</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
