<x-layout title="Audit Trail & Histori — Admin Aethereal Joki">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        detailModal: false,
        modalTitle: '',
        modalAction: '',
        modalUser: '',
        modalIp: '',
        modalDetails: '',
        modalOldValues: null,
        modalNewValues: null
    }">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Audit Trail Sistem']]" />

        {{-- Admin Navigation Bar --}}
        @include('admin.partials.nav')

        {{-- Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Audit Trail & Log Aktivitas Sistem</h2>
                <p class="text-xs text-slate-400 mt-0.5">Catatan tidak dapat dihapus untuk setiap tindakan administratif, otorisasi, dan mutasi data.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Immutable Logging Enabled
                </span>
            </div>
        </div>

        {{-- Search & Action Filter --}}
        <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <form action="{{ route('admin.audit-logs') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari deskripsi, nama user, action, atau alamat IP..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="w-full sm:w-56">
                    <select
                        name="action"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl bg-slate-950 border border-slate-800 text-slate-200 px-3.5 py-2.5 text-xs focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="">Semua Action</option>
                        @foreach($distinctActions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>

                <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto">
                    Cari
                </x-button>
            </form>
        </div>

        {{-- Audit Logs Table --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 overflow-hidden">
            @if($logs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3 font-semibold">Waktu Log</th>
                                <th class="pb-3 font-semibold">User Pelaksana</th>
                                <th class="pb-3 font-semibold">Action Key</th>
                                <th class="pb-3 font-semibold">Deskripsi Aktivitas</th>
                                <th class="pb-3 font-semibold">IP Address</th>
                                <th class="pb-3 font-semibold text-right">Detail Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-slate-200">
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-950/40 transition">
                                    <td class="py-4 font-mono text-slate-400 text-[11px] whitespace-nowrap">
                                        <div class="font-bold text-slate-200">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>

                                    <td class="py-4">
                                        @if($log->user)
                                            <div class="font-bold text-white flex items-center gap-1.5">
                                                <span>{{ $log->user->name }}</span>
                                                <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-mono {{ $log->user->role === 'admin' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-800 text-slate-300' }}">
                                                    {{ $log->user->role }}
                                                </span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->user->email }}</div>
                                        @else
                                            <span class="text-slate-500 italic">System / Guest</span>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-950 border border-slate-800 text-cyan-400 font-mono text-[10px] font-bold">
                                            {{ $log->action }}
                                        </span>
                                    </td>

                                    <td class="py-4 max-w-xs text-slate-300">
                                        <p class="truncate">{{ $log->details ?? '-' }}</p>
                                    </td>

                                    <td class="py-4 font-mono text-[11px] text-slate-400">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </td>

                                    <td class="py-4 text-right">
                                        @if($log->old_values || $log->new_values)
                                            <button
                                                type="button"
                                                @click="
                                                    modalTitle = 'Detail Mutasi Data Log #{{ $log->id }}';
                                                    modalAction = '{{ $log->action }}';
                                                    modalUser = '{{ $log->user?->name ?? 'System' }}';
                                                    modalIp = '{{ $log->ip_address }}';
                                                    modalDetails = '{{ addslashes($log->details ?? '') }}';
                                                    modalOldValues = {{ json_encode($log->old_values) }};
                                                    modalNewValues = {{ json_encode($log->new_values) }};
                                                    detailModal = true;
                                                "
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold text-cyan-400 hover:text-white bg-cyan-500/10 hover:bg-cyan-600 border border-cyan-500/30 transition"
                                            >
                                                Inspeksi
                                            </button>
                                        @else
                                            <span class="text-slate-600 text-[10px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-sm font-bold text-white">Tidak ada log aktivitas ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">Sistem belum merekam aktivitas dengan kriteria pencarian ini.</p>
                </div>
            @endif
        </div>

        {{-- Log Detail Inspection Modal --}}
        <div x-show="detailModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="detailModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-white font-mono" x-text="modalTitle"></h3>
                    <button type="button" @click="detailModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800">
                        <div>
                            <span class="text-slate-400 block">User Pelaksana:</span>
                            <span class="font-bold text-white" x-text="modalUser"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">IP Address:</span>
                            <span class="font-mono text-cyan-400" x-text="modalIp"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 block">Keterangan:</span>
                            <span class="text-slate-200" x-text="modalDetails"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-bold text-amber-400 mb-1 flex items-center gap-1">
                                <span>Nilai Sebelum (Old Values)</span>
                            </h4>
                            <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-[11px] font-mono text-amber-300 overflow-x-auto max-h-60" x-text="JSON.stringify(modalOldValues, null, 2) || 'None'"></pre>
                        </div>

                        <div>
                            <h4 class="font-bold text-emerald-400 mb-1 flex items-center gap-1">
                                <span>Nilai Sesudah (New Values)</span>
                            </h4>
                            <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-[11px] font-mono text-emerald-300 overflow-x-auto max-h-60" x-text="JSON.stringify(modalNewValues, null, 2) || 'None'"></pre>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-slate-800">
                        <button type="button" @click="detailModal = false" class="px-5 py-2 rounded-xl font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
