<article class="mx-auto max-w-3xl px-4 py-10">
    <a href="{{ route('news.index') }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.content.back_news') }}</a>
    <p class="mt-6 text-xs text-ink/45">{{ $item->published_at?->translatedFormat('j F Y') }}</p>
    <h1 class="font-display mt-2 text-4xl leading-tight">{{ $item->displayTitle() }}</h1>
    @if ($item->imageUrl())
        <img src="{{ $item->imageUrl() }}" alt="" class="mt-6 w-full rounded-[1.6rem] object-cover">
    @endif
    <div class="mt-8 text-base leading-8 whitespace-pre-line text-ink/85">{{ $item->displayBody() }}</div>
</article>
