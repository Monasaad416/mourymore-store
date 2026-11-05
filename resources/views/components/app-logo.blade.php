@props([
    'sidebar' => false,
])

@php
    $settings = \App\Models\SiteSetting::current();
    $name = $settings->displayName();
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white">
            <x-app-logo-icon class="size-5" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white">
            <x-app-logo-icon class="size-5" />
        </x-slot>
    </flux:brand>
@endif
