<x-layout title="Notifikasi Akun — Customer Dashboard">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Notifikasi'],
        ]" />

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mt-4">
                <x-alert type="success" :dismissible="true">
                    {{ session('success') }}
                </x-alert>
            </div>
        @endif

        @if(session('error'))
            <div class="mt-4">
                <x-alert type="error" :dismissible="true">
                    {{ session('error') }}
                </x-alert>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="mt-6 mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>Notifikasi Akun</span>
                    @if($unreadNotificationsCount > 0)
                        <x-badge variant="primary" size="sm" :dot="true">
                            {{ $unreadNotificationsCount }} Baru
                        </x-badge>
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Pemberitahuan sistem terkait status pesanan, verifikasi pembayaran, dan promo khusus.
                </p>
            </div>

            @if($unreadNotificationsCount > 0)
                <form method="POST" action="{{ route('dashboard.notifications.mark-all-read') }}">
                    @csrf
                    <x-button type="submit" variant="outline" size="sm">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tandai Semua Dibaca
                    </x-button>
                </form>
            @endif
        </div>

        {{-- Sub Navigation Tabs --}}
        @include('dashboard.partials.nav')

        {{-- Notifications List Card --}}
        <div class="p-5 sm:p-8 rounded-2xl sm:rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4">
            @if($notifications->count() > 0)
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                        <div class="p-4 sm:p-5 rounded-2xl border transition-all {{ $notification->is_read ? 'bg-slate-950/40 border-slate-800/80 text-slate-300' : 'bg-slate-950/90 border-cyan-500/30 text-white shadow-[0_0_20px_-5px_rgba(6,182,212,0.1)]' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    {{-- Icon Indicator --}}
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $notification->is_read ? 'bg-slate-900 text-slate-400' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' }}">
                                        @if($notification->type === 'order')
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                        @elseif($notification->type === 'payment')
                                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-bold text-sm {{ $notification->is_read ? 'text-slate-300' : 'text-white' }}">
                                                {{ $notification->title }}
                                            </h4>
                                            @if(! $notification->is_read)
                                                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                            {{ $notification->message }}
                                        </p>
                                        <span class="text-[10px] text-slate-500 mt-2 block">
                                            {{ $notification->created_at->diffForHumans() }} ({{ $notification->created_at->translatedFormat('d M Y, H:i') }})
                                        </span>
                                    </div>
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 shrink-0">
                                    @if($notification->link)
                                        <x-button as="a" :href="$notification->link" variant="outline" size="sm" class="text-[11px] py-1 px-2.5">
                                            Buka &rarr;
                                        </x-button>
                                    @endif

                                    @if(! $notification->is_read)
                                        <form method="POST" action="{{ route('dashboard.notifications.read', $notification->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="text-[11px] text-slate-400 hover:text-cyan-400 font-semibold p-1 transition"
                                                title="Tandai telah dibaca"
                                            >
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="pt-4 border-t border-slate-800/80">
                    <x-pagination :paginator="$notifications" />
                </div>
            @else
                <x-empty-state
                    title="Tidak Ada Notifikasi"
                    description="Kotak masuk notifikasi Anda saat ini kosong. Notifikasi terkait pesanan dan promo akan muncul di sini."
                    :actionUrl="route('dashboard')"
                    actionText="Kembali ke Ringkasan"
                />
            @endif
        </div>
    </div>
</x-layout>
