<x-layout title="Aethereal AI Assistant - Konsultan Gaming Cerdas & Rekomendasi Joki">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" x-data="{
        prompt: '',
        loading: false,
        errorMessage: '',
        messages: {{ Js::from(!empty($initialMessages) ? $initialMessages : [[
            'role' => 'ai',
            'text' => 'Halo Champion! Saya Aethereal AI, konsultan gaming resmi Aethereal Joki. Butuh rekomendasi top up berdasarkan budget, paket push rank Mythic, atau cek status pesananmu?',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Budget 50rb MLBB', 'prompt' => 'Saya punya budget 50 ribu untuk Mobile Legends'],
                ['label' => 'Paket Push Mythic', 'prompt' => 'Rekomendasi paket joki rank Mobile Legends'],
                ['label' => 'Cek Status Order', 'prompt' => 'Cek status order saya']
            ]
        ]]) }},
        init() {
            this.scrollToBottom();
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
                    throw new Error(err.message || 'Gagal memproses jawaban dari AI.');
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
                this.errorMessage = err.message || 'Terjadi gangguan koneksi.';
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
                    navigation: [
                        { label: 'Budget 50rb MLBB', prompt: 'Saya punya budget 50 ribu untuk Mobile Legends' },
                        { label: 'Paket Push Mythic', prompt: 'Rekomendasi paket joki rank Mobile Legends' }
                    ]
                }
            ];
            this.errorMessage = '';
        }
    }">
        <x-breadcrumb :items="[['label' => 'AI Assistant', 'url' => route('ai-assistant')]]" />

        <div class="mt-4 mb-8 text-center max-w-2xl mx-auto">
            <x-badge variant="purple" size="sm" class="mb-2">INTELLIGENT GAMING CONSULTANT</x-badge>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                Aethereal <span class="bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">AI Assistant</span>
            </h1>
            <p class="text-sm text-slate-400 mt-2">Dapatkan rekomendasi top up berdasarkan budget, pilih paket joki terbaik, cek status order, dan jelajahi layanan Aethereal Joki.</p>
        </div>

        {{-- Chat Box Container --}}
        <div class="rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl overflow-hidden flex flex-col h-[600px] relative">
            {{-- Header --}}
            <div class="p-4 bg-slate-950/90 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-cyan-500 p-0.5 shadow-[0_0_15px_rgba(168,85,247,0.3)]">
                        <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center text-cyan-400 font-black">
                            <svg class="w-5 h-5 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white flex items-center gap-1.5">
                            Aethereal Neural Consultant
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-cyan-500/20 text-cyan-300 font-mono">v2.5</span>
                        </h4>
                        <span class="text-[11px] text-emerald-400 flex items-center gap-1 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Online & Siap Membantu
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="clearChat()"
                        class="px-2.5 py-1.5 rounded-xl text-xs text-slate-400 hover:text-white bg-slate-900 hover:bg-slate-800 border border-slate-800 transition flex items-center gap-1.5"
                        title="Bersihkan Percakapan"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Bersihkan</span>
                    </button>
                    <x-badge variant="primary" size="sm">Smart Mode</x-badge>
                </div>
            </div>

            {{-- Message Body --}}
            <div x-ref="chatBox" class="flex-1 p-4 sm:p-6 overflow-y-auto space-y-4 scrollbar-thin scrollbar-thumb-slate-800">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            :class="msg.role === 'user'
                                ? 'bg-gradient-to-r from-cyan-600 to-indigo-600 text-white rounded-2xl rounded-tr-none max-w-lg p-4 shadow-md text-xs sm:text-sm'
                                : 'bg-slate-950/90 border border-slate-800 text-slate-200 rounded-2xl rounded-tl-none max-w-xl p-4 shadow-lg text-xs sm:text-sm space-y-3'"
                        >
                            {{-- Message text --}}
                            <div class="whitespace-pre-line leading-relaxed" x-text="msg.text"></div>

                            {{-- Render Structured Recommendations if any --}}
                            <template x-if="msg.recommendations && msg.recommendations.length > 0">
                                <div class="mt-3 space-y-2 pt-3 border-t border-slate-800">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-400 block">
                                        Rekomendasi Produk & Layanan:
                                    </span>
                                    <div class="grid grid-cols-1 gap-2">
                                        <template x-for="(rec, rIndex) in msg.recommendations" :key="rIndex">
                                            <div class="p-3 rounded-xl bg-slate-900 border border-purple-500/30 hover:border-cyan-400/50 transition flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h5 class="text-xs font-bold text-white truncate" x-text="rec.title"></h5>
                                                        <template x-if="rec.badge">
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-purple-500/20 text-purple-300 font-mono" x-text="rec.badge"></span>
                                                        </template>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 truncate mt-0.5" x-text="rec.subtitle"></div>
                                                    <template x-if="rec.price">
                                                        <div class="font-mono font-bold text-cyan-400 text-xs mt-1" x-text="rec.price"></div>
                                                    </template>
                                                </div>

                                                <a
                                                    :href="rec.url"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-950 bg-gradient-to-r from-cyan-400 to-indigo-300 hover:from-cyan-300 hover:to-indigo-200 transition shrink-0 shadow-md"
                                                >
                                                    Pilih Paket &raquo;
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Navigation Chips --}}
                            <template x-if="msg.navigation && msg.navigation.length > 0">
                                <div class="mt-2.5 pt-2.5 border-t border-slate-800/80 flex flex-wrap gap-2">
                                    <template x-for="(nav, nIndex) in msg.navigation" :key="nIndex">
                                        <template x-if="nav.prompt">
                                            <button
                                                type="button"
                                                @click="sendPrompt(nav.prompt)"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-cyan-300 bg-cyan-950/40 hover:bg-cyan-900/60 border border-cyan-800/50 transition cursor-pointer"
                                                x-text="nav.label"
                                            ></button>
                                        </template>
                                        <template x-if="nav.url">
                                            <a
                                                :href="nav.url"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-purple-300 bg-purple-950/40 hover:bg-purple-900/60 border border-purple-800/50 transition"
                                                x-text="nav.label"
                                            ></a>
                                        </template>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="flex justify-start">
                        <div class="bg-slate-950/90 border border-slate-800 p-4 rounded-2xl text-xs text-slate-300 flex items-center gap-3 shadow-lg">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                            <span class="animate-pulse">Aethereal AI sedang mencari database katalog dan menganalisis permintaan...</span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Suggested Question Pills --}}
            <div class="px-4 py-2.5 bg-slate-950/60 border-t border-slate-800 flex items-center gap-2 overflow-x-auto scrollbar-none">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider shrink-0">Pertanyaan Populer:</span>
                <button
                    type="button"
                    @click="sendPrompt('Saya punya budget 50 ribu untuk Mobile Legends')"
                    class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 hover:border-cyan-500/50 text-[11px] text-slate-300 hover:text-cyan-400 whitespace-nowrap transition cursor-pointer"
                >
                    Budget 50rb MLBB
                </button>
                <button
                    type="button"
                    @click="sendPrompt('Rekomendasi paket joki rank Mobile Legends')"
                    class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 hover:border-purple-500/50 text-[11px] text-slate-300 hover:text-purple-400 whitespace-nowrap transition cursor-pointer"
                >
                    Joki Mythic MLBB
                </button>
                <button
                    type="button"
                    @click="sendPrompt('Cek status order saya')"
                    class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 hover:border-cyan-500/50 text-[11px] text-slate-300 hover:text-cyan-400 whitespace-nowrap transition cursor-pointer"
                >
                    Status Pesanan Saya
                </button>
                <button
                    type="button"
                    @click="sendPrompt('Apa perbedaan joki gendong dan push biasa?')"
                    class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 hover:border-indigo-500/50 text-[11px] text-slate-300 hover:text-indigo-400 whitespace-nowrap transition cursor-pointer"
                >
                    Perbedaan Joki Gendong vs Akun
                </button>
                <button
                    type="button"
                    @click="sendPrompt('Apakah aman dan ada garansi anti-banned?')"
                    class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 hover:border-emerald-500/50 text-[11px] text-slate-300 hover:text-emerald-400 whitespace-nowrap transition cursor-pointer"
                >
                    Garansi 100% Anti Banned
                </button>
            </div>

            {{-- Input Action Form --}}
            <div class="p-4 bg-slate-950 border-t border-slate-800">
                <form @submit.prevent="sendPrompt()" class="relative flex items-center gap-2">
                    <input
                        type="text"
                        x-model="prompt"
                        maxlength="500"
                        placeholder="Ketik pertanyaan atau kebutuhanmu (misal: 'budget 100rb buat diamond MLBB' atau 'mau joki Valorant')..."
                        class="flex-1 pl-4 pr-16 py-3 rounded-xl bg-slate-900 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:border-cyan-500 focus:outline-none"
                        :disabled="loading"
                    />

                    {{-- Character Counter inside input --}}
                    <span class="absolute right-20 text-[10px] text-slate-500 font-mono pointer-events-none" x-text="prompt.length + '/500'"></span>

                    <x-button type="submit" variant="primary" size="md" ::disabled="loading || !prompt.trim()">
                        Kirim
                    </x-button>
                </form>

                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500 px-1">
                    <span>* AI beroperasi sebagai konsultan cerdas & tidak dapat mengeksekusi pembayaran otomatis atau mengubah akun secara langsung.</span>
                    <span>Rate limit: 30 kueri/menit</span>
                </div>
            </div>
        </div>

        {{-- Featured Joki Services Catalog --}}
        @if(isset($recommendations) && $recommendations->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span>Layanan Joki Populer Rekomendasi AI</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-400 font-mono">Verified Pro</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Daftar paket boosting dengan tingkat kepuasan tertinggi dari ribuan order.</p>
                    </div>
                    <a href="{{ route('joki') }}" class="text-xs font-bold text-cyan-400 hover:text-cyan-300">
                        Lihat Semua Layanan &raquo;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($recommendations as $service)
                        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-cyan-500/50 transition flex flex-col justify-between group">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">{{ $service->game->name ?? 'Game' }}</span>
                                    <span class="text-[11px] text-amber-400 font-bold">★ {{ number_format($service->rating ?? 4.9, 1) }}</span>
                                </div>
                                <h3 class="text-sm font-bold text-white group-hover:text-cyan-400 transition">{{ $service->title }}</h3>
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $service->description }}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-slate-500 block">Mulai dari</span>
                                    <span class="text-sm font-bold font-mono text-cyan-400">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('joki.show', $service) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-cyan-500 hover:text-slate-950 text-xs font-bold text-white transition">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layout>
