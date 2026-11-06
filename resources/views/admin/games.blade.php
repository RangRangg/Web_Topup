<x-layout title="Kelola Games — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        createModal: false,
        editModal: false,
        deleteModal: false,
        deleteAction: '',
        deleteGameName: '',
        editGame: {
            id: null,
            name: '',
            slug: '',
            category: 'moba',
            publisher: '',
            badge: '',
            icon_url: '',
            banner_url: '',
            description: '',
            order_priority: 0,
            is_active: true,
            actionUrl: ''
        }
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Katalog Games']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Top Bar & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Katalog Permainan & Platform</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola game yang tersedia untuk layanan Top Up dan Joki.</p>
            </div>

            <button
                type="button"
                @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-cyan-400 to-indigo-400 hover:from-cyan-300 hover:to-indigo-300 transition shadow-lg shadow-cyan-950/40"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Game Baru</span>
            </button>
        </div>

        {{-- Search & Category Filter --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.games') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari judul game atau publisher..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full sm:w-44">
                    <select
                        name="category"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ strtoupper($cat) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-40">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto">
                    Cari
                </x-button>
            </form>
        </div>

        {{-- Games Table / Grid --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($games->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Game</th>
                                <th class="pb-3 font-semibold">Kategori</th>
                                <th class="pb-3 font-semibold">Badge</th>
                                <th class="pb-3 font-semibold">Produk Top Up</th>
                                <th class="pb-3 font-semibold">Layanan Joki</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($games as $g)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            @if($g->icon_url)
                                                <img src="{{ $g->icon_url }}" alt="{{ $g->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 bg-slate-950 flex-shrink-0">
                                            @else
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-indigo-600 flex items-center justify-center text-slate-950 font-bold text-xs flex-shrink-0">
                                                    {{ strtoupper(substr($g->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold text-white text-sm">{{ $g->name }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $g->publisher ?? 'N/A' }} • <span class="font-mono text-cyan-400">/{{ $g->slug }}</span></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-800 text-slate-300 font-mono text-[10px] uppercase font-bold">
                                            {{ $g->category }}
                                        </span>
                                    </td>

                                    <td class="py-4">
                                        @if($g->badge)
                                            <span class="px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 text-[10px] font-extrabold uppercase">
                                                {{ $g->badge }}
                                            </span>
                                        @else
                                            <span class="text-slate-600">-</span>
                                        @endif
                                    </td>

                                    <td class="py-4 font-mono font-bold text-slate-300">
                                        {{ $g->game_products_count }} item
                                    </td>

                                    <td class="py-4 font-mono font-bold text-slate-300">
                                        {{ $g->joki_services_count }} paket
                                    </td>

                                    <td class="py-4">
                                        <form action="{{ route('admin.games.status', $g->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $g->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full {{ $g->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                                {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Edit Game Button --}}
                                            <button
                                                type="button"
                                                @click="
                                                    editGame = {
                                                        id: {{ $g->id }},
                                                        name: '{{ addslashes($g->name) }}',
                                                        slug: '{{ addslashes($g->slug) }}',
                                                        category: '{{ addslashes($g->category) }}',
                                                        publisher: '{{ addslashes($g->publisher ?? '') }}',
                                                        badge: '{{ addslashes($g->badge ?? '') }}',
                                                        icon_url: '{{ addslashes($g->icon_url ?? '') }}',
                                                        banner_url: '{{ addslashes($g->banner_url ?? '') }}',
                                                        description: '{{ addslashes($g->description ?? '') }}',
                                                        order_priority: {{ $g->order_priority ?? 0 }},
                                                        is_active: {{ $g->is_active ? 'true' : 'false' }},
                                                        actionUrl: '{{ route('admin.games.update', $g->id) }}'
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
                                                    deleteGameName = '{{ addslashes($g->name) }}';
                                                    deleteAction = '{{ route('admin.games.destroy', $g->id) }}';
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
                    {{ $games->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada data game ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Gunakan tombol "Tambah Game Baru" di atas untuk menambahkan game.</p>
                </div>
            @endif
        </div>

        {{-- Create Game Modal --}}
        <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Tambah Game Baru</h3>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.games.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Game <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Mobile Legends" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Slug (Opsional)</label>
                            <input type="text" name="slug" placeholder="mobile-legends" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                            <input type="text" name="category" required placeholder="moba / fps / battle_royale / rpg" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Publisher</label>
                            <input type="text" name="publisher" placeholder="Moonton / Riot Games" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Badge Tag</label>
                            <input type="text" name="badge" placeholder="HOT / POPULAR / NEW" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">URL Icon / Gambar Persegi</label>
                        <input type="url" name="icon_url" placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">URL Banner Lanskap</label>
                        <input type="url" name="banner_url" placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Deskripsi Ringkas</label>
                        <textarea name="description" rows="3" placeholder="Informasi singkat game..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-950 border-slate-800 text-cyan-500 focus:ring-0">
                            <span class="text-slate-300 font-medium">Status Aktif (Ditampilkan)</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-cyan-400 hover:bg-cyan-300 transition">Simpan Game</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Game Modal --}}
        <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Edit Game</h3>
                    <button type="button" @click="editModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="editGame.actionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Game <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" x-model="editGame.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Slug</label>
                            <input type="text" name="slug" x-model="editGame.slug" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                            <input type="text" name="category" x-model="editGame.category" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Publisher</label>
                            <input type="text" name="publisher" x-model="editGame.publisher" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Badge Tag</label>
                            <input type="text" name="badge" x-model="editGame.badge" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">URL Icon</label>
                        <input type="url" name="icon_url" x-model="editGame.icon_url" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">URL Banner</label>
                        <input type="url" name="banner_url" x-model="editGame.banner_url" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" x-model="editGame.description" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition">Update Game</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Game Confirmation Modal (Destructive Action) --}}
        <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <h3 class="text-base font-bold text-white text-center">Hapus Game Ini?</h3>
                <p class="text-xs text-slate-400 text-center mt-2">
                    Tindakan ini permanen. Game <strong class="text-rose-400" x-text="deleteGameName"></strong> beserta produk dan layanannya akan dihapus dari platform.
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <button type="button" @click="deleteModal = false" class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">Batal</button>
                    <form :action="deleteAction" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition shadow-lg shadow-rose-950/50">Hapus Permanen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
