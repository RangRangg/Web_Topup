<x-layout title="404: Halaman Tidak Ditemukan">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <span class="inline-block px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-4">
            Error 404
        </span>

        <h1 class="text-7xl sm:text-8xl font-black text-slate-900 dark:text-white tracking-tight mb-4 select-none">
            404
        </h1>

        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mb-3">
            Halaman Tidak Ditemukan
        </h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-8">
            Halaman yang Anda tuju mungkin sudah dipindahkan, dihapus, atau alamat URL yang dimasukkan salah.
        </p>

        {{-- Navigation Action Buttons --}}
        <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
            <x-button as="a" :href="route('home')" variant="primary" size="md">
                Kembali ke Beranda
            </x-button>

            <x-button as="a" :href="route('topup')" variant="secondary" size="md">
                Katalog Top Up
            </x-button>

            <x-button as="a" :href="route('tracking')" variant="outline" size="md">
                Lacak Pesanan
            </x-button>
        </div>

        {{-- Quick Help Footer --}}
        <div class="pt-6 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-500">
            Butuh bantuan transaksi? Hubungi customer service 24/7 kami melalui WhatsApp resmi.
        </div>
    </div>
</x-layout>
