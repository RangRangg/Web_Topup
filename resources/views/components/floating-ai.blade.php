<div
    x-data="{
        isOpen: false,
        prompt: '',
        loading: false,
        errorMessage: '',
        messages: [
            {
                role: 'ai',
                text: 'Halo Champion! Saya Aethereal AI, konsultan gaming resmi Aethereal Joki. Mau tanya rekomendasi top up berdasarkan budget, paket push rank terlaris, atau cek status orderanmu?',
                recommendations: [],
                navigation: [
                    { label: 'Budget 50rb MLBB', prompt: 'Saya punya budget 50 ribu untuk Mobile Legends' },
                    { label: 'Paket Push Mythic', prompt: 'Rekomendasi paket joki rank Mobile Legends' },
                    { label: 'Cek Status Order', prompt: 'Cek status order saya' }
                ]
            }
        ],
        init() {
            // Load user conversation history if authenticated
            fetch('{{ route('ai.history') }}')
                .then(res => res.json())
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        this.messages = data.messages;
                        this.scrollToBottom();
                    }
                })
                .catch(() => {});
        },
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.scrollToBottom();
                    if (this.$refs.aiInput) this.$refs.aiInput.focus();
                });
            }
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const box = this.$refs.chatBox;
                if (box) box.scrollTop = box.scrollHeight;
            });
        },
        sendPrompt(customText = null) {
            const query = customText || this.prompt.trim();
            if (!query || this.loading) return;

            this.errorMessage = '';
            this.messages.push({
                role: 'user',
                text: query,
                recommendations: [],
                navigation: []
            });

            if (!customText) this.prompt = '';
            this.loading = true;
            this.scrollToBottom();

            fetch('{{ route('ai-assistant.consult') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ prompt: query })
            })
            .then(async res => {
                if (res.status === 429) {
                    throw new Error('Terlalu banyak permintaan. Silakan tunggu beberapa detik sebelum bertanya lagi.');
                }
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal terhubung dengan Aethereal AI.');
                }
                return res.json();
            })
            .then(data => {
                this.messages.push({
                    role: 'ai',
                    text: data.reply,
                    recommendations: data.recommendations || [],
                    navigation: data.navigation || []
                });
                this.loading = false;
                this.scrollToBottom();
            })
            .catch(err => {
                this.errorMessage = err.message || 'Terjadi gangguan jaringan. Silakan coba kembali.';
                this.messages.push({
                    role: 'ai',
                    text: 'Maaf, terjadi kendala saat memproses permintaan Anda: ' + this.errorMessage,
                    recommendations: [],
                    navigation: []
                });
                this.loading = false;
                this.scrollToBottom();
            });
        },
        clearChat() {
            fetch('{{ route('ai.clear') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            }).catch(() => {});

            this.messages = [
                {
                    role: 'ai',
                    text: 'Obrolan telah dibersihkan. Ada yang bisa saya bantu selanjutnya untuk kebutuhan game Anda?',
                    recommendations: [],
                    navigation: []
                }
            ];
            this.errorMessage = '';
        }
    }"
    class="relative"
