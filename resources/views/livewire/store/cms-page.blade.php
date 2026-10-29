<article class="mx-auto max-w-3xl px-4 py-10">
    <p class="text-xs font-semibold uppercase tracking-wide text-nile-800/70">{{ __('store.pages.kicker') }}</p>
    <h1 class="font-display mt-3 text-4xl leading-tight">{{ $page->displayTitle() }}</h1>
    <div class="mt-8 text-base leading-8 whitespace-pre-line text-ink/85">{{ $page->displayBody() }}</div>
</article>
