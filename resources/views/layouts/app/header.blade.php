<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('home') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="user" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                    {{ __('store.account.profile') }}
                </flux:navbar.item>
                <flux:navbar.item icon="shopping-bag" :href="route('orders.index')" :current="request()->routeIs('orders.*')" wire:navigate>
                    {{ __('store.nav.orders') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('store.account.kicker')">
                    <flux:sidebar.item icon="user" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                        {{ __('store.account.profile') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('orders.index')" :current="request()->routeIs('orders.*')" wire:navigate>
                        {{ __('store.nav.orders') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
