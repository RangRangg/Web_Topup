@props([])

<div
    x-data="{
        toasts: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.remove(id);
            }, 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-init="
        @if(session('success'))
            add('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            add('{{ session('error') }}', 'danger');
        @endif
        @if(session('info'))
            add('{{ session('info') }}', 'info');
        @endif
    "
    x-on:toast.window="add($event.detail.message, $event.detail.type)"
    class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="pointer-events-auto flex items-center justify-between gap-3 p-4 rounded-xl border backdrop-blur-lg shadow-xl transition-all"
            :class="{
                'bg-white/95 dark:bg-slate-900/95 border-emerald-500/50 text-emerald-800 dark:text-emerald-200 shadow-emerald-500/10': toast.type === 'success',
                'bg-white/95 dark:bg-slate-900/95 border-rose-500/50 text-rose-800 dark:text-rose-200 shadow-rose-500/10': toast.type === 'danger',
                'bg-white/95 dark:bg-slate-900/95 border-cyan-500/50 text-cyan-800 dark:text-cyan-200 shadow-cyan-500/10': toast.type === 'info',
                'bg-white/95 dark:bg-slate-900/95 border-amber-500/50 text-amber-800 dark:text-amber-200 shadow-amber-500/10': toast.type === 'warning'
            }"
        >
            <div class="flex items-center gap-2.5">
                <template x-if="toast.type === 'success'">
                    <span class="p-1 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </template>
                <template x-if="toast.type === 'danger'">
                    <span class="p-1 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                </template>
                <template x-if="toast.type === 'info'">
                    <span class="p-1 rounded-lg bg-cyan-500/20 text-cyan-600 dark:text-cyan-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </template>
                <p class="text-xs font-semibold text-slate-900 dark:text-white" x-text="toast.message"></p>
            </div>

            <button
                type="button"
                x-on:click="remove(toast.id)"
                class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition p-1"
                aria-label="Tutup notifikasi"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
