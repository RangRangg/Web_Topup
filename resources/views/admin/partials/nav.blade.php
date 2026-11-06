<div class="mb-8">
    {{-- Header Banner / Title Area --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800/80">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-rose-500/10 text-rose-400 border border-rose-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    Admin Command Center
                </span>
                <span class="text-xs text-slate-500 font-mono">v2.5 • Role: {{ auth()->user()->role }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="Aetherel" class="w-8 h-8 rounded-lg object-contain" />
                <span>Halo, <span class="bg-gradient-to-r from-cyan-400 to-indigo-400 bg-clip-text text-transparent">{{ auth()->user()->name ?? 'Admin' }}</span></span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Pusat kendali operasional, marketplace games, pengguna, dan audit platform.</p>
        </div>

        <div class="flex items-center gap-2">
            @php
                $telegramSettings = app(\App\Services\TelegramService::class)->getSettings();
                $isTeleReady = $telegramSettings['enabled'] && !empty($telegramSettings['bot_token']) && !empty($telegramSettings['chat_id']);
            @endphp
            <a
                href="{{ route('admin.telegram') }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.telegram*') ? 'text-sky-300 bg-sky-500/20 border border-sky-500/40 shadow-[0_0_15px_rgba(56,189,248,0.25)]' : 'text-slate-300 hover:text-white bg-slate-900 border border-slate-800 hover:border-sky-500/40' }}"
                title="Integrasi Telegram Bot API"
            >
                <svg class="w-4 h-4 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>Telegram API</span>
                @if($isTeleReady)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1"></span>
                        ON
                    </span>
                @else
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        SETUP
                    </span>
                @endif
            </a>

            <a
                href="{{ route('admin.reports') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-300 hover:text-white bg-slate-900 border border-slate-800 hover:border-cyan-500/40 transition"
            >
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Lihat Laporan</span>
            </a>

            <a
                href="{{ route('home') }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-cyan-400 bg-cyan-500/10 border border-cyan-500/30 hover:bg-cyan-500/20 transition"
            >
                <span>Lihat Situs</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Admin Navigation Pills (Horizontally scrollable with mouse wheel, drag, and arrow controls) --}}
    <div class="relative mt-4 group/nav-scroll">
        {{-- Left Scroll Arrow Button --}}
        <button
            type="button"
            id="adminNavScrollLeft"
            aria-label="Scroll tab ke kiri"
            class="hidden sm:flex absolute -left-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-slate-900/95 border border-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-800 hover:border-cyan-500/50 shadow-xl items-center justify-center transition-all opacity-0 pointer-events-none group-hover/nav-scroll:opacity-100 disabled:opacity-0 disabled:pointer-events-none"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        {{-- Left Fade Indicator --}}
        <div id="adminNavFadeLeft" class="pointer-events-none absolute left-0 top-0 bottom-2 w-8 bg-gradient-to-r from-slate-950/90 to-transparent z-10 opacity-0 transition-opacity"></div>

        {{-- The Horizontal Tabs Container (Only this section scrolls horizontally) --}}
        <div
            id="adminNavScrollContainer"
            class="flex items-center gap-2 overflow-x-auto pb-2.5 border-b border-slate-800/80 -mx-4 px-4 sm:mx-0 sm:px-0 select-none cursor-grab active:cursor-grabbing scroll-smooth admin-nav-scroll"
            style="scrollbar-width: thin; scrollbar-color: rgba(56, 189, 248, 0.35) transparent;"
        >
            {{-- Overview --}}
            <a
                href="{{ route('admin.dashboard') }}"
                data-nav-active="{{ request()->routeIs('admin.dashboard') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Overview</span>
            </a>

            {{-- Users --}}
            <a
                href="{{ route('admin.users') }}"
                data-nav-active="{{ request()->routeIs('admin.users*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.users*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.users*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Users</span>
            </a>

            {{-- Games --}}
            <a
                href="{{ route('admin.games') }}"
                data-nav-active="{{ request()->routeIs('admin.games*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.games*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.games*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Games</span>
            </a>

            {{-- Game Products --}}
            <a
                href="{{ route('admin.products') }}"
                data-nav-active="{{ request()->routeIs('admin.products*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.products*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.products*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Produk Top Up</span>
            </a>

            {{-- Joki Services --}}
            <a
                href="{{ route('admin.joki-services') }}"
                data-nav-active="{{ request()->routeIs('admin.joki-services*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.joki-services*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.joki-services*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Layanan Joki</span>
            </a>

            {{-- Orders --}}
            <a
                href="{{ route('admin.orders') }}"
                data-nav-active="{{ request()->routeIs('admin.orders*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.orders*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.orders*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Pesanan</span>
            </a>

            {{-- Payments --}}
            <a
                href="{{ route('admin.payments') }}"
                data-nav-active="{{ request()->routeIs('admin.payments*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.payments*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.payments*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>Pembayaran</span>
            </a>

            {{-- Promotions --}}
            <a
                href="{{ route('admin.promotions') }}"
                data-nav-active="{{ request()->routeIs('admin.promotions*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.promotions*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.promotions*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span>Promosi & Diskon</span>
            </a>

            {{-- Reports --}}
            <a
                href="{{ route('admin.reports') }}"
                data-nav-active="{{ request()->routeIs('admin.reports*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.reports*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.reports*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Laporan</span>
            </a>

            {{-- Audit Logs --}}
            <a
                href="{{ route('admin.audit-logs') }}"
                data-nav-active="{{ request()->routeIs('admin.audit-logs*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.audit-logs*') ? 'text-rose-300 bg-rose-500/15 border border-rose-500/35 shadow-[0_0_15px_rgba(244,63,94,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.audit-logs*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Audit Trail</span>
            </a>

            {{-- Telegram Bot API Integration --}}
            <a
                href="{{ route('admin.telegram') }}"
                data-nav-active="{{ request()->routeIs('admin.telegram*') ? 'true' : 'false' }}"
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap {{ request()->routeIs('admin.telegram*') ? 'text-sky-300 bg-sky-500/15 border border-sky-500/35 shadow-[0_0_15px_rgba(56,189,248,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent' }}"
            >
                <svg class="w-4 h-4 {{ request()->routeIs('admin.telegram*') ? 'text-sky-400' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>Telegram API</span>
                @if($isTeleReady)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                @endif
            </a>
        </div>

        {{-- Right Fade Indicator --}}
        <div id="adminNavFadeRight" class="pointer-events-none absolute right-0 top-0 bottom-2 w-8 bg-gradient-to-l from-slate-950/90 to-transparent z-10 opacity-0 transition-opacity"></div>

        {{-- Right Scroll Arrow Button --}}
        <button
            type="button"
            id="adminNavScrollRight"
            aria-label="Scroll tab ke kanan"
            class="hidden sm:flex absolute -right-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-slate-900/95 border border-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-800 hover:border-cyan-500/50 shadow-xl items-center justify-center transition-all opacity-0 pointer-events-none group-hover/nav-scroll:opacity-100 disabled:opacity-0 disabled:pointer-events-none"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Script khusus scrolling navigasi horizontal --}}
    <script>
        (function() {
            const container = document.getElementById('adminNavScrollContainer');
            const btnLeft = document.getElementById('adminNavScrollLeft');
            const btnRight = document.getElementById('adminNavScrollRight');
            const fadeLeft = document.getElementById('adminNavFadeLeft');
            const fadeRight = document.getElementById('adminNavFadeRight');

            if (!container) return;

            function updateScrollState() {
                const scrollLeft = container.scrollLeft;
                const maxScroll = container.scrollWidth - container.clientWidth;

                if (btnLeft && fadeLeft) {
                    const canScrollLeft = scrollLeft > 6;
                    btnLeft.disabled = !canScrollLeft;
                    btnLeft.style.pointerEvents = canScrollLeft ? 'auto' : 'none';
                    fadeLeft.style.opacity = canScrollLeft ? '1' : '0';
                }

                if (btnRight && fadeRight) {
                    const canScrollRight = scrollLeft < (maxScroll - 6);
                    btnRight.disabled = !canScrollRight;
                    btnRight.style.pointerEvents = canScrollRight ? 'auto' : 'none';
                    fadeRight.style.opacity = canScrollRight ? '1' : '0';
                }
            }

            // 1. Mouse wheel horizontal scrolling (rolling wheel up/down over tabs scrolls them left/right)
            container.addEventListener('wheel', function(e) {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                    e.preventDefault();
                    container.scrollLeft += e.deltaY * 0.9;
                    updateScrollState();
                }
            }, { passive: false });

            // 2. Drag / Grab to scroll with mouse
            let isDown = false;
            let startX = 0;
            let initialScroll = 0;
            let dragMoved = false;

            container.addEventListener('mousedown', function(e) {
                isDown = true;
                dragMoved = false;
                startX = e.pageX - container.offsetLeft;
                initialScroll = container.scrollLeft;
                container.classList.add('cursor-grabbing');
            });

            window.addEventListener('mouseup', function() {
                if (isDown) {
                    isDown = false;
                    container.classList.remove('cursor-grabbing');
                }
            });

            container.addEventListener('mousemove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                const currentX = e.pageX - container.offsetLeft;
                const walk = (currentX - startX) * 1.3;
                if (Math.abs(walk) > 4) {
                    dragMoved = true;
                }
                container.scrollLeft = initialScroll - walk;
                updateScrollState();
            });

            // Prevent tab navigation if user was dragging
            container.querySelectorAll('a').forEach(function(anchor) {
                anchor.addEventListener('click', function(e) {
                    if (dragMoved) {
                        e.preventDefault();
                    }
                });
            });

            // 3. Arrow buttons click handlers
            if (btnLeft) {
                btnLeft.addEventListener('click', function() {
                    container.scrollBy({ left: -260, behavior: 'smooth' });
                    setTimeout(updateScrollState, 300);
                });
            }

            if (btnRight) {
                btnRight.addEventListener('click', function() {
                    container.scrollBy({ left: 260, behavior: 'smooth' });
                    setTimeout(updateScrollState, 300);
                });
            }

            container.addEventListener('scroll', updateScrollState, { passive: true });
            window.addEventListener('resize', updateScrollState);

            // 4. Auto center the active tab into view on load
            const activeTab = container.querySelector('[data-nav-active="true"]');
            if (activeTab) {
                setTimeout(function() {
                    activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    setTimeout(updateScrollState, 350);
                }, 80);
            } else {
                updateScrollState();
            }
        })();
    </script>
</div>
