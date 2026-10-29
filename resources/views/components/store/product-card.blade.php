@props(['product', 'wished' => false])

@php
    $badgeKey = is_string($product->badge) ? $product->badge : '';
    $badge = in_array($badgeKey, ['express', 'bestseller', 'deal', 'new'], true)
        ? __('store.badges.'.$badgeKey)
        : null;
@endphp

<article {{ $attributes->class('group flex h-full flex-col rounded-3xl bg-white p-3 shadow-[0_10px_30px_-24px_rgba(26,34,51,0.55)] ring-1 ring-black/5') }}>
    <div class="relative">
        <button
            type="button"
            wire:click="toggleWishlist({{ $product->id }})"
            class="absolute top-3 end-3 z-10 grid size-9 place-items-center rounded-full bg-white/95 text-ink shadow-sm ring-1 ring-black/5 transition hover:text-deal"
            aria-label="{{ $wished ? __('store.card.remove') : __('store.card.save') }}"
        >
            <svg class="size-4 {{ $wished ? 'fill-deal text-deal' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linejoin="round" d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 4.6-7 9-7 9Z" />
            </svg>
        </button>
        <a href="{{ route('product', $product) }}" wire:navigate class="block overflow-hidden rounded-2xl bg-sand-100">
            <img
                src="{{ $product->imageUrl() }}"
                alt="{{ $product->displayName() }}"
                class="aspect-square w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            >
        </a>
        @if ($badge)
            <span class="absolute top-3 start-3 rounded-full bg-nile-900/90 px-2.5 py-1 text-[11px] font-semibold text-gold-300">
                {{ $badge }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col px-1 pt-3">
        <p class="text-[11px] font-semibold text-nile-700 uppercase">{{ $product->brand }}</p>
        <a href="{{ route('product', $product) }}" wire:navigate class="mt-1 line-clamp-2 text-sm font-semibold leading-5 text-ink hover:text-nile-800">
            {{ $product->displayName() }}
        </a>
        <div class="mt-2 flex items-center gap-2">
            <x-store.stars :rating="$product->rating" />
            <span class="text-xs text-ink/50">{{ number_format($product->reviews_count) }}</span>
        </div>
        <div class="mt-3 flex flex-wrap items-baseline gap-2">
            <p class="text-lg font-semibold tracking-tight">{{ $product->formattedPrice() }}</p>
            @if ($product->discountPercent())
                <p class="text-sm text-ink/40 line-through">{{ $product->formattedComparePrice() }}</p>
                <p class="text-xs font-semibold text-deal">-{{ $product->discountPercent() }}%</p>
            @endif
        </div>
        <p class="mt-1 text-xs text-nile-800">
            {{ $product->inStock() ? __('store.card.express') : __('store.card.out') }}
        </p>
        <div class="mt-auto pt-3">
            @if ($product->inStock())
                <button
                    type="button"
                    wire:click="addToCart({{ $product->id }})"
                    wire:loading.attr="disabled"
                    wire:target="addToCart({{ $product->id }})"
                    class="w-full rounded-full bg-nile-900 py-2.5 text-sm font-semibold text-white transition hover:bg-nile-800 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove wire:target="addToCart({{ $product->id }})">{{ __('store.card.add') }}</span>
                    <span wire:loading wire:target="addToCart({{ $product->id }})">{{ __('store.card.adding') }}</span>
                </button>
            @else
                <button type="button" disabled class="w-full cursor-not-allowed rounded-full bg-sand-200 py-2.5 text-sm font-semibold text-ink/40">
                    {{ __('store.card.out') }}
                </button>
            @endif
        </div>
    </div>
</article>
