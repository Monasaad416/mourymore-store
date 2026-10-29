<div class="mx-auto max-w-3xl px-4 py-10">
    <p class="text-xs font-semibold text-nile-800 uppercase">{{ __('store.content.news_kicker') }}</p>
    <h1 class="font-display mt-2 text-4xl">{{ __('store.content.news_title') }}</h1>

    @if ($items->isEmpty())
        <p class="mt-8 text-ink/60">{{ __('store.content.news_empty') }}</p>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($items as $item)
                <a href="{{ route('news.show', $item->slug) }}" wire:navigate class="block rounded-[1.6rem] bg-white p-6 ring-1 ring-black/5 hover:shadow-lg">
                    <p class="text-xs text-ink/45">{{ $item->published_at?->translatedFormat('j F Y') }}</p>
                    <h2 class="font-display mt-2 text-2xl">{{ $item->displayTitle() }}</h2>
                    @if ($item->displayExcerpt() !== '')
                        <p class="mt-3 text-sm leading-6 text-ink/70">{{ $item->displayExcerpt() }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
