<x-layout title="Daftar Akun Baru">
    <div class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block group mb-3">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="AETHEREL"
                    class="w-16 h-16 mx-auto rounded-2xl object-contain shadow-xl shadow-cyan-500/20 group-hover:scale-105 transition"
                />
            </a>
            <x-badge variant="primary" size="sm" class="mb-2">REGISTRASI PENGGUNA</x-badge>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Buat Akun Baru</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Dapatkan diskon loyalitas dan kemudahan pantau order game kamu di Aetherel.</p>
        </div>

        <div class="p-8 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-xl dark:shadow-2xl space-y-6">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <x-input
                    label="Nama Lengkap"
                    name="name"
                    placeholder="Contoh: Budi Santoso"
                    required="true"
                    :value="old('name')"
                />

                <x-input
                    label="Alamat Email"
                    name="email"
                    type="email"
                    placeholder="nama@email.com"
                    required="true"
                    :value="old('email')"
                />

                <x-input
                    label="Nomor WhatsApp"
                    name="phone"
                    placeholder="08123456789"
                    :value="old('phone')"
                    hint="Untuk info progres joki"
                />

                <x-input
                    label="Password"
                    name="password"
                    type="password"
                    placeholder="Minimal 6 karakter"
                    required="true"
                />

                <x-input
                    label="Konfirmasi Password"
                    name="password_confirmation"
                    type="password"
                    placeholder="Ulangi password"
                    required="true"
                />

                <div class="pt-2">
                    <x-button type="submit" variant="primary" size="lg" class="w-full">
                        Daftar Akun Aethereal
                    </x-button>
                </div>
            </form>

            <div class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-200 dark:border-slate-800">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-cyan-600 dark:text-cyan-400 font-bold hover:underline">Masuk di Sini</a>
            </div>
        </div>
    </div>
</x-layout>
