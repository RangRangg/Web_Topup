<x-layout title="Katalog Jasa Joki Game & Rank Booster">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Header & Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Jasa Joki', 'url' => route('joki')]]" />

        <div class="mt-4 mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <x-badge variant="purple" size="sm" class="mb-2">MARKETPLACE JOKI RESMI</x-badge>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Katalog Jasa Joki & Push Rank</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Pilih game favorit, tentukan target rank, dan nikmati pengerjaan kilat oleh pro booster terverifikasi.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-600 dark:text-slate-400">Total Layanan: <strong class="text-slate-900 dark:text-white">{{ $packages->total() }}</strong></span>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="mb-8 p-5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <form action="{{ route('joki') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                {{-- Game filter --}}
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Pilih Game:</label>
                    <select
                        name="game"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 px-4 py-2.5 text-xs font-semibold focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Judul Game</option>
                        @foreach($games as $g)
                            <option value="{{ $g->slug }}" {{ request('game') === $g->slug ? 'selected' : '' }}>
                                {{ $g->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Category filter --}}
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Kategori Joki:</label>
                    <select
                        name="category"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 px-4 py-2.5 text-xs font-semibold focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Tipe Layanan</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->slug }}" {{ request('category') === $c->slug ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Keyword search --}}
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Cari Layanan / Rank:</label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Misal: Mythic, Radiant, Master..."
                            class="flex-1 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 px-4 py-2.5 text-xs focus:border-cyan-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500"
                        />
                        <x-button type="submit" variant="primary" size="sm">
                            Cari
                        </x-button>
                        @if(request()->hasAny(['game', 'category', 'search']))
                            <x-button as="a" :href="route('joki')" variant="ghost" size="sm" title="Reset filter">
                                Reset
                            </x-button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Packages Grid --}}
        @if($packages->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($packages as $pkg)
                    <div class="relative rounded-2xl bg-white dark:bg-[#131c2e] border border-slate-200 dark:border-slate-800 hover:border-sky-500/60 p-5 flex flex-col justify-between transition-colors group overflow-hidden shadow-sm">
                        <div>
                            {{-- Image Preview with Badges --}}
                            <div class="relative h-44 rounded-2xl overflow-hidden mb-5 border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-950">
                                <img
                                    src="{{ $pkg->image }}"
                                    alt="{{ $pkg->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                    loading="lazy"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>

                                {{-- Top Status & Badge --}}
                                <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-950/80 backdrop-blur-sm border border-slate-800 text-[10px] font-bold text-cyan-400">
                                        {{ $pkg->game->name ?? 'Game' }}
                                    </span>

                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-black flex items-center gap-1">
                                            ★ {{ number_format($pkg->rating, 1) }}
                                        </span>
                                        @if($pkg->is_popular)
                                            <span class="px-2 py-0.5 rounded-lg bg-purple-500 text-slate-950 text-[10px] font-black shadow-md">
                                                HOT
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Bottom Title on Image --}}
                                <div class="absolute bottom-3 left-3 right-3">
                                    <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">Status: {{ ucfirst($pkg->status) }}</span>
                                    <h3 class="text-sm font-black text-white truncate">{{ $pkg->title }}</h3>
                                </div>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                {{ $pkg->description }}
                            </p>

                            {{-- Target & Estimated Time Details --}}
                            <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-800/80 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Target Rank:
                                    </span>
                                    <span class="font-bold text-cyan-700 dark:text-cyan-300 font-mono">{{ $pkg->target }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-purple-500 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Estimasi Waktu:
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $pkg->estimated_time }}</span>
                                </div>
                            </div>

                            {{-- Features bullet points --}}
                            @if(is_array($pkg->features))
                                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-800/80 space-y-1.5">
                                    @foreach(array_slice($pkg->features, 0, 2) as $feat)
                                        <div class="flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-300 truncate">
                                            <svg class="w-3.5 h-3.5 text-emerald-500 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <span class="truncate">{{ $feat }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Footer Price and Actions --}}
                        <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Mulai Dari</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $pkg->formatted_starting_price }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-button as="a" :href="route('joki.show', $pkg->slug)" variant="secondary" size="sm">
                                    Detail
                                </x-button>
                                <x-button as="a" :href="route('joki.checkout', ['service' => $pkg->slug])" variant="primary" size="sm">
                                    Checkout
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Reusable Pagination Component --}}
            <div class="mt-10">
                <x-pagination :paginator="$packages" />
            </div>
        @else
            {{-- Reusable Empty State Component --}}
            <x-empty-state
                title="Layanan Joki Tidak Ditemukan"
                description="Tidak ada paket joki yang sesuai dengan filter atau kata kunci pencarian Anda. Coba ganti filter atau pilih judul game lain."
                :actionUrl="route('joki')"
                actionText="Reset Semua Filter"
            />
        @endif
    </div>
</x-layout>
