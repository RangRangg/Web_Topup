<x-layout title="Profil & Keamanan Akun — Customer Dashboard">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profil & Keamanan'],
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

        {{-- Header Section --}}
        <div class="mt-6 mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Profil & Keamanan Akun
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Kelola informasi pribadi, nomor kontak WhatsApp, dan kata sandi akun Aethereal Joki Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Role: <strong class="text-white capitalize">{{ $user->role }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('dashboard.partials.nav')

        <div class="space-y-8">
            {{-- Form 1: Informasi Profil Pribadi --}}
            <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informasi Pribadi
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Perbarui nama tampilan dan nomor kontak aktif untuk notifikasi pesanan.
                    </p>
                </div>

                <form method="POST" action="{{ route('dashboard.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Nama Lengkap <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition @error('name') border-rose-500 @enderror"
                        >
                        @error('name')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email (Read-Only) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300">
                                Alamat Email
                            </label>
                            <span class="text-[10px] text-emerald-400 font-semibold flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Terverifikasi
                            </span>
                        </div>
                        <input
                            type="email"
                            value="{{ $user->email }}"
                            disabled
                            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800/80 rounded-xl text-xs sm:text-sm text-slate-400 cursor-not-allowed font-mono"
                        >
                        <p class="text-[11px] text-slate-500 mt-1">
                            Alamat email terdaftar tidak dapat diubah secara langsung untuk menjaga keamanan akun.
                        </p>
                    </div>

                    {{-- Nomor WhatsApp --}}
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Nomor WhatsApp / Telepon
                        </label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="Contoh: 081234567890"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 font-mono transition @error('phone') border-rose-500 @enderror"
                        >
                        @error('phone')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-slate-500 mt-1">
                            Digunakan untuk konfirmasi pesanan dan koordinasi pengerjaan joki oleh tim.
                        </p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button type="submit" variant="primary" size="md">
                            Simpan Perubahan Profil
                        </x-button>
                    </div>
                </form>
            </div>

            {{-- Form 2: Keamanan & Ganti Password --}}
            <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Keamanan & Ganti Kata Sandi
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Pastikan Anda menggunakan kata sandi yang kuat dan unik demi keamanan data akun Anda.
                    </p>
                </div>

                <form method="POST" action="{{ route('dashboard.profile.password') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Kata Sandi Saat Ini <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            required
                            placeholder="Masukkan kata sandi saat ini"
                            class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition @error('current_password') border-rose-500 @enderror"
                        >
                        @error('current_password')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- New Password --}}
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Kata Sandi Baru <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition @error('password') border-rose-500 @enderror"
                            >
                            @error('password')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm New Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Konfirmasi Kata Sandi Baru <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                placeholder="Ulangi kata sandi baru"
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition"
                            >
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button type="submit" variant="primary" size="md">
                            Perbarui Kata Sandi
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
