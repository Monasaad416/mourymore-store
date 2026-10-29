<div class="mx-auto max-w-7xl px-4 py-6">
    <nav class="flex flex-wrap items-center gap-2 text-sm text-ink/50">
        <a href="{{ route('home') }}" class="hover:text-ink" wire:navigate>{{ __('store.product.home') }}</a>
        <span>/</span>
        @if ($product->kind === \App\Enums\ProductKind::Book)
            <a href="{{ route('shop', ['department' => 'books']) }}" class="hover:text-ink" wire:navigate>{{ __('store.nav.books') }}</a>
            <span>/</span>
        @elseif ($product->kind === \App\Enums\ProductKind::Course)
            <a href="{{ route('shop', ['department' => 'courses']) }}" class="hover:text-ink" wire:navigate>{{ __('store.nav.courses') }}</a>
            <span>/</span>
        @else
            <a href="{{ route('shop') }}" class="hover:text-ink" wire:navigate>{{ __('store.nav.products') }}</a>
            <span>/</span>
        @endif
        <span class="text-ink">{{ $product->displayName() }}</span>
    </nav>

    <div class="mt-5 grid items-start gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
        <div class="overflow-hidden rounded-[2rem] bg-white ring-1 ring-black/5">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->displayName() }}" class="aspect-[4/5] w-full object-cover sm:aspect-[5/4]">
        </div>

        <div class="lg:sticky lg:top-28">
            <p class="text-xs font-semibold text-nile-700 uppercase">{{ $product->bookDetail?->displayAuthor() ?? $product->brand }}</p>
            <h1 class="font-display mt-2 text-4xl leading-tight">{{ $product->displayName() }}</h1>
            <div class="mt-3 flex items-center gap-2">
                <x-store.stars :rating="$product->rating" />
                <span class="text-sm text-ink/55">{{ __('store.product.reviews_count', ['rating' => number_format((float) $product->rating, 1), 'count' => number_format($product->reviews_count)]) }}</span>
            </div>

            <div class="mt-5 flex flex-wrap items-end gap-3">
                <p class="text-3xl font-semibold tracking-tight">{{ $product->formattedPrice() }}</p>
                @if ($product->discountPercent())
                    <p class="text-lg text-ink/35 line-through">{{ $product->formattedComparePrice() }}</p>
                    <p class="rounded-full bg-deal/10 px-2.5 py-1 text-sm font-semibold text-deal">{{ __('store.product.save_percent', ['percent' => $product->discountPercent()]) }}</p>
                @endif
            </div>
            @php($taxSettings = \App\Models\SiteSetting::current())
            <p class="mt-1 text-xs text-ink/45">
                @if ($taxSettings->taxPercent() > 0)
                    {{ $taxSettings->pricesIncludeTax()
                        ? __('store.product.vat_included', ['percent' => rtrim(rtrim(number_format($taxSettings->taxPercent(), 2, '.', ''), '0'), '.')])
                        : __('store.product.vat_excluded', ['percent' => rtrim(rtrim(number_format($taxSettings->taxPercent(), 2, '.', ''), '0'), '.')]) }}
                @else
                    {{ __('store.product.ships') }}
                @endif
            </p>

            @if ($product->tracksStock())
                <div class="mt-5 rounded-3xl bg-sand-50 p-4 ring-1 ring-sand-200">
                    <p class="text-sm font-semibold text-nile-800">{{ $quote['note'] }}</p>
                    <p class="mt-1 text-sm text-ink/70">{{ __('store.product.deliver_to') }} <span class="font-semibold text-ink">{{ \App\Support\Shipping::label($governorate) }}</span> · {{ $quote['eta'] }}</p>
                    <p class="mt-2 text-xs text-ink/50">{{ __('store.product.change_city') }}</p>
                </div>
            @endif

            <ul class="mt-5 space-y-2 text-sm">
                @foreach ($product->displayHighlights() as $highlight)
                    <li class="flex gap-2">
                        <span class="mt-1 size-1.5 shrink-0 rounded-full bg-gold-500"></span>
                        <span>{{ $highlight }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 flex items-center gap-3">
                <div class="flex items-center rounded-full bg-white ring-1 ring-black/10">
                    <button type="button" wire:click="decrement" class="grid size-11 place-items-center text-lg" aria-label="{{ __('store.product.decrease') }}">−</button>
                    <span class="w-8 text-center text-sm font-semibold">{{ $qty }}</span>
                    <button type="button" wire:click="increment" class="grid size-11 place-items-center text-lg" aria-label="{{ __('store.product.increase') }}">+</button>
                </div>
                <p class="text-sm {{ $product->inStock() ? 'text-nile-800' : 'text-deal' }}">
                    @if (! $product->tracksStock())
                        {{ __('store.product.available') }}
                    @elseif ($product->inStock())
                        {{ __('store.product.in_stock', ['count' => $product->availableQuantity()]) }}
                    @else
                        {{ __('store.product.out_soon') }}
                    @endif
                </p>
            </div>

            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add" @disabled(! $product->inStock()) class="rounded-full bg-gold-500 py-3 text-sm font-semibold text-white hover:bg-gold-600 disabled:cursor-not-allowed disabled:bg-sand-200 disabled:text-ink/40">
                    {{ __('store.product.add') }}
                </button>
                <button type="button" wire:click="buyNow" @disabled(! $product->inStock()) class="rounded-full bg-nile-900 py-3 text-sm font-semibold text-white hover:bg-nile-800 disabled:cursor-not-allowed disabled:bg-sand-200 disabled:text-ink/40">
                    {{ __('store.product.buy') }}
                </button>
            </div>
            <button type="button" wire:click="save" class="mt-3 text-sm font-semibold text-nile-800">
                {{ $wished ? __('store.product.saved') : __('store.product.save') }}
            </button>

            <p class="mt-6 text-xs leading-5 text-ink/50">{{ __('store.product.sold_by') }}</p>
        </div>
    </div>

    <section class="mt-12 grid gap-8 lg:grid-cols-[1.4fr_0.8fr]">
        <div>
            <h2 class="font-display text-3xl">{{ __('store.product.about') }}</h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-ink/75">{{ $product->displayDescription() }}</p>
            @if ($product->lessons->isNotEmpty())
                <ol class="mt-6 max-w-2xl space-y-3">
                    @foreach ($product->lessons as $lesson)
                        @php($open = $lesson->is_preview || $hasCourseAccess)
                        <li class="flex items-baseline justify-between gap-4 border-t border-sand-200 pt-3 text-sm">
                            <span>{{ $lesson->displayTitle() }}</span>
                            <span class="shrink-0 text-ink/50">
                                @if ($open && $lesson->duration_minutes)
                                    {{ $lesson->duration_minutes }} {{ __('store.product.minutes') }}
                                @elseif (! $open)
                                    {{ __('store.product.lesson_locked') }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
        <div class="rounded-[2rem] bg-white p-6 ring-1 ring-black/5">
            <h2 class="font-semibold">{{ __('store.product.shoppers') }}</h2>
            <div class="mt-4 space-y-4">
                @foreach ($reviews as $review)
                    <figure class="border-t border-sand-200 pt-4 first:border-0 first:pt-0">
                        <div class="flex items-center justify-between gap-3">
                            <figcaption class="text-sm font-semibold">{{ $review['name'] }} · {{ $review['city'] }}</figcaption>
                            <x-store.stars :rating="$review['stars']" />
                        </div>
                        <p class="mt-1 text-sm leading-6 text-ink/70">{{ $review['text'] }}</p>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mt-12">
            <h2 class="font-display text-3xl">{{ __('store.product.more', ['name' => $product->category?->displayName() ?? __('store.product.aisle')]) }}</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($related as $item)
                    <x-store.product-card :product="$item" :wished="in_array($item->id, $wishedIds, true)" wire:key="related-{{ $item->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>
