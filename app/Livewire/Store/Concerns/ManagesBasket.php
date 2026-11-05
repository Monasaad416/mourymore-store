<?php

namespace App\Livewire\Store\Concerns;

use App\Enums\BookFormat;
use App\Livewire\Concerns\NotifiesCustomer;
use App\Models\Product;
use App\Services\Cart;
use App\Services\Wishlist;

trait ManagesBasket
{
    use NotifiesCustomer;

    public function addToCart(int $productId, int $qty = 1, ?string $format = null): void
    {
        $product = Product::query()->with('bookDetail')->find($productId);
        $bookFormat = is_string($format) ? BookFormat::tryFrom($format) : null;

        if (
            ! $product instanceof Product
            || ! $product->canPurchaseFormat($bookFormat, max(1, $qty))
        ) {
            $this->notifyCustomer(__('store.toast.out'));

            return;
        }

        app(Cart::class)->add($productId, $qty, $format);
        $this->dispatch('basket-updated');
        $this->notifyCustomer(__('store.toast.added'));
    }

    public function toggleWishlist(int $productId): void
    {
        $added = app(Wishlist::class)->toggle($productId);
        $this->dispatch('basket-updated');
        $this->notifyCustomer($added ? __('store.toast.wished') : __('store.toast.unwished'));
    }
}
