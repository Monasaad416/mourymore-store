<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ \App\Models\SiteSetting::current()->faviconUrl() }}" type="image/png">
<link rel="apple-touch-icon" href="{{ \App\Models\SiteSetting::current()->faviconUrl() }}">

@fonts

@include('partials.theme-colors')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
