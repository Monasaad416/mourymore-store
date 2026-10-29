<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $site = \App\Models\SiteSetting::current();
            $storeName = $site->displayName();
            $pageTitle = filled($title ?? null) ? $title.' · '.$storeName : __('store.meta.fallback_title');
            $pageDescription = $description ?? __('store.meta.description');
            $pageUrl = $canonical ?? url()->current();
            $pageImage = $ogImage ?? url($site->logoUrl());
            $pageType = $ogType ?? 'website';
        @endphp
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <link rel="canonical" href="{{ $pageUrl }}">
        <meta property="og:site_name" content="{{ $storeName }}">
        <meta property="og:type" content="{{ $pageType }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ $pageUrl }}">
        <meta property="og:image" content="{{ $pageImage }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">
        <meta name="twitter:image" content="{{ $pageImage }}">
        <link rel="icon" href="{{ $site->faviconUrl() }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ $site->faviconUrl() }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800|fraunces:500,600,700|manrope:400,500,600,700,800" rel="stylesheet">
        @fonts
        @include('partials.theme-colors')
        {{-- Keep gold yellow and nile brown while accent/deal stay red from the logo and cover. --}}
        <style>
            :root {
                --theme-accent: #dc2626;
                --theme-on-accent: #ffffff;
                --color-accent: #dc2626;
                --color-accent-foreground: #ffffff;
                --theme-deal: #dc2626;
                --theme-star: #f0c040;
                --theme-ink: #1c1408;
                --theme-gold-300: #ffe7a3;
                --theme-gold-400: #f0d060;
                --theme-gold-500: #f0c040;
                --theme-gold-600: #d9a81a;
                --theme-nile-50: #fff8e8;
                --theme-nile-100: #fff1cc;
                --theme-nile-200: #ffe4a3;
                --theme-nile-700: #6b4a16;
                --theme-nile-800: #3a2a14;
                --theme-nile-900: #1c1408;
                --theme-nile-950: #120d08;
                --theme-sand-50: #fffaf0;
                --theme-sand-100: #fff3d6;
                --theme-sand-200: #ffe7b0;
            }

            [x-cloak] { display: none !important; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="store-shell min-h-screen antialiased">
        <livewire:store.nav />

        <main>
            {{ $slot }}
        </main>

        @include('partials.store.footer')

        <div
            x-data="{ show: false, message: '' }"
            x-on:toast.window="message = ($event.detail && $event.detail.message) ? $event.detail.message : $el.dataset.fallback; show = true; setTimeout(() => show = false, 2400)"
            x-show="show"
            x-transition.opacity
            x-cloak
            data-fallback="{{ __('store.toast.saved') }}"
            class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-full bg-ink px-4 py-2.5 text-sm font-medium text-white shadow-xl"
        >
            <span x-text="message"></span>
        </div>
    </body>
</html>
