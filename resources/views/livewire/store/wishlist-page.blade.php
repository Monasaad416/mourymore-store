<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl">{{ __('store.wishlist.title') }}</h1>
            <p class="mt-1 text-sm text-ink/55">{{ __('store.wishlist.lead') }}</p>
        </div>
        @if ($products->isNotEmpty())
            <button type="button" wire:click="moveAllToCart" class="rounded-full bg-nile-900 px-5 py-2.5 text-sm font-semibold text-white">
                {{ __('store.wishlist.move_all') }}
            </button>
        @endif
    </div>

    @if ($products->isEmpty())
        <div class="mt-8 rounded-[2rem] bg-white px-6 py-16 text-center ring-1 ring-black/5">
            <p class="font-display text-3xl">{{ __('store.wishlist.empty_title') }}</p>
            <p class="mt-2 text-sm text-ink/60">{{ __('store.wishlist.empty_text') }}</p>
            <a href="{{ route('shop') }}" class="mt-6 inline-flex rounded-full bg-gold-500 px-5 py-3 text-sm font-semibold text-white" wire:navigate>{{ __('store.wishlist.find') }}</a>
        </div>
    @else
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-store.product-card :product="$product" :wished="true" wire:key="wish-{{ $product->id }}" />
            @endforeach
        </div>
    @endif
</div>
