<?php

namespace App\Livewire\Store\Concerns;

use App\Models\Product;
use App\Services\Cart;
use App\Services\Wishlist;

trait ManagesBasket
{
    public function addToCart(int $productId, int $qty = 1): void
    {
        $product = Product::query()->find($productId);

        if (! $product instanceof Product || ! $product->inStock()) {
            $this->dispatch('toast', message: __('store.toast.out'));

            return;
        }

        app(Cart::class)->add($productId, $qty);
        $this->dispatch('basket-updated');
        $this->dispatch('toast', message: __('store.toast.added'));
    }

    public function toggleWishlist(int $productId): void
    {
        $added = app(Wishlist::class)->toggle($productId);
        $this->dispatch('basket-updated');
        $this->dispatch('toast', message: $added ? __('store.toast.wished') : __('store.toast.unwished'));
    }
}
