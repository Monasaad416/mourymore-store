<?php

namespace App\Livewire\Store;

use App\Livewire\Store\Concerns\ManagesBasket;
use App\Services\Cart;
use App\Support\Shipping;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.store')]
class CartPage extends Component
{
    use ManagesBasket;

    public function setQty(int $productId, int $qty): void
    {
        app(Cart::class)->set($productId, $qty);
        $this->dispatch('basket-updated');
    }

    public function remove(int $productId): void
    {
        app(Cart::class)->remove($productId);
        $this->dispatch('basket-updated');
        $this->dispatch('toast', message: __('store.cart.removed'));
    }

    public function saveForLater(int $productId): void
    {
        $this->toggleWishlist($productId);
        app(Cart::class)->remove($productId);
        $this->dispatch('basket-updated');
    }

    #[On('location-updated')]
    public function refreshLocation(): void
    {
        //
    }

    public function render(): View
    {
        $cart = app(Cart::class);
        $saved = session('store.governorate', 'Cairo');
        $governorate = is_string($saved) ? $saved : 'Cairo';
        $lines = $cart->lines();
        $subtotal = $cart->subtotal();

        return view('livewire.store.cart-page', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'quote' => Shipping::quote($governorate, $subtotal),
            'governorate' => $governorate,
            'count' => $cart->count(),
            'ships' => $lines->contains(fn (array $line): bool => $line['product']->tracksStock()),
        ])->title(__('store.cart.meta_title'));
    }
}
