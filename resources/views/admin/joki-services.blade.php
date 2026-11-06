<x-layout title="Kelola Layanan Joki — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        createModal: false,
        editModal: false,
        deleteModal: false,
        deleteAction: '',
        deleteServiceName: '',
        editService: {
            id: null,
            game_id: '',
            name: '',
            slug: '',
            service_type: 'rank',
            rank_from: '',
            rank_to: '',
            price: 0,
            original_price: '',
            unit: 'Paket',
            estimated_duration: '2-4 Jam',
            is_popular: false,
            is_active: true,
            description: '',
            features: '',
            actionUrl: ''
        }
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Layanan Joki']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Paket & Layanan Jasa Joki</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola paket push rank, joki per bintang, gendong/mabar, dan penugasan booster.</p>
            </div>

            <button
                type="button"
                @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-purple-400 to-indigo-400 hover:from-purple-300 hover:to-indigo-300 transition shadow-lg shadow-purple-950/40"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Layanan Joki</span>
            </button>
        </div>

        {{-- Search & Filters --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.joki-services') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari judul paket layanan joki atau target rank..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-purple-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full sm:w-48">
                    <select
                        name="game_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-purple-500 focus:outline-none"
                    >
                        <option value="">Semua Game</option>
                        @foreach($games as $gm)
                            <option value="{{ $gm->id }}" {{ request('game_id') == $gm->id ? 'selected' : '' }}>{{ $gm->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-40">
                    <select
                        name="service_type"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-purple-500 focus:outline-none"
                    >
                        <option value="">Semua Jenis</option>
                        <option value="rank" {{ request('service_type') === 'rank' ? 'selected' : '' }}>Push Rank</option>
                        <option value="winrate" {{ request('service_type') === 'winrate' ? 'selected' : '' }}>Winrate</option>
                        <option value="placement" {{ request('service_type') === 'placement' ? 'selected' : '' }}>Placement</option>
                        <option value="mabar" {{ request('service_type') === 'mabar' ? 'selected' : '' }}>Mabar/Party</option>
                    </select>
                </div>

                <div class="w-full sm:w-36">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-purple-500 focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <x-button type="submit" variant="purple" size="sm" class="w-full sm:w-auto">
                    Filter
                </x-button>
            </form>
        </div>

        {{-- Services Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($services->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Layanan & Game</th>
                                <th class="pb-3 font-semibold">Target / Lingkup</th>
                                <th class="pb-3 font-semibold">Estimasi Waktu</th>
                                <th class="pb-3 font-semibold">Harga Mulai</th>
                                <th class="pb-3 font-semibold">Label</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($services as $s)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-bold text-white">
                                        <div class="text-sm font-bold text-white">{{ $s->name }}</div>
                                        <div class="text-[11px] text-purple-400 font-medium">
                                            {{ $s->game->name ?? 'Semua Game' }} • <span class="uppercase text-slate-500 text-[10px] font-mono">{{ $s->service_type }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 text-slate-300 font-medium">
                                        {{ $s->target }}
                                    </td>

                                    <td class="py-4 text-slate-300 font-mono">
                                        <span class="inline-flex items-center gap-1 text-slate-300">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $s->estimated_duration }}
                                        </span>
                                    </td>

                                    <td class="py-4">
                                        <div class="font-mono font-bold text-emerald-400 text-sm">
                                            Rp {{ number_format($s->price, 0, ',', '.') }}
                                        </div>
                                        @if($s->original_price)
                                            <div class="font-mono text-[10px] text-slate-500 line-through">
                                                Rp {{ number_format($s->original_price, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        @if($s->is_popular)
                                            <span class="px-2 py-0.5 rounded-full bg-purple-500/15 text-purple-400 border border-purple-500/30 text-[10px] font-extrabold uppercase">
                                                POPULER
                                            </span>
                                        @else
                                            <span class="text-slate-600">-</span>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        <form action="{{ route('admin.joki-services.status', $s->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $s->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full {{ $s->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                                {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Edit Button --}}
                                            <button
                                                type="button"
                                                @click="
                                                    editService = {
                                                        id: {{ $s->id }},
                                                        game_id: '{{ $s->game_id }}',
                                                        name: '{{ addslashes($s->name) }}',
                                                        slug: '{{ addslashes($s->slug) }}',
                                                        service_type: '{{ addslashes($s->service_type) }}',
                                                        rank_from: '{{ addslashes($s->rank_from ?? '') }}',
                                                        rank_to: '{{ addslashes($s->rank_to ?? '') }}',
                                                        price: {{ $s->price }},
                                                        original_price: '{{ $s->original_price ?? '' }}',
                                                        unit: '{{ addslashes($s->unit ?? 'Paket') }}',
                                                        estimated_duration: '{{ addslashes($s->estimated_duration ?? '2-4 Jam') }}',
                                                        is_popular: {{ $s->is_popular ? 'true' : 'false' }},
                                                        is_active: {{ $s->is_active ? 'true' : 'false' }},
                                                        description: '{{ addslashes($s->description ?? '') }}',
                                                        features: '{{ addslashes(is_array($s->features) ? implode('\n', $s->features) : '') }}',
                                                        actionUrl: '{{ route('admin.joki-services.update', $s->id) }}'
                                                    };
                                                    editModal = true;
                                                "
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition"
                                            >
                                                Edit
                                            </button>

                                            {{-- Delete Button (Confirmation Modal) --}}
                                            <button
                                                type="button"
                                                @click="
                                                    deleteServiceName = '{{ addslashes($s->name) }}';
                                                    deleteAction = '{{ route('admin.joki-services.destroy', $s->id) }}';
                                                    deleteModal = true;
                                                "
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-600 border border-rose-500/30 transition"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $services->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada layanan joki ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Gunakan tombol "Tambah Layanan Joki" untuk menambahkan paket baru.</p>
                </div>
            @endif
        </div>

        {{-- Create Service Modal --}}
        <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Tambah Layanan Joki</h3>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.joki-services.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Game Terkait <span class="text-rose-400">*</span></label>
                        <select name="game_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                            <option value="">Pilih Game</option>
                            @foreach($games as $gm)
                                <option value="{{ $gm->id }}">{{ $gm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Paket Layanan <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Push Rank Epic ke Legend" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Jenis Layanan</label>
                            <select name="service_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                                <option value="rank">Push Rank</option>
                                <option value="winrate">Winrate</option>
                                <option value="placement">Placement</option>
                                <option value="mabar">Mabar / Party</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Rank Awal</label>
                            <input type="text" name="rank_from" placeholder="Epic V" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Rank Target</label>
                            <input type="text" name="rank_to" placeholder="Legend I" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Layanan (Rp) <span class="text-rose-400">*</span></label>
                            <input type="number" name="price" required min="0" step="1000" placeholder="150000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Coret (Rp)</label>
                            <input type="number" name="original_price" min="0" step="1000" placeholder="180000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Estimasi Pengerjaan</label>
                            <input type="text" name="estimated_duration" value="2-4 Jam" placeholder="Contoh: 2-4 Jam / 1 Hari" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Satuan</label>
                            <input type="text" name="unit" value="Paket" placeholder="Paket / Bintang / Jam" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Fitur / Keunggulan Paket (1 per baris)</label>
                        <textarea name="features" rows="3" placeholder="Garansi Anti Banned 100%&#10;Booster Mythic Pro&#10;Bonus Winrate 80%+" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Deskripsi Tambahan</label>
                        <textarea name="description" rows="2" placeholder="Ketentuan khusus atau catatan order..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_popular" value="1" class="rounded bg-slate-950 border-slate-800 text-purple-500 focus:ring-0">
                            <span class="text-slate-300 font-medium">Tandai Sebagai Paket Populer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-950 border-slate-800 text-purple-500 focus:ring-0">
                            <span class="text-slate-300 font-medium">Status Aktif</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-purple-400 hover:bg-purple-300 transition">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Service Modal --}}
        <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Edit Layanan Joki</h3>
                    <button type="button" @click="editModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="editService.actionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Game Terkait <span class="text-rose-400">*</span></label>
                        <select name="game_id" x-model="editService.game_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                            @foreach($games as $gm)
                                <option value="{{ $gm->id }}">{{ $gm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Paket Layanan <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" x-model="editService.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Jenis</label>
                            <select name="service_type" x-model="editService.service_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                                <option value="rank">Push Rank</option>
                                <option value="winrate">Winrate</option>
                                <option value="placement">Placement</option>
                                <option value="mabar">Mabar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Rank Awal</label>
                            <input type="text" name="rank_from" x-model="editService.rank_from" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Rank Target</label>
                            <input type="text" name="rank_to" x-model="editService.rank_to" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga (Rp) <span class="text-rose-400">*</span></label>
                            <input type="number" name="price" x-model="editService.price" required min="0" step="1000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Coret (Rp)</label>
                            <input type="number" name="original_price" x-model="editService.original_price" min="0" step="1000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Estimasi Pengerjaan</label>
                            <input type="text" name="estimated_duration" x-model="editService.estimated_duration" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Satuan</label>
                            <input type="text" name="unit" x-model="editService.unit" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Fitur (1 per baris)</label>
                        <textarea name="features" rows="3" x-model="editService.features" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-purple-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_popular" value="1" x-model="editService.is_popular" class="rounded bg-slate-950 border-slate-800 text-purple-500 focus:ring-0">
                            <span class="text-slate-300 font-medium">Tandai Populer</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-purple-600 hover:bg-purple-500 transition">Update Layanan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Service Confirmation Modal --}}
        <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <h3 class="text-base font-bold text-white text-center">Hapus Layanan Joki Ini?</h3>
                <p class="text-xs text-slate-400 text-center mt-2">
                    Layanan <strong class="text-rose-400" x-text="deleteServiceName"></strong> akan dihapus permanen dari marketplace joki.
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <button type="button" @click="deleteModal = false" class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">Batal</button>
                    <form :action="deleteAction" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition shadow-lg shadow-rose-950/50">Hapus Layanan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
