<x-layout title="Profil Pro Booster — Booster Dashboard">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Booster Workstation', 'url' => route('joki.dashboard')],
            ['label' => 'Profil Pro Booster'],
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
                    Profil & Kredensial Booster
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Atur status ketersediaan Anda, spesialisasi game, dan informasi publik profil booster Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div class="px-3.5 py-1.5 rounded-xl bg-purple-950/40 border border-purple-500/30 text-xs text-purple-300 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Verified Pro Booster</span>
                </div>
            </div>
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('joki.dashboard.partials.nav')

        <div class="space-y-8">
            {{-- Performance Stats Card --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
                    <span class="text-slate-400 block text-xs">Rating Pro Booster</span>
                    <span class="text-2xl font-black text-amber-400 mt-1 block flex items-center gap-1">
                        &#9733; {{ number_format($jokiProfile->rating ?? 5.0, 1) }}
                    </span>
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Berdasarkan ulasan klien</span>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
                    <span class="text-slate-400 block text-xs">Pesanan Diselesaikan</span>
                    <span class="text-2xl font-black text-emerald-400 mt-1 block">
                        {{ $jokiProfile->completed_orders_count ?? 0 }}
                    </span>
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Pengerjaan sukses</span>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
                    <span class="text-slate-400 block text-xs">Rank Tertinggi</span>
                    <span class="text-lg font-black text-purple-300 mt-1 block truncate">
                        {{ $jokiProfile->highest_rank ?? 'Immortal / Radiant' }}
                    </span>
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Kredensial pro player</span>
                </div>
            </div>

            {{-- Profile Settings Form --}}
            <div class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Pengaturan Data Profil Booster
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola informasi yang ditampilkan kepada klien dan admin marketplace.
                    </p>
                </div>

                <form method="POST" action="{{ route('joki.dashboard.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nickname Booster --}}
                        <div>
                            <label for="nickname" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Nickname Booster <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="text"
                                name="nickname"
                                id="nickname"
                                value="{{ old('nickname', $jokiProfile->nickname) }}"
                                required
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition @error('nickname') border-rose-500 @enderror"
                            >
                            @error('nickname')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status Keaktifan --}}
                        <div>
                            <label for="status" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Status Keaktifan <span class="text-rose-400">*</span>
                            </label>
                            <select
                                name="status"
                                id="status"
                                class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl text-xs sm:text-sm text-white transition @error('status') border-rose-500 @enderror"
                            >
                                <option value="active" {{ old('status', $jokiProfile->status) === 'active' ? 'selected' : '' }}>🟢 Aktif (Siap Menerima Order)</option>
                                <option value="busy" {{ old('status', $jokiProfile->status) === 'busy' ? 'selected' : '' }}>🟡 Sedang Sibuk (Kapasitas Penuh)</option>
                                <option value="resting" {{ old('status', $jokiProfile->status) === 'resting' ? 'selected' : '' }}>🔴 Istirahat / Libur</option>
                            </select>
                            @error('status')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nomor WhatsApp --}}
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Nomor WhatsApp Booster
                            </label>
                            <input
                                type="text"
                                name="phone"
                                id="phone"
                                value="{{ old('phone', $jokiProfile->phone ?? $user->phone) }}"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 font-mono transition @error('phone') border-rose-500 @enderror"
                            >
                            @error('phone')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Rank Tertinggi --}}
                        <div>
                            <label for="highest_rank" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Rank Tertinggi yang Pernah Dicapai
                            </label>
                            <input
                                type="text"
                                name="highest_rank"
                                id="highest_rank"
                                value="{{ old('highest_rank', $jokiProfile->highest_rank) }}"
                                placeholder="Contoh: Mythical Immortal 120+, Radiant Top 200"
                                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 transition @error('highest_rank') border-rose-500 @enderror"
                            >
                            @error('highest_rank')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Bio Booster --}}
                    <div>
                        <label for="bio" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Bio & Pengalaman Kompetitif
                        </label>
                        <textarea
                            name="bio"
                            id="bio"
                            rows="3"
                            placeholder="Tuliskan pengalaman kompetitif, hero pool, dan keahlian role Anda..."
                            class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm text-white placeholder-slate-500 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition @error('bio') border-rose-500 @enderror"
                        >{{ old('bio', $jokiProfile->bio) }}</textarea>
                        @error('bio')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Specialized Games Checkboxes --}}
                    @php
                        $myGames = (array) ($jokiProfile->specialized_games ?? []);
                        $availableGameList = ['Mobile Legends', 'Valorant', 'Free Fire', 'PUBG Mobile', 'Genshin Impact', 'Honor of Kings'];
                    @endphp
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2">
                            Game Spesialisasi Pengerjaan
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($availableGameList as $g)
                                <label class="p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 flex items-center gap-2.5 cursor-pointer transition">
                                    <input
                                        type="checkbox"
                                        name="specialized_games[]"
                                        value="{{ $g }}"
                                        {{ in_array($g, $myGames) ? 'checked' : '' }}
                                        class="rounded text-purple-600 focus:ring-purple-500 bg-slate-900 border-slate-700"
                                    >
                                    <span class="text-xs text-white font-medium">{{ $g }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button type="submit" variant="primary" size="md" class="bg-purple-600 hover:bg-purple-500 border-none shadow-[0_0_20px_rgba(147,51,234,0.3)]">
                            Simpan Perubahan Profil
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
