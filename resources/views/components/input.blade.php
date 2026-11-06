@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'icon' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
])

<div class="w-full">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 dark:text-rose-400">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500">
                {!! $icon !!}
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full rounded-xl bg-white dark:bg-slate-950/70 border ' . 
                ($error || $errors->has($name) ? 'border-rose-500/80 focus:border-rose-400 focus:ring-rose-500/30' : 'border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 focus:border-cyan-500 focus:ring-cyan-500/30') . 
                ' ' . ($icon ? 'pl-10' : 'pl-4') . ' pr-4 py-3 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all duration-200 focus:outline-none focus:ring-2 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:text-slate-400 dark:disabled:text-slate-600 disabled:cursor-not-allowed'
            ]) }}
        />
    </div>

    @if($hint && !$error && !$errors->has($name))
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif

    @if($error || $errors->has($name))
        <p class="mt-1.5 text-xs text-rose-400 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $error ?? $errors->first($name) }}
        </p>
    @endif
</div>
