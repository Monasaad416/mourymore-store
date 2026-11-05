<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $settings = \App\Models\SiteSetting::current();
            $storeName = $settings->displayName();
            $pageTitle = filled($title ?? null) ? $title.' · '.$storeName : $storeName;
        @endphp
        <title>{{ $pageTitle }}</title>
        <link rel="icon" href="{{ $settings->faviconUrl() }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ $settings->faviconUrl() }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800|fraunces:500,600,700|manrope:400,500,600,700,800" rel="stylesheet">
        @fonts
        @include('partials.theme-colors')
        <style>
            :root {
                --theme-accent: #dc2626;
                --theme-on-accent: #ffffff;
                --color-accent: #dc2626;
                --color-accent-foreground: #ffffff;
                --theme-deal: #dc2626;
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
        <div class="grid min-h-dvh lg:grid-cols-2">
            <aside class="relative hidden min-h-dvh lg:block">
                @if (filled($settings->coverUrl()))
                    <img
                        src="{{ $settings->coverUrl() }}"
                        alt=""
                        class="absolute inset-0 size-full object-fill"
                    >
                @endif
            </aside>

            <main class="relative flex min-h-dvh items-center justify-center bg-sand-50 px-4 py-10 sm:px-8">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(240,192,64,0.12),transparent_40%)]"></div>
                <div class="relative w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-6 flex flex-col items-center gap-2" wire:navigate>
                        <span class="flex h-12 items-center justify-center">
                            <x-app-logo-icon class="h-12 w-auto max-w-[10rem]" />
                        </span>
                        <span class="text-sm font-semibold text-nile-900">{{ $storeName }}</span>
                    </a>

                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-sm text-ink/50">
                        <a href="{{ route('home') }}" class="font-medium text-nile-800 hover:text-nile-900" wire:navigate>
                            {{ __('store.auth.back_home') }}
                        </a>
                    </p>
                </div>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
