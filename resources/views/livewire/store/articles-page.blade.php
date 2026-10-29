<div class="mx-auto max-w-3xl px-4 py-10">
    <p class="text-xs font-semibold text-nile-800 uppercase">{{ __('store.content.articles_kicker') }}</p>
    <h1 class="font-display mt-2 text-4xl">{{ __('store.content.articles_title') }}</h1>

    @if ($articles->isEmpty())
        <p class="mt-8 text-ink/60">{{ __('store.content.articles_empty') }}</p>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($articles as $article)
                <a href="{{ route('articles.show', $article->slug) }}" wire:navigate class="block rounded-[1.6rem] bg-white p-6 ring-1 ring-black/5 hover:shadow-lg">
                    <p class="text-xs text-ink/45">{{ $article->published_at?->translatedFormat('j F Y') }}</p>
                    <h2 class="font-display mt-2 text-2xl">{{ $article->displayTitle() }}</h2>
                    <p class="mt-3 text-sm leading-6 text-ink/70">{{ $article->displayExcerpt() }}</p>
                    <span class="mt-4 inline-block text-sm font-semibold text-nile-800">{{ __('store.content.read') }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
