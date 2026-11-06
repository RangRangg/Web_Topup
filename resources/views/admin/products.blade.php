<x-layout title="Kelola Produk Top Up — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        createModal: false,
        editModal: false,
        deleteModal: false,
        deleteAction: '',
        deleteProductName: '',
        editProduct: {
            id: null,
            game_id: '',
            name: '',
            slug: '',
            code: '',
            category: 'diamond',
            price: 0,
            original_price: '',
            stock: 999,
            is_active: true,
            description: '',
            actionUrl: ''
        }
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Produk Top Up']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Produk & Nominal Top Up Game</h2>
                <p class="text-xs text-slate-400 mt-0.5">Atur diamond, point, UC, voucher, harga, dan ketersediaan stok.</p>
            </div>

            <button
                type="button"
                @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-cyan-400 to-indigo-400 hover:from-cyan-300 hover:to-indigo-300 transition shadow-lg shadow-cyan-950/40"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Produk Baru</span>
            </button>
        </div>

        {{-- Search & Filters --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.products') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama produk atau kode produk (misal: 86 Diamonds)..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full sm:w-52">
                    <select
                        name="game_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Game</option>
                        @foreach($games as $gm)
                            <option value="{{ $gm->id }}" {{ request('game_id') == $gm->id ? 'selected' : '' }}>{{ $gm->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-36">
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
                    Filter
                </x-button>
            </form>
        </div>

        {{-- Products Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Produk</th>
                                <th class="pb-3 font-semibold">Game</th>
                                <th class="pb-3 font-semibold">Kode</th>
                                <th class="pb-3 font-semibold">Harga Jual</th>
                                <th class="pb-3 font-semibold">Stok</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($products as $p)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-bold text-white">
                                        <div class="text-sm font-bold text-white">{{ $p->name }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase font-mono">{{ $p->category }}</div>
                                    </td>

                                    <td class="py-4">
                                        <span class="px-2.5 py-1 rounded-xl bg-slate-950 border border-slate-800 text-cyan-400 font-semibold text-[11px]">
                                            {{ $p->game->name ?? 'Unassigned' }}
                                        </span>
                                    </td>

                                    <td class="py-4 font-mono text-slate-300">
                                        {{ $p->code ?? '-' }}
                                    </td>

                                    <td class="py-4">
                                        <div class="font-mono font-bold text-emerald-400 text-sm">
                                            Rp {{ number_format($p->price, 0, ',', '.') }}
                                        </div>
                                        @if($p->original_price)
                                            <div class="font-mono text-[10px] text-slate-500 line-through">
                                                Rp {{ number_format($p->original_price, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-4 font-mono text-slate-300">
                                        {{ number_format($p->stock) }} unit
                                    </td>

                                    <td class="py-4">
                                        <form action="{{ route('admin.products.status', $p->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $p->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full {{ $p->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                                {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Edit Button --}}
                                            <button
                                                type="button"
                                                @click="
                                                    editProduct = {
                                                        id: {{ $p->id }},
                                                        game_id: '{{ $p->game_id }}',
                                                        name: '{{ addslashes($p->name) }}',
                                                        slug: '{{ addslashes($p->slug) }}',
                                                        code: '{{ addslashes($p->code ?? '') }}',
                                                        category: '{{ addslashes($p->category) }}',
                                                        price: {{ $p->price }},
                                                        original_price: '{{ $p->original_price ?? '' }}',
                                                        stock: {{ $p->stock }},
                                                        is_active: {{ $p->is_active ? 'true' : 'false' }},
                                                        description: '{{ addslashes($p->description ?? '') }}',
                                                        actionUrl: '{{ route('admin.products.update', $p->id) }}'
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
                                                    deleteProductName = '{{ addslashes($p->name) }}';
                                                    deleteAction = '{{ route('admin.products.destroy', $p->id) }}';
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
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada produk ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Coba gunakan filter lain atau tambahkan produk baru.</p>
                </div>
            @endif
        </div>

        {{-- Create Product Modal --}}
        <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Tambah Produk Top Up</h3>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Game Terkait <span class="text-rose-400">*</span></label>
                        <select name="game_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            <option value="">Pilih Game</option>
                            @foreach($games as $gm)
                                <option value="{{ $gm->id }}">{{ $gm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Produk <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: 86 Diamonds / 1000 VP" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kode SKU</label>
                            <input type="text" name="code" placeholder="ML-86" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                            <input type="text" name="category" required value="diamond" placeholder="diamond / points / uc" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Jual (Rp) <span class="text-rose-400">*</span></label>
                            <input type="number" name="price" required min="0" step="100" placeholder="20000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Coret (Rp)</label>
                            <input type="number" name="original_price" min="0" step="100" placeholder="25000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Stok Awal</label>
                        <input type="number" name="stock" value="999" min="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Deskripsi / Bonus Info</label>
                        <textarea name="description" rows="2" placeholder="Termasuk bonus 8 diamonds..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-950 border-slate-800 text-cyan-500 focus:ring-0">
                            <span class="text-slate-300 font-medium">Langsung Aktifkan Produk</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-cyan-400 hover:bg-cyan-300 transition">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Product Modal --}}
        <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Edit Produk</h3>
                    <button type="button" @click="editModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="editProduct.actionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Game Terkait <span class="text-rose-400">*</span></label>
                        <select name="game_id" x-model="editProduct.game_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                            @foreach($games as $gm)
                                <option value="{{ $gm->id }}">{{ $gm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Produk <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" x-model="editProduct.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kode SKU</label>
                            <input type="text" name="code" x-model="editProduct.code" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                            <input type="text" name="category" x-model="editProduct.category" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Jual (Rp) <span class="text-rose-400">*</span></label>
                            <input type="number" name="price" x-model="editProduct.price" required min="0" step="100" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Harga Coret (Rp)</label>
                            <input type="number" name="original_price" x-model="editProduct.original_price" min="0" step="100" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Stok Unit</label>
                        <input type="number" name="stock" x-model="editProduct.stock" min="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" x-model="editProduct.description" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-cyan-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition">Update Produk</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Product Confirmation Modal --}}
        <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <h3 class="text-base font-bold text-white text-center">Hapus Produk Ini?</h3>
                <p class="text-xs text-slate-400 text-center mt-2">
                    Tindakan ini tidak dapat dibatalkan. Produk <strong class="text-rose-400" x-text="deleteProductName"></strong> akan dihapus dari etalase.
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <button type="button" @click="deleteModal = false" class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">Batal</button>
                    <form :action="deleteAction" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition shadow-lg shadow-rose-950/50">Hapus Produk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
