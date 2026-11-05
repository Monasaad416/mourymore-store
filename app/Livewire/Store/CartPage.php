<?php

namespace App\Livewire\Store;

use App\Enums\BookFormat;
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

    public function setQty(int $productId, int $qty, string $format = 'default'): void
    {
        app(Cart::class)->set($productId, $qty, $format === 'default' ? null : $format);
        $this->dispatch('basket-updated');
    }

    public function remove(int $productId, string $format = 'default'): void
    {
        app(Cart::class)->remove($productId, $format);
        $this->dispatch('basket-updated');
        $this->notifyCustomer(__('store.cart.removed'));
    }

    public function saveForLater(int $productId, string $format = 'default'): void
    {
        $this->toggleWishlist($productId);
        app(Cart::class)->remove($productId, $format);
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
            'ships' => $lines->contains(function (array $line): bool {
                return ($line['format'] ?? null) !== BookFormat::Digital
                    && $line['product']->tracksStock();
            }),
        ])->title(__('store.cart.meta_title'));
    }
}
