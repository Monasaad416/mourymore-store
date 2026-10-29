<?php

namespace App\Livewire\Store;

use App\Livewire\Store\Concerns\ManagesBasket;
use App\Services\Cart;
use App\Services\Wishlist;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class WishlistPage extends Component
{
    use ManagesBasket;

    public function moveAllToCart(): void
    {
        $cart = app(Cart::class);

        foreach (app(Wishlist::class)->ids() as $id) {
            $cart->add($id, 1);
        }

        $this->dispatch('basket-updated');
        $this->dispatch('toast', message: __('store.wishlist.moved'));
    }

    public function render(): View
    {
        $products = app(Wishlist::class)->products();

        return view('livewire.store.wishlist-page', [
            'products' => $products,
            'wishedIds' => $products->pluck('id')->all(),
        ])->title(__('store.wishlist.meta_title'));
    }
}
