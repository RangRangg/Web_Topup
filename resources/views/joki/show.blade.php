<x-layout :title="$service->title . ' — Jasa Joki Resmi'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Jasa Joki', 'url' => route('joki')],
            ['label' => $service->game->name ?? 'Game', 'url' => route('joki', ['game' => $service->game->slug ?? ''])],
            ['label' => $service->title]
        ]" />

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-10">
            {{-- Main Column: Detail & Features --}}
            <div class="lg:col-span-8 space-y-8">
                <div class="p-8 rounded-3xl bg-slate-900/90 border border-slate-800 relative overflow-hidden shadow-2xl">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <x-badge variant="primary" size="sm">{{ $service->game->name ?? 'Layanan' }}</x-badge>
                        @if($service->is_popular)
                            <x-badge variant="purple" size="sm">BESTSELLER</x-badge>
                        @endif
                        <x-badge variant="success" size="sm">ESTIMASI: {{ $service->estimated_time }}</x-badge>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-black flex items-center gap-1">
                            ★ {{ number_format($service->rating, 1) }} / 5.0
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                        {{ $service->title }}
                    </h1>

                    <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-slate-300">
                        <span class="flex items-center gap-1.5 font-semibold text-cyan-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Target: <span class="text-white font-mono">{{ $service->target }}</span>
                        </span>
                        <span>&bull;</span>
                        <span class="flex items-center gap-1.5 font-semibold text-purple-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Estimasi: <span class="text-white">{{ $service->estimated_time }}</span>
                        </span>
                        <span>&bull;</span>
                        <span class="text-emerald-400 font-semibold">
                            Status: {{ ucfirst($service->status) }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-300 mt-5 leading-relaxed">
                        {{ $service->description }}
                    </p>

                    {{-- Features List --}}
                    <div class="mt-8 pt-6 border-t border-slate-800">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-4">Fitur Unggulan & Standar Keamanan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if(is_array($service->features))
                                @foreach($service->features as $feat)
                                    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs text-slate-200">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 font-bold text-xs">
                                            ✓
                                        </div>
                                        <span>{{ $feat }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Safe Guarantee Block --}}
                <div class="p-6 rounded-3xl bg-cyan-950/20 border border-cyan-500/30 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Garansi Keamanan Akun 100% Anti-Banned</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            Semua pengerjaan push rank di Aethereal Joki dikerjakan murni dengan tangan (manual hand) oleh pro booster tier tertinggi. Privasi chat in-game dijaga ketat, opsi mode offline tersedia, dan data login dienkripsi penuh.
                        </p>
                    </div>
                </div>

                {{-- Community Reviews Section --}}
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span class="text-amber-400">★</span>
                                Ulasan Gamers untuk Layanan Ini
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Testimoni terverifikasi dari customer yang telah menyelesaikan order.</p>
                        </div>
                        <span class="text-xs font-bold text-cyan-400">{{ $reviews->count() }} Ulasan</span>
                    </div>

                    @if($reviews->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($reviews as $rev)
                                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-white text-xs">{{ $rev->customer_name }}</span>
                                            @if($rev->is_verified)
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300 font-bold">Terverifikasi</span>
                                            @endif
                                        </div>
                                        <span class="text-amber-400 text-xs font-bold tracking-widest">
                                            {{ str_repeat('★', $rev->rating) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-300 leading-relaxed">{{ $rev->comment }}</p>
                                    <span class="text-[10px] text-slate-500 block">{{ $rev->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada ulasan untuk layanan ini. Jadilah yang pertama memberikan ulasan setelah menyelesaikan order!</p>
                    @endif
                </div>

                {{-- Related Packages --}}
                @if($relatedPackages->count() > 0)
                    <div class="pt-4">
                        <h3 class="text-lg font-bold text-white mb-4">Paket Lain dari {{ $service->game->name ?? 'Game Ini' }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach($relatedPackages as $rel)
                                <a href="{{ route('joki.show', $rel->slug) }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800 hover:border-cyan-500/40 transition flex flex-col justify-between group">
                                    <div>
                                        <h5 class="text-xs font-bold text-white group-hover:text-cyan-400 transition line-clamp-1">{{ $rel->title }}</h5>
                                        <p class="text-[11px] text-slate-400 mt-1 font-mono">{{ $rel->target }}</p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-800 text-xs font-black text-cyan-400">
                                        {{ $rel->formatted_starting_price }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Checkout Pricing Card --}}
            <div class="lg:col-span-4">
                <div class="sticky top-28 p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Mulai Dari</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black text-white">{{ $service->formatted_starting_price }}</span>
                            @if($service->formatted_original_price)
                                <span class="text-xs text-slate-400 line-through">{{ $service->formatted_original_price }}</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-cyan-400 font-semibold block mt-0.5">Satuan: {{ $service->unit }}</span>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-slate-800 text-xs text-slate-300">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Target Level / Rank</span>
                            <span class="font-bold text-cyan-300 font-mono">{{ $service->target }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Estimasi Durasi</span>
                            <span class="font-bold text-white">{{ $service->estimated_time }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Metode Joki</span>
                            <span class="font-bold text-cyan-400">Manual Hand 100%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Customer Support</span>
                            <span class="font-bold text-emerald-400">24/7 Siaga</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 space-y-3">
                        <x-button as="a" :href="route('joki.checkout', ['service' => $service->slug])" variant="primary" size="lg" class="w-full">
                            Checkout Layanan Ini
                        </x-button>
                        <a
                            href="https://wa.me/6281234567890?text=Halo%20Admin%20Aethereal%20Joki%2C%20saya%20tertarik%20dengan%20paket%20{{ urlencode($service->title) }}"
                            target="_blank"
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-950 border border-slate-800 hover:border-emerald-500/50 text-slate-300 hover:text-emerald-400 text-xs font-bold flex items-center justify-center gap-2 transition"
                        >
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.134 8.134 0 01-1.25-4.38c0-4.5 3.66-8.16 8.16-8.16 2.18 0 4.23.85 5.77 2.39 1.54 1.54 2.39 3.59 2.39 5.77 0 4.5-3.66 8.16-8.16 8.16z"/></svg>
                            Konsultasi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
