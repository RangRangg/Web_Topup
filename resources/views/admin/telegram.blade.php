<x-layout title="Integrasi Telegram Bot API — Admin Aethereal Nexus">
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="{
            botToken: '{{ $settings['bot_token'] }}',
            showToken: false,
            chatId: '{{ $settings['chat_id'] }}',
            purchaseChatId: '{{ $settings['purchase_chat_id'] }}',
            jokiChatId: '{{ $settings['joki_chat_id'] }}',
            enabled: {{ $settings['enabled'] ? 'true' : 'false' }},
            testingConnection: false,
            connectionResult: {{ json_encode($connectionTest) }},
            sendingTestMessage: false,
            testMessageResult: null,
            detectingChats: false,
            detectedChats: {{ json_encode($recentChats) }},
            showDetectModal: false,
            statusToast: null,

            notify(message, type = 'success') {
                this.statusToast = { message, type };
                setTimeout(() => { this.statusToast = null; }, 5000);
            },

            async testBotConnection() {
                if (!this.botToken.trim()) {
                    this.notify('Harap isi API Bot Token terlebih dahulu.', 'error');
                    return;
                }
                this.testingConnection = true;
                this.connectionResult = null;
                try {
                    const res = await fetch('{{ route('admin.telegram.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            bot_token: this.botToken,
                            send_message: false
                        })
                    });
                    const data = await res.json();
                    this.connectionResult = data;
                    if (data.success) {
                        this.notify(data.message, 'success');
                    } else {
                        this.notify(data.message, 'error');
                    }
                } catch (err) {
                    this.connectionResult = { success: false, message: 'Gagal menghubungi server: ' + err.message };
                    this.notify('Terjadi kesalahan saat memeriksa koneksi.', 'error');
                } finally {
                    this.testingConnection = false;
                }
            },

            async sendTestNotification() {
                if (!this.botToken.trim()) {
                    this.notify('Harap masukkan Bot Token API.', 'error');
                    return;
                }
                if (!this.chatId.trim()) {
                    this.notify('Harap tentukan Target Chat ID terlebih dahulu.', 'error');
                    return;
                }
                this.sendingTestMessage = true;
                this.testMessageResult = null;
                try {
                    const res = await fetch('{{ route('admin.telegram.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            bot_token: this.botToken,
                            chat_id: this.chatId,
                            send_message: true
                        })
                    });
                    const data = await res.json();
                    this.testMessageResult = data;
                    if (data.success) {
                        this.notify(data.message, 'success');
                    } else {
                        this.notify(data.message, 'error');
                    }
                } catch (err) {
                    this.testMessageResult = { success: false, message: 'Gagal mengirim: ' + err.message };
                    this.notify('Gagal mengirim pesan uji coba.', 'error');
                } finally {
                    this.sendingTestMessage = false;
                }
            },

            async fetchRecentChats() {
                if (!this.botToken.trim()) {
                    this.notify('Harap isi API Bot Token terlebih dahulu.', 'error');
                    return;
                }
                this.detectingChats = true;
                this.showDetectModal = true;
                try {
                    const res = await fetch('{{ route('admin.telegram.detect-chats') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            bot_token: this.botToken
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.detectedChats = data.chats || [];
                        if (this.detectedChats.length === 0) {
                            this.notify('Belum ada pesan masuk ke bot ini. Kirim /start ke bot Anda di Telegram lalu ulangi.', 'info');
                        }
                    } else {
                        this.notify('Gagal mendeteksi riwayat chat.', 'error');
                    }
                } catch (err) {
                    this.notify('Error: ' + err.message, 'error');
                } finally {
                    this.detectingChats = false;
                }
            },

            applyChatId(id, target = 'main') {
                if (target === 'main') {
                    this.chatId = id;
                    this.notify('Chat ID utama berhasil disetel: ' + id, 'success');
                } else if (target === 'purchase') {
                    this.purchaseChatId = id;
                    this.notify('Chat ID pesanan top up disetel: ' + id, 'success');
                } else if (target === 'joki') {
                    this.jokiChatId = id;
                    this.notify('Chat ID pesanan joki disetel: ' + id, 'success');
                }
                this.showDetectModal = false;
            }
        }"
    >
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[['label' => 'Admin Panel', 'url' => route('admin.dashboard')], ['label' => 'Integrasi Telegram API']]" />

        {{-- Admin Navigation Bar (Scrollable Tabs) --}}
        @include('admin.partials.nav')

        {{-- Toast Notification --}}
        <div
            x-show="statusToast"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-6 right-6 z-50 max-w-md p-4 rounded-2xl shadow-2xl border flex items-center gap-3 backdrop-blur-md"
            :class="{
                'bg-emerald-950/90 border-emerald-500/40 text-emerald-200': statusToast && statusToast.type === 'success',
                'bg-rose-950/90 border-rose-500/40 text-rose-200': statusToast && statusToast.type === 'error',
                'bg-sky-950/90 border-sky-500/40 text-sky-200': statusToast && statusToast.type === 'info'
            }"
            style="display: none;"
        >
            <template x-if="statusToast && statusToast.type === 'success'">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </template>
            <template x-if="statusToast && statusToast.type === 'error'">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </template>
            <template x-if="statusToast && statusToast.type === 'info'">
                <svg class="w-5 h-5 text-sky-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </template>
            <span class="text-xs font-medium" x-text="statusToast ? statusToast.message : ''"></span>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-xs font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
                <div class="flex items-center gap-2 mb-1.5 font-bold text-xs">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Terdapat kesalahan pada formulir:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Title Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-sky-500/15 text-sky-400 border border-sky-500/30">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Official Bot Integration
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Telegram Bot API v7+</span>
                </div>
                <h2 class="text-2xl font-black text-white tracking-tight">Koneksi & Integrasi Telegram Bot</h2>
                <p class="text-xs text-slate-400 mt-1">Konfigurasikan Bot Telegram untuk menerima notifikasi instan pesanan top up game dan order joki real-time.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    @click="testBotConnection()"
                    :disabled="testingConnection"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-sky-300 bg-sky-500/10 border border-sky-500/30 hover:bg-sky-500/20 disabled:opacity-50 transition"
                >
                    <svg class="w-4 h-4" :class="{ 'animate-spin': testingConnection }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="testingConnection ? 'Memeriksa...' : 'Cek Status Bot'"></span>
                </button>

                <button
                    type="button"
                    @click="fetchRecentChats()"
                    :disabled="detectingChats"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-200 bg-slate-900 border border-slate-800 hover:border-slate-700 disabled:opacity-50 transition"
                >
                    <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Cari Chat ID</span>
                </button>
            </div>
        </div>

        {{-- Bot Status Overview Banner --}}
        <div class="mb-8 p-5 rounded-2xl border transition-all"
            :class="{
                'bg-slate-900/90 border-slate-800': !connectionResult || (!connectionResult.success && !connectionResult.message),
                'bg-emerald-950/20 border-emerald-500/30': connectionResult && connectionResult.success,
                'bg-rose-950/20 border-rose-500/30': connectionResult && !connectionResult.success
            }"
        >
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-lg"
                        :class="{
                            'bg-sky-500/10 text-sky-400 border border-sky-500/30': connectionResult && connectionResult.success,
                            'bg-rose-500/10 text-rose-400 border border-rose-500/30': connectionResult && !connectionResult.success,
                            'bg-slate-800 text-slate-400 border border-slate-700': !connectionResult
                        }"
                    >
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <template x-if="connectionResult && connectionResult.success">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    TERHUBUNG KE TELEGRAM
                                </span>
                            </template>
                            <template x-if="connectionResult && !connectionResult.success">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                    BELUM TERHUBUNG / SALAH TOKEN
                                </span>
                            </template>
                            <template x-if="!connectionResult">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                    STATUS BELUM DICEK
                                </span>
                            </template>
                        </div>

                        <div class="text-base font-bold text-white mt-1">
                            <template x-if="connectionResult && connectionResult.success && connectionResult.bot">
                                <span>
                                    <span x-text="connectionResult.bot.first_name"></span>
                                    <span class="text-sky-400 font-mono text-sm ml-1" x-text="'(@' + connectionResult.bot.username + ')'"></span>
                                </span>
                            </template>
                            <template x-if="!connectionResult || !connectionResult.success">
                                <span>Bot Telegram Belum Aktif</span>
                            </template>
                        </div>

                        <p class="text-xs text-slate-400 mt-0.5" x-text="connectionResult ? connectionResult.message : 'Masukkan Bot Token API dari @BotFather di bawah ini untuk menghubungkan.'"></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-800">
                    <template x-if="connectionResult && connectionResult.bot">
                        <div class="flex items-center gap-3 text-xs text-slate-300 font-mono bg-slate-950/70 px-3.5 py-2 rounded-xl border border-slate-800">
                            <div>
                                <span class="text-slate-500">ID Bot:</span>
                                <span class="text-slate-200 font-bold ml-1" x-text="connectionResult.bot.id"></span>
                            </div>
                            <div class="h-3 w-px bg-slate-800"></div>
                            <div>
                                <span class="text-slate-500">Grup:</span>
                                <span class="ml-1" :class="connectionResult.bot.can_join_groups ? 'text-emerald-400' : 'text-slate-500'" x-text="connectionResult.bot.can_join_groups ? 'Ya' : 'Tidak'"></span>
                            </div>
                        </div>
                    </template>

                    <button
                        type="button"
                        @click="sendTestNotification()"
                        :disabled="sendingTestMessage || !chatId.trim() || !botToken.trim()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 disabled:opacity-50 shadow-lg shadow-sky-500/20 transition"
                    >
                        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': sendingTestMessage }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-text="sendingTestMessage ? 'Mengirim...' : 'Tes Kirim Pesan'"></span>
                    </button>
                </div>
            </div>

            {{-- Test Message Result Alert --}}
            <template x-if="testMessageResult">
                <div class="mt-4 p-3 rounded-xl border text-xs flex items-center justify-between gap-3"
                    :class="testMessageResult.success ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/40 border-rose-500/40 text-rose-300'"
                >
                    <div class="flex items-center gap-2">
                        <span x-text="testMessageResult.success ? '✅' : '❌'"></span>
                        <span x-text="testMessageResult.message"></span>
                    </div>
                    <button type="button" @click="testMessageResult = null" class="text-slate-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Main Configuration Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Form Column (2 Cols) --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-white">Pengaturan API & Target Notifikasi</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Ubah token API atau ganti target chat ID untuk menghubungkan ke akun/grup Telegram baru.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-mono text-slate-400 bg-slate-950 border border-slate-800">
                            Auto-sync .env
                        </span>
                    </div>

                    <form action="{{ route('admin.telegram.update') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- Bot Token Field --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="bot_token" class="block text-xs font-bold text-slate-200">
                                    Telegram Bot API Token <span class="text-rose-400">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="testBotConnection()"
                                    class="text-[11px] font-bold text-sky-400 hover:text-sky-300 transition"
                                >
                                    Verifikasi Token Sekarang
                                </button>
                            </div>

                            <div class="relative">
                                <input
                                    :type="showToken ? 'text' : 'password'"
                                    id="bot_token"
                                    name="bot_token"
                                    x-model="botToken"
                                    required
                                    placeholder="Contoh: 123456789:AAHPaXTRjbX1eFfnlC_MtototXJnjtWQNSs"
                                    class="w-full pl-4 pr-24 py-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none font-mono"
                                />
                                <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click="showToken = !showToken"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                                        :title="showToken ? 'Sembunyikan Token' : 'Lihat Token'"
                                    >
                                        <svg x-show="!showToken" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="showToken" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1.5">
                                Didapatkan dari <b>@BotFather</b> saat Anda membuat bot dengan perintah <code class="text-sky-300">/newbot</code>.
                            </p>
                        </div>

                        {{-- Target Chat ID Utama --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="chat_id" class="block text-xs font-bold text-slate-200">
                                    Target Chat ID Utama (Default) <span class="text-rose-400">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="fetchRecentChats()"
                                    class="text-[11px] font-bold text-purple-400 hover:text-purple-300 transition flex items-center gap-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                    </svg>
                                    Deteksi Chat ID Otomatis
                                </button>
                            </div>

                            <input
                                type="text"
                                id="chat_id"
                                name="chat_id"
                                x-model="chatId"
                                required
                                placeholder="Contoh: 5170978169 atau -1001234567890"
                                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none font-mono"
                            />
                            <p class="text-[11px] text-slate-500 mt-1.5">
                                User ID Telegram Anda untuk chat pribadi, atau Group/Channel ID (diawali tanda minus <code class="text-sky-300">-</code> atau <code class="text-sky-300">-100</code>).
                            </p>
                        </div>

                        {{-- Grid: Chat ID Khusus Topup & Joki (Optional Separation) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-800/80">
                            {{-- Purchase Chat ID --}}
                            <div>
                                <label for="purchase_chat_id" class="block text-xs font-bold text-slate-300 mb-1.5">
                                    Chat ID Khusus Top Up Game <span class="text-slate-500 font-normal">(Opsional)</span>
                                </label>
                                <input
                                    type="text"
                                    id="purchase_chat_id"
                                    name="purchase_chat_id"
                                    x-model="purchaseChatId"
                                    placeholder="Kosongkan jika sama dengan Default"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none font-mono"
                                />
                                <p class="text-[10px] text-slate-500 mt-1">Dipakai untuk notifikasi pesanan diamond/koin game.</p>
                            </div>

                            {{-- Joki Chat ID --}}
                            <div>
                                <label for="joki_chat_id" class="block text-xs font-bold text-slate-300 mb-1.5">
                                    Chat ID Khusus Layanan Joki <span class="text-slate-500 font-normal">(Opsional)</span>
                                </label>
                                <input
                                    type="text"
                                    id="joki_chat_id"
                                    name="joki_chat_id"
                                    x-model="jokiChatId"
                                    placeholder="Kosongkan jika sama dengan Default"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none font-mono"
                                />
                                <p class="text-[10px] text-slate-500 mt-1">Dipakai untuk channel booster atau tim penjoki.</p>
                            </div>
                        </div>

                        {{-- Notification Toggle --}}
                        <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-white">Status Notifikasi Real-time</div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Kirim pesan otomatis saat pesanan baru masuk dan pembayaran berhasil terverifikasi.</p>
                            </div>

                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="enabled"
                                    value="1"
                                    x-model="enabled"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        {{-- Submit Action Bar --}}
                        <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <span class="text-xs text-slate-500">
                                Perubahan akan disimpan secara permanen ke file konfigurasi sistem dan .env.
                            </span>

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button
                                    type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-600/30 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Simpan Pengaturan API</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Live Message Preview Cards --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <h3 class="text-sm font-bold text-white mb-2">Pratinjau Format Notifikasi Telegram</h3>
                    <p class="text-xs text-slate-400 mb-4">Pesan yang otomatis dikirim ke bot Telegram Anda saat customer memesan atau menyelesaikan pembayaran.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Top Up Preview --}}
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 font-mono text-[11px] text-slate-300 space-y-1">
                            <div class="text-sky-400 font-bold">🛒 PESANAN BARU: TOP UP GAME</div>
                            <div class="text-slate-600">━━━━━━━━━━━━━━━━━━━━</div>
                            <div>📦 <b>Kode:</b> <span class="text-white">#AJ-TOPUP99</span></div>
                            <div>🎮 <b>Game:</b> Mobile Legends</div>
                            <div>💎 <b>Produk:</b> 86 Diamonds</div>
                            <div>🆔 <b>User ID:</b> 12345678 (2024)</div>
                            <div>💰 <b>Total:</b> <span class="text-emerald-400 font-bold">Rp 21.000</span></div>
                            <div>💳 <b>Metode:</b> QRIS Real-time</div>
                            <div>📊 <b>Status:</b> 🟡 <i>Menunggu Pembayaran</i></div>
                        </div>

                        {{-- Joki Preview --}}
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 font-mono text-[11px] text-slate-300 space-y-1">
                            <div class="text-indigo-400 font-bold">🎮 PESANAN BARU: JASA JOKI</div>
                            <div class="text-slate-600">━━━━━━━━━━━━━━━━━━━━</div>
                            <div>📦 <b>Kode:</b> <span class="text-white">#AJ-JOKI77</span></div>
                            <div>🎯 <b>Game:</b> Valorant</div>
                            <div>🛡️ <b>Paket:</b> Joki Rank Diamond ke Ascendant</div>
                            <div>💰 <b>Total:</b> <span class="text-emerald-400 font-bold">Rp 150.000</span></div>
                            <div>💵 <b>Komisi Booster:</b> Rp 120.000 (80%)</div>
                            <div>📊 <b>Status:</b> 🔵 <i>Siap Diambil Penjoki</i></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guide & Help Column (1 Col) --}}
            <div class="space-y-6">
                {{-- Quick Guide Card --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white">Panduan Setup Bot Telegram</h3>
                    </div>

                    <ol class="space-y-4 text-xs text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-slate-800 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">1</span>
                            <div>
                                <span class="font-bold text-white">Buat Bot di @BotFather</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Buka aplikasi Telegram, cari <a href="https://t.me/BotFather" target="_blank" class="text-sky-400 hover:underline">@BotFather</a> dan kirim pesan <code class="text-rose-400">/newbot</code>.</p>
                            </div>
                        </li>

                        <li class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-slate-800 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">2</span>
                            <div>
                                <span class="font-bold text-white">Salin HTTP API Token</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">BotFather akan memberikan token seperti <code class="text-sky-300">8981326:AAH...</code>. Salin dan tempel di formulir Telegram API.</p>
                            </div>
                        </li>

                        <li class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-slate-800 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">3</span>
                            <div>
                                <span class="font-bold text-white">Mulai Chat / Start Bot</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Cari bot yang baru Anda buat, lalu tekan tombol <b>START</b> (kirim <code class="text-rose-400">/start</code>) agar bot memiliki izin mengirim pesan ke Anda.</p>
                            </div>
                        </li>

                        <li class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-slate-800 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">4</span>
                            <div>
                                <span class="font-bold text-white">Dapatkan Chat ID Anda</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol <b>"Deteksi Chat ID Otomatis"</b> di samping, atau cek User ID Anda melalui bot <a href="https://t.me/userinfobot" target="_blank" class="text-sky-400 hover:underline">@userinfobot</a>.</p>
                            </div>
                        </li>

                        <li class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-slate-800 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">5</span>
                            <div>
                                <span class="font-bold text-white">Grup atau Channel (Opsional)</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Jika ingin dikirim ke grup, masukkan bot Anda ke dalam grup dan angkat sebagai admin grup.</p>
                            </div>
                        </li>
                    </ol>

                    <div class="mt-6 pt-4 border-t border-slate-800">
                        <button
                            type="button"
                            @click="fetchRecentChats()"
                            class="w-full py-2.5 rounded-xl bg-purple-500/10 border border-purple-500/30 text-purple-300 hover:bg-purple-500/20 text-xs font-bold transition flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                            <span>Buka Riwayat Deteksi Chat ID</span>
                        </button>
                    </div>
                </div>

                {{-- Trigger Events Card --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <h3 class="text-sm font-bold text-white mb-3">Kapan Notifikasi Dikirim?</h3>
                    <ul class="space-y-3 text-xs text-slate-300">
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 mt-1.5 shrink-0"></span>
                            <div>
                                <span class="font-semibold text-white">Pesanan Top Up Masuk:</span>
                                <span class="text-slate-400 block text-[11px]">Saat customer membuat invoice top up diamond game.</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400 mt-1.5 shrink-0"></span>
                            <div>
                                <span class="font-semibold text-white">Pesanan Joki Masuk:</span>
                                <span class="text-slate-400 block text-[11px]">Saat order joki rank dibuat dengan instruksi akun.</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></span>
                            <div>
                                <span class="font-semibold text-white">Pembayaran Lunas (Auto):</span>
                                <span class="text-slate-400 block text-[11px]">Saat transaksi QRIS/Virtual Account terkonfirmasi lunas.</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Modal: Deteksi Chat ID Otomatis --}}
        <div
            x-show="showDetectModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
            style="display: none;"
            @keydown.escape.window="showDetectModal = false"
        >
            <div
                class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl relative"
                @click.away="showDetectModal = false"
            >
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Deteksi Chat ID Otomatis</h3>
                            <p class="text-xs text-slate-400">Pilih ID dari riwayat pesan yang baru saja dikirim ke bot Anda.</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="showDetectModal = false"
                        class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    <template x-if="detectingChats">
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <svg class="w-6 h-6 animate-spin mx-auto text-sky-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Mengecek pesan masuk ke bot Telegram...</span>
                        </div>
                    </template>

                    <template x-if="!detectingChats && detectedChats.length === 0">
                        <div class="py-10 text-center text-slate-400 text-xs bg-slate-950/50 rounded-2xl border border-slate-800 p-6">
                            <svg class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="font-bold text-slate-300">Belum Ada Pesan Masuk ke Bot Ini</p>
                            <p class="text-slate-500 mt-1 max-w-sm mx-auto">
                                Buka aplikasi Telegram, cari bot Anda, kirim pesan apa saja (misal: <code class="text-sky-300 font-mono">/start</code> atau <code class="text-sky-300 font-mono">Halo</code>), lalu tekan tombol refresh di bawah ini.
                            </p>
                            <button
                                type="button"
                                @click="fetchRecentChats()"
                                class="mt-4 px-4 py-2 rounded-xl bg-sky-500/10 border border-sky-500/30 text-sky-300 text-xs font-bold hover:bg-sky-500/20 transition"
                            >
                                Refresh Pesan Sekarang
                            </button>
                        </div>
                    </template>

                    <template x-if="!detectingChats && detectedChats.length > 0">
                        <div class="space-y-2">
                            <template x-for="chat in detectedChats" :key="chat.id">
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 flex items-center justify-between gap-3 transition">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-white" x-text="chat.name"></span>
                                            <span class="text-[10px] font-mono text-slate-500" x-text="chat.username ? chat.username : ''"></span>
                                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-800 text-slate-400" x-text="chat.type"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs font-mono font-bold text-sky-400" x-text="chat.id"></span>
                                            <span class="text-[10px] text-slate-500" x-text="'Pesan: ' + chat.last_message"></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button
                                            type="button"
                                            @click="applyChatId(chat.id, 'main')"
                                            class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-sky-300 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 transition"
                                        >
                                            Gunakan ID
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-500">
                        Kirim pesan ke bot Telegram jika ID Anda belum muncul.
                    </span>

                    <button
                        type="button"
                        @click="showDetectModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
