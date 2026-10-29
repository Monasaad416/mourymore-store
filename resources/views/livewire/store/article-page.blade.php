<article class="mx-auto max-w-3xl px-4 py-10">
    <a href="{{ route('articles') }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.content.back_articles') }}</a>
    <p class="mt-6 text-xs text-ink/45">{{ $article->published_at?->translatedFormat('j F Y') }}</p>
    <h1 class="font-display mt-2 text-4xl leading-tight">{{ $article->displayTitle() }}</h1>
    <p class="mt-4 text-lg leading-8 text-ink/70">{{ $article->displayExcerpt() }}</p>
    @if ($article->imageUrl())
        <img src="{{ $article->imageUrl() }}" alt="" class="mt-6 w-full rounded-[1.6rem] object-cover">
    @endif
    <div class="mt-8 text-base leading-8 whitespace-pre-line text-ink/85">{{ $article->displayBody() }}</div>
</article>
