<style data-theme>
    :root {
@foreach (\App\Support\Theme::variables() as $name => $value)
        --{{ $name }}: {{ $value }};
@endforeach
        --color-accent: var(--theme-accent);
        --color-accent-content: var(--theme-accent);
        --color-accent-foreground: var(--theme-on-accent);
    }
</style>
