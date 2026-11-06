@props([
    'title' => null,
    'description' => 'Platform Jasa Joki Game & Akademik Terpercaya #1 di Indonesia. Bergaransi 100% Anti Banned, Cepat, dan Aman.',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Instant Anti-FOUC Theme Initializer --}}
    <script>
        (function() {
            try {
                const stored = localStorage.getItem('theme');
                if (stored === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (stored === 'light') {
                    document.documentElement.classList.remove('dark');
                } else {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.add('dark');
                    }
                }
            } catch (e) {}
        })();
    </script>

    <title>{{ $title ? $title : 'AETHEREL — Top Up Game Termurah & Tercepat Resmi' }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="Aetherel, top up game murah, joki mlbb, topup roblox, free fire, joki valorant, genshin impact, gaming store">
    <meta name="author" content="Aetherel">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon with New Aetherel Logo --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:site_name" content="Aetherel">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ? $title : 'AETHEREL — Top Up Game Termurah & Tercepat Resmi' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="id_ID">

    {{-- Twitter / X Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $title ? $title : 'AETHEREL — Top Up Game Termurah & Tercepat Resmi' }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    {{-- Vite Scripts & Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#14131a] text-slate-900 dark:text-slate-100 antialiased selection:bg-yellow-400 selection:text-slate-950 flex flex-col relative transition-colors duration-200">
    {{-- Global Reusable Toast Notification Container --}}
    <x-toast />

    {{-- Top Reusable Navbar --}}
    <x-navbar />

    {{-- Main Content Slot --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Global Reusable Footer --}}
    <x-footer />

    {{-- Global Floating Support / Assistant --}}
    @if(!request()->routeIs('ai-assistant'))
        <x-floating-ai />
    @endif
</body>
</html>
