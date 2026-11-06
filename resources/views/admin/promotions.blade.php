<x-layout title="Kelola Voucher Promo — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        createModal: false,
        editModal: false,
        deleteModal: false,
        deleteAction: '',
        deletePromoCode: '',
        editPromo: {
            id: null,
            code: '',
            name: '',
            discount_type: 'percentage',
            discount_value: 10,
            min_spend: 0,
            max_discount: '',
            valid_from: '',
            valid_until: '',
            usage_limit: '',
            is_active: true,
            actionUrl: ''
        }
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Kode Promo & Diskon']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Top Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Kode Promo & Kupon Diskon</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola kupon diskon persentase dan potongan nominal untuk event marketplace.</p>
            </div>

            <button
                type="button"
                @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-amber-400 to-rose-400 hover:from-amber-300 hover:to-rose-300 transition shadow-lg shadow-amber-950/40"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Kupon Baru</span>
            </button>
        </div>

        {{-- Search & Filters --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.promotions') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode kupon (misal: AETHER10) atau nama promo..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none uppercase"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full sm:w-44">
                    <select
                        name="discount_type"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-amber-500 focus:outline-none"
                    >
                        <option value="">Semua Tipe Diskon</option>
                        <option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>Potongan Tetap (Rp)</option>
                    </select>
                </div>

                <div class="w-full sm:w-36">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-amber-500 focus:outline-none"
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

        {{-- Promotions Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($promotions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Kode & Nama Kupon</th>
                                <th class="pb-3 font-semibold">Nilai Diskon</th>
                                <th class="pb-3 font-semibold">Min. Belanja</th>
                                <th class="pb-3 font-semibold">Masa Berlaku</th>
                                <th class="pb-3 font-semibold">Penggunaan</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($promotions as $promo)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 font-mono font-black text-sm uppercase">
                                                {{ $promo->code }}
                                            </span>
                                            <div>
                                                <div class="font-bold text-white">{{ $promo->name }}</div>
                                                <div class="text-[10px] text-slate-400 uppercase font-mono">{{ $promo->discount_type }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4">
                                        <span class="font-mono font-bold text-emerald-400 text-sm">
                                            @if($promo->discount_type === 'percentage')
                                                {{ (float) $promo->discount_value }}%
                                                @if($promo->max_discount)
                                                    <span class="text-[10px] text-slate-400 block font-normal">Maks. Rp {{ number_format($promo->max_discount, 0, ',', '.') }}</span>
                                                @endif
                                            @else
                                                Rp {{ number_format($promo->discount_value, 0, ',', '.') }}
                                            @endif
                                        </span>
                                    </td>

                                    <td class="py-4 font-mono text-slate-300">
                                        Rp {{ number_format($promo->min_spend, 0, ',', '.') }}
                                    </td>

                                    <td class="py-4 text-slate-300">
                                        @if($promo->valid_until)
                                            <div class="font-mono text-xs {{ $promo->valid_until->isPast() ? 'text-rose-400 line-through' : 'text-slate-200' }}">
                                                s/d {{ $promo->valid_until->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 font-sans">
                                                {{ $promo->valid_until->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-emerald-400 font-medium">Selamanya</span>
                                        @endif
                                    </td>

                                    <td class="py-4 font-mono">
                                        <span class="font-bold text-white">{{ $promo->used_count }}</span>
                                        <span class="text-slate-500">/ {{ $promo->usage_limit ?? '∞' }} kali</span>
                                    </td>

                                    <td class="py-4">
                                        <form action="{{ route('admin.promotions.status', $promo->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $promo->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700' }}"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full {{ $promo->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                                {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Edit Button --}}
                                            <button
                                                type="button"
                                                @click="
                                                    editPromo = {
                                                        id: {{ $promo->id }},
                                                        code: '{{ addslashes($promo->code) }}',
                                                        name: '{{ addslashes($promo->name) }}',
                                                        discount_type: '{{ $promo->discount_type }}',
                                                        discount_value: {{ $promo->discount_value }},
                                                        min_spend: {{ $promo->min_spend }},
                                                        max_discount: '{{ $promo->max_discount ?? '' }}',
                                                        valid_from: '{{ $promo->valid_from ? $promo->valid_from->format('Y-m-d\TH:i') : '' }}',
                                                        valid_until: '{{ $promo->valid_until ? $promo->valid_until->format('Y-m-d\TH:i') : '' }}',
                                                        usage_limit: '{{ $promo->usage_limit ?? '' }}',
                                                        is_active: {{ $promo->is_active ? 'true' : 'false' }},
                                                        actionUrl: '{{ route('admin.promotions.update', $promo->id) }}'
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
                                                    deletePromoCode = '{{ addslashes($promo->code) }}';
                                                    deleteAction = '{{ route('admin.promotions.destroy', $promo->id) }}';
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
                    {{ $promotions->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada kode promo ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Buat kupon promo baru dengan menekan tombol di atas.</p>
                </div>
            @endif
        </div>

        {{-- Create Promotion Modal --}}
        <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Buat Kode Promo Baru</h3>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form action="{{ route('admin.promotions.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kode Kupon <span class="text-rose-400">*</span></label>
                            <input type="text" name="code" required placeholder="AETHER2026" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono uppercase focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nama Promo <span class="text-rose-400">*</span></label>
                            <input type="text" name="name" required placeholder="Promo Grand Launching" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Jenis Potongan</label>
                            <select name="discount_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Besar Diskon <span class="text-rose-400">*</span></label>
                            <input type="number" name="discount_value" required min="0.1" step="0.1" placeholder="10 (untuk %) atau 15000 (untuk Rp)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Minimal Belanja (Rp)</label>
                            <input type="number" name="min_spend" min="0" step="1000" value="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Maksimal Diskon (Rp)</label>
                            <input type="number" name="max_discount" min="0" step="1000" placeholder="50000 (Opsional)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Berlaku Mulai</label>
                            <input type="datetime-local" name="valid_from" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Berlaku Sampai</label>
                            <input type="datetime-local" name="valid_until" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Batas Penggunaan (Kuota Kupon)</label>
                        <input type="number" name="usage_limit" min="1" placeholder="Kosongkan untuk kuota tak terbatas" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0">
                            <span class="text-slate-300 font-medium">Langsung Aktifkan Kupon</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 transition">Simpan Kupon</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Promotion Modal --}}
        <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white">Edit Kode Promo</h3>
                    <button type="button" @click="editModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form :action="editPromo.actionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kode Kupon <span class="text-rose-400">*</span></label>
                            <input type="text" name="code" x-model="editPromo.code" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono uppercase focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nama Promo <span class="text-rose-400">*</span></label>
                            <input type="text" name="name" x-model="editPromo.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Jenis Potongan</label>
                            <select name="discount_type" x-model="editPromo.discount_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Besar Diskon <span class="text-rose-400">*</span></label>
                            <input type="number" name="discount_value" x-model="editPromo.discount_value" required min="0.1" step="0.1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Minimal Belanja (Rp)</label>
                            <input type="number" name="min_spend" x-model="editPromo.min_spend" min="0" step="1000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Maksimal Diskon (Rp)</label>
                            <input type="number" name="max_discount" x-model="editPromo.max_discount" min="0" step="1000" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Berlaku Mulai</label>
                            <input type="datetime-local" name="valid_from" x-model="editPromo.valid_from" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Berlaku Sampai</label>
                            <input type="datetime-local" name="valid_until" x-model="editPromo.valid_until" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Batas Penggunaan (Kuota Kupon)</label>
                        <input type="number" name="usage_limit" x-model="editPromo.usage_limit" min="1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white bg-slate-800 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-amber-600 hover:bg-amber-500 transition">Update Kupon</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Promo Confirmation Modal --}}
        <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <h3 class="text-base font-bold text-white text-center">Hapus Kupon Promo Ini?</h3>
                <p class="text-xs text-slate-400 text-center mt-2">
                    Kupon <strong class="text-amber-400" x-text="deletePromoCode"></strong> akan dihapus permanen dan tidak dapat digunakan lagi oleh customer saat checkout.
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <button type="button" @click="deleteModal = false" class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">Batal</button>
                    <form :action="deleteAction" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition shadow-lg shadow-rose-950/50">Hapus Kupon</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
