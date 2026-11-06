<x-layout title="403: Akses Ditolak">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <span class="inline-block px-3 py-1 rounded-lg bg-rose-500/10 border border-rose-500/20 text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider mb-4">
            Error 403
        </span>

        <h1 class="text-7xl sm:text-8xl font-black text-slate-900 dark:text-white tracking-tight mb-4 select-none">
            403
        </h1>

        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mb-3">
            Akses Ditolak
        </h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-8">
            Anda tidak memiliki izin untuk mengakses halaman ini. Silakan masuk dengan akun yang memiliki hak akses.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3">
            <x-button as="a" :href="route('home')" variant="primary" size="md">
                Kembali ke Beranda
            </x-button>
            <x-button as="a" :href="route('login')" variant="secondary" size="md">
                Masuk ke Akun
            </x-button>
        </div>
    </div>
</x-layout>