>
    {{-- Floating Trigger Button --}}
    <div class="fixed bottom-5 right-5 z-40">
        <button
            type="button"
            @click="toggleChat()"
            class="flex items-center gap-2 px-3.5 py-2.5 rounded-full bg-white dark:bg-[#111927] hover:bg-slate-100 dark:hover:bg-[#162134] text-slate-900 dark:text-white font-bold border border-slate-200 dark:border-slate-700 hover:border-sky-500 shadow-xl transition-colors focus:outline-none cursor-pointer"
            :class="isOpen ? 'border-sky-500 ring-1 ring-sky-500' : ''"
            aria-label="Aethereal AI Assistant"
            title="Aethereal AI Assistant"
        >
            <div class="w-6 h-6 rounded-full bg-sky-500 text-slate-950 flex items-center justify-center font-bold text-xs">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 pr-1">Aethereal AI Assistant</span>
        </button>
    </div>

    {{-- Chat Panel Floating Box --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-20 right-4 sm:right-6 z-50 w-[92vw] sm:w-[400px] h-[540px] max-h-[calc(100vh-6rem)] rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col overflow-hidden"
        style="display: none;"
    >
        {{-- Clean Header --}}
        <div class="p-3.5 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-sky-500 text-slate-950 flex items-center justify-center font-black text-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Aethereal AI Assistant</h3>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">Online 24 Jam</span>
                </div>
            </div>

            <div class="flex items-center gap-1">
                {{-- Clear chat --}}
                <button
                    type="button"
                    @click="clearChat()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 transition"
                    title="Bersihkan Chat"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>

                {{-- Close Button --}}
                <button
                    type="button"
                    @click="isOpen = false"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages Container --}}
        <div x-ref="chatBox" class="flex-1 p-3.5 overflow-y-auto space-y-3 scrollbar-thin text-xs">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="msg.role === 'user'
                            ? 'bg-sky-600 text-white rounded-xl rounded-tr-none max-w-[85%] p-3'
                            : 'bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-xl rounded-tl-none max-w-[92%] p-3 space-y-2'"
                    >
                        <div class="whitespace-pre-line leading-relaxed" x-text="msg.text"></div>

                        {{-- Recommendations --}}
                        <template x-if="msg.recommendations && msg.recommendations.length > 0">
                            <div class="mt-2 space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block">
                                    Rekomendasi Terkait:
                                </span>
                                <template x-for="(rec, rIndex) in msg.recommendations" :key="rIndex">
                                    <div class="p-2 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="rec.title"></h4>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate" x-text="rec.subtitle"></div>
                                            <template x-if="rec.price">
                                                <div class="font-bold text-sky-600 dark:text-sky-400 text-[11px] mt-0.5" x-text="rec.price"></div>
                                            </template>
                                        </div>
                                        <a
                                            :href="rec.url"
                                            class="px-2.5 py-1 rounded text-[10px] font-bold text-slate-950 bg-sky-500 hover:bg-sky-400 transition shrink-0"
                                        >
                                            Pilih &raquo;
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Quick links --}}
                        <template x-if="msg.navigation && msg.navigation.length > 0">
                            <div class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-800 flex flex-wrap gap-1">
                                <template x-for="(nav, nIndex) in msg.navigation" :key="nIndex">
                                    <template x-if="nav.prompt">
                                        <button
                                            type="button"
                                            @click="sendPrompt(nav.prompt)"
                                            class="px-2 py-0.5 rounded text-[10px] font-semibold text-sky-700 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/40 hover:bg-sky-100 dark:hover:bg-sky-900/40 transition cursor-pointer"
                                            x-text="nav.label"
                                        ></button>
                                    </template>
                                    <template x-if="nav.url">
                                        <a
                                            :href="nav.url"
                                            class="px-2 py-0.5 rounded text-[10px] font-semibold text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 transition"
                                            x-text="nav.label"
                                        ></a>
                                    </template>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="loading" class="flex justify-start" style="display: none;">
                <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-xs flex items-center gap-2">
                    <span class="inline-flex gap-1 items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    </span>
                    <span>Mengetik...</span>
                </div>
            </div>
        </div>

        {{-- Query Chips --}}
        <div class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center gap-1.5 overflow-x-auto scrollbar-none">
            <button
                type="button"
                @click="sendPrompt('Budget 50 ribu untuk Mobile Legends dapat apa?')"
                class="px-2.5 py-1 rounded text-[10px] font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:text-slate-950 dark:hover:text-white whitespace-nowrap transition cursor-pointer shadow-xs"
            >
                Budget 50rb MLBB
            </button>
            <button
                type="button"
                @click="sendPrompt('Cek status order saya')"
                class="px-2.5 py-1 rounded text-[10px] font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:text-slate-950 dark:hover:text-white whitespace-nowrap transition cursor-pointer shadow-xs"
            >
                Cek Status Order
            </button>
            <button
                type="button"
                @click="sendPrompt('Rekomendasi paket joki rank MLBB')"
                class="px-2.5 py-1 rounded text-[10px] font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:text-slate-950 dark:hover:text-white whitespace-nowrap transition cursor-pointer shadow-xs"
            >
                Paket Joki
            </button>
        </div>

        {{-- Input Form --}}
        <div class="p-2.5 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <form @submit.prevent="sendPrompt()" class="flex items-center gap-2">
                <input
                    type="text"
                    x-ref="aiInput"
                    x-model="prompt"
                    maxlength="500"
                    placeholder="Ketik pertanyaan atau kendala Anda..."
                    class="flex-1 px-3 py-2 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                    :disabled="loading"
                />

                <button
                    type="submit"
                    class="p-2 rounded-lg bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold transition disabled:opacity-40 cursor-pointer"
                    :disabled="loading || !prompt.trim()"
                    aria-label="Kirim"
                >
                    <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
