<x-layout title="Kelola Users — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ 
        editUserModal: false,
        editUser: {
            id: null,
            name: '',
            email: '',
            role: 'customer',
            is_active: true,
            is_self: false,
            actionUrl: ''
        }
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Manajemen Pengguna']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Role & Status Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5 mb-6">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Pengguna</span>
                <span class="text-xl font-black text-white mt-1 block">{{ $roleCounts['total'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-sky-400 block">Customer</span>
                <span class="text-xl font-black text-sky-400 mt-1 block">{{ $roleCounts['customer'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400 block">Pro Booster</span>
                <span class="text-xl font-black text-purple-400 mt-1 block">{{ $roleCounts['joki'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block">Administrator</span>
                <span class="text-xl font-black text-rose-400 mt-1 block">{{ $roleCounts['admin'] }}</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">Nonaktif</span>
                <span class="text-xl font-black text-amber-400 mt-1 block">{{ $roleCounts['inactive'] }}</span>
            </div>
        </div>

        {{-- Search and Filter Form --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari berdasarkan nama, email, atau no WhatsApp..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full md:w-44">
                    <select
                        name="role"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Role</option>
                        <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="joki" {{ request('role') === 'joki' ? 'selected' : '' }}>Pro Booster</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div class="w-full md:w-44">
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

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <x-button type="submit" variant="primary" size="sm" class="flex-1 md:flex-initial">
                        Filter
                    </x-button>
                    @if(request()->hasAny(['search', 'role', 'status']))
                        <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">User</th>
                                <th class="pb-3 font-semibold">Kontak</th>
                                <th class="pb-3 font-semibold">Role</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold">Total Order</th>
                                <th class="pb-3 font-semibold">Terdaftar</th>
                                <th class="pb-3 font-semibold text-right">Aksi & Kontrol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($users as $u)
                                <tr class="hover:bg-slate-950/40 transition">
                                    {{-- User info --}}
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl {{ $u->role === 'admin' ? 'bg-gradient-to-tr from-rose-500 to-amber-500' : ($u->role === 'joki' ? 'bg-gradient-to-tr from-purple-500 to-indigo-500' : 'bg-gradient-to-tr from-cyan-500 to-blue-500') }} flex items-center justify-center text-slate-950 font-bold text-xs shadow-md">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white flex items-center gap-1.5">
                                                    {{ $u->name }}
                                                    @if($u->id === auth()->id())
                                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-rose-500/20 text-rose-300 font-mono">You</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-mono">ID: #{{ $u->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact --}}
                                    <td class="py-4">
                                        <div class="text-slate-200 font-medium">{{ $u->email }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $u->phone ?? '-' }}</div>
                                    </td>

                                    {{-- Role --}}
                                    <td class="py-4">
                                        @if($u->role === 'admin')
                                            <x-badge variant="danger" size="sm">Administrator</x-badge>
                                        @elseif($u->role === 'joki')
                                            <x-badge variant="purple" size="sm">Pro Booster</x-badge>
                                        @else
                                            <x-badge variant="primary" size="sm">Customer</x-badge>
                                        @endif
                                    </td>

                                    {{-- Active Status --}}
                                    <td class="py-4">
                                        @if($u->is_active)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Total Orders --}}
                                    <td class="py-4 font-mono font-bold text-slate-300">
                                        {{ $u->orders_count }}
                                    </td>

                                    {{-- Registered at --}}
                                    <td class="py-4 text-slate-400 text-[11px]">
                                        {{ $u->created_at->format('d M Y') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end">
                                            <button
                                                type="button"
                                                @click="
                                                    editUser = {
                                                        id: {{ $u->id }},
                                                        name: '{{ addslashes($u->name) }}',
                                                        email: '{{ addslashes($u->email) }}',
                                                        role: '{{ $u->role }}',
                                                        is_active: {{ $u->is_active ? 'true' : 'false' }},
                                                        is_self: {{ $u->id === auth()->id() ? 'true' : 'false' }},
                                                        actionUrl: '{{ route('admin.users.update', $u->id) }}'
                                                    };
                                                    editUserModal = true;
                                                "
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700/70 hover:border-slate-600 transition shadow-sm"
                                                title="Edit Data Pengguna"
                                            >
                                                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span>Edit</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-white">Tidak ada pengguna ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian atau filter yang berbeda.</p>
                </div>
            @endif
        </div>

        {{-- Unified Edit User Modal --}}
        <div
            x-show="editUserModal"
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center"
            style="display: none;"
            x-cloak
        >
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editUserModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-5">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>Edit Pengguna</span>
                            <span class="text-xs font-mono font-medium text-slate-400" x-text="'#' + editUser.id"></span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="editUser.email"></p>
                    </div>
                    <button type="button" @click="editUserModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="editUser.actionUrl" method="POST" class="space-y-5 text-xs">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="role" :value="editUser.role">
                    <input type="hidden" name="is_active" :value="editUser.is_active ? 1 : 0">

                    {{-- 1. Nama Lengkap --}}
                    <div>
                        <label for="edit_user_name" class="block font-semibold text-slate-200 mb-1.5">
                            Nama Lengkap <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit_user_name"
                            name="name"
                            x-model="editUser.name"
                            required
                            maxlength="100"
                            placeholder="Masukkan nama pengguna"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none transition"
                        >
                    </div>

                    {{-- 2. Role Pengguna --}}
                    <div>
                        <label class="block font-semibold text-slate-200 mb-1.5">
                            Role & Hak Akses <span class="text-rose-400">*</span>
                        </label>
                        <div class="space-y-2.5">
                            {{-- Customer --}}
                            <div 
                                @click="if (!editUser.is_self) editUser.role = 'customer'"
                                class="flex items-start gap-3 p-3 rounded-xl bg-slate-950 border transition"
                                :class="[
                                    editUser.role === 'customer' ? 'border-cyan-500/80 bg-cyan-950/20 ring-1 ring-cyan-500/30' : 'border-slate-800 hover:border-slate-700',
                                    editUser.is_self ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                <div class="w-4 h-4 mt-0.5 rounded-full border flex items-center justify-center transition"
                                    :class="editUser.role === 'customer' ? 'border-cyan-400 bg-cyan-500' : 'border-slate-700 bg-slate-900'">
                                    <div x-show="editUser.role === 'customer'" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white">Customer</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 font-semibold border border-cyan-500/20">Pelanggan</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Dapat melakukan pemesanan Top Up game dan order jasa joki.</p>
                                </div>
                            </div>

                            {{-- Pro Booster (Joki) --}}
                            <div 
                                @click="if (!editUser.is_self) editUser.role = 'joki'"
                                class="flex items-start gap-3 p-3 rounded-xl bg-slate-950 border transition"
                                :class="[
                                    editUser.role === 'joki' ? 'border-purple-500/80 bg-purple-950/20 ring-1 ring-purple-500/30' : 'border-slate-800 hover:border-slate-700',
                                    editUser.is_self ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                <div class="w-4 h-4 mt-0.5 rounded-full border flex items-center justify-center transition"
                                    :class="editUser.role === 'joki' ? 'border-purple-400 bg-purple-500' : 'border-slate-700 bg-slate-900'">
                                    <div x-show="editUser.role === 'joki'" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-purple-300">Pro Booster (Joki)</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 font-semibold border border-purple-500/20">Booster</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Akses ke Booster Workstation untuk mengambil & mengerjakan pesanan joki.</p>
                                </div>
                            </div>

                            {{-- Administrator --}}
                            <div 
                                @click="editUser.role = 'admin'"
                                class="flex items-start gap-3 p-3 rounded-xl bg-slate-950 border transition cursor-pointer"
                                :class="editUser.role === 'admin' ? 'border-rose-500/80 bg-rose-950/20 ring-1 ring-rose-500/30' : 'border-slate-800 hover:border-slate-700'"
                            >
                                <div class="w-4 h-4 mt-0.5 rounded-full border flex items-center justify-center transition"
                                    :class="editUser.role === 'admin' ? 'border-rose-400 bg-rose-500' : 'border-slate-700 bg-slate-900'">
                                    <div x-show="editUser.role === 'admin'" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-rose-300">Administrator</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 font-semibold border border-rose-500/20">Akses Penuh</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Akses penuh ke seluruh menu Admin Command Center dan konfigurasi sistem.</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="editUser.is_self" class="mt-2 text-[11px] text-amber-400/90 flex items-center gap-1.5 bg-amber-950/20 p-2.5 rounded-xl border border-amber-500/20">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Role akun admin Anda sendiri terkunci demi keamanan sistem.</span>
                        </div>
                    </div>

                    {{-- 3. Status Akun (Aktif / Nonaktifkan Satuan) --}}
                    <div class="pt-2 border-t border-slate-800/80">
                        <label class="block font-semibold text-slate-200 mb-1.5">
                            Status Akun Pengguna
                        </label>

                        {{-- When NOT self --}}
                        <div x-show="!editUser.is_self">
                            <div class="grid grid-cols-2 gap-2.5">
                                {{-- Option Aktif --}}
                                <div 
                                    @click="editUser.is_active = true"
                                    class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-950 border transition cursor-pointer"
                                    :class="editUser.is_active ? 'border-emerald-500/80 bg-emerald-950/20 ring-1 ring-emerald-500/30' : 'border-slate-800 hover:border-slate-700'"
                                >
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition"
                                        :class="editUser.is_active ? 'border-emerald-400 bg-emerald-500' : 'border-slate-700 bg-slate-900'">
                                        <div x-show="editUser.is_active" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span>Aktif</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Akun dapat login normal</div>
                                    </div>
                                </div>

                                {{-- Option Nonaktif --}}
                                <div 
                                    @click="editUser.is_active = false"
                                    class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-950 border transition cursor-pointer"
                                    :class="!editUser.is_active ? 'border-rose-500/80 bg-rose-950/20 ring-1 ring-rose-500/30' : 'border-slate-800 hover:border-slate-700'"
                                >
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition"
                                        :class="!editUser.is_active ? 'border-rose-400 bg-rose-500' : 'border-slate-700 bg-slate-900'">
                                        <div x-show="!editUser.is_active" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-rose-400 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                            <span>Nonaktif</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Blokir akses login akun</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Warning text when nonaktif is selected --}}
                            <div x-show="!editUser.is_active" class="text-[11px] text-rose-300/90 mt-2.5 flex items-center gap-2 bg-rose-950/25 p-2.5 rounded-xl border border-rose-500/20">
                                <svg class="w-4 h-4 flex-shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Akun ini akan dinonaktifkan dan tidak dapat login ke sistem hingga diaktifkan kembali.</span>
                            </div>
                        </div>

                        {{-- When IS self --}}
                        <div x-show="editUser.is_self" class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Aktif
                                </span>
                                <span class="text-[11px] text-slate-300 font-medium">Akun Anda saat ini</span>
                            </div>
                            <span class="text-[10px] text-slate-500 italic">Tidak dapat dinonaktifkan</span>
                        </div>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button 
                            type="button" 
                            @click="editUserModal = false" 
                            class="px-4 py-2.5 rounded-xl font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-950 bg-gradient-to-r from-cyan-400 to-indigo-400 hover:from-cyan-300 hover:to-indigo-300 transition shadow-lg shadow-cyan-950/40"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
