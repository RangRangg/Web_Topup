<x-layout title="Masuk ke Akun">
    <div class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block group mb-3">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="AETHEREL"
                    class="w-16 h-16 mx-auto rounded-2xl object-contain shadow-xl shadow-cyan-500/20 group-hover:scale-105 transition"
                />
            </a>
            <x-badge variant="primary" size="sm" class="mb-2">LOGIN CLIENT & ADMIN</x-badge>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Selamat Datang</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Masuk ke akun Aetherel untuk cek riwayat dan status pesanan.</p>
        </div>

        <div class="p-8 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-xl dark:shadow-2xl space-y-6">
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <x-input
                    label="Alamat Email"
                    name="email"
                    type="email"
                    placeholder="nama@email.com"
                    required="true"
                    :value="old('email')"
                />

                <x-input
                    label="Password"
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    required="true"
                />

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                        <input type="checkbox" name="remember" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-cyan-600 dark:text-cyan-500 focus:ring-cyan-500" />
                        <span>Ingat Saya</span>
                    </label>

                    <a href="#" class="text-cyan-600 dark:text-cyan-400 hover:underline">Lupa Password?</a>
                </div>

                <div class="pt-2">
                    <x-button type="submit" variant="primary" size="lg" class="w-full">
                        Masuk ke Akun
                    </x-button>
                </div>
            </form>

            <div class="text-center text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-200 dark:border-slate-800">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-cyan-600 dark:text-cyan-400 font-bold hover:underline">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</x-layout>
