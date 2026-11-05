@php
    $settings = \App\Models\SiteSetting::current();
@endphp
<img
    src="{{ $settings->logoUrl() }}"
    alt="{{ $settings->displayName() }}"
    {{ $attributes->class(['object-contain']) }}
>
