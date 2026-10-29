<?php

namespace App\Livewire\Store;

use App\Services\Cart;
use App\Services\Wishlist;
use App\Support\Shipping;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Nav extends Component
{
    public string $governorate = 'Cairo';

    public function mount(): void
    {
        $saved = session('store.governorate', 'Cairo');
        $this->governorate = is_string($saved) ? $saved : 'Cairo';
    }

    public function updatedGovernorate(string $value): void
    {
        if (! in_array($value, Shipping::governorates(), true)) {
            return;
        }

        if (session('store.governorate') === $value) {
            return;
        }

        session(['store.governorate' => $value]);
        $this->dispatch('location-updated');
    }

    #[On('basket-updated')]
    public function refreshBasket(): void
    {
        //
    }

    #[On('location-updated')]
    public function refreshLocation(): void
    {
        $saved = session('store.governorate', 'Cairo');
        $this->governorate = is_string($saved) ? $saved : 'Cairo';
    }

    public function render(): View
    {
        return view('livewire.store.nav', [
            'cartCount' => app(Cart::class)->count(),
            'wishCount' => app(Wishlist::class)->count(),
            'governorates' => Shipping::governorates(),
        ]);
    }
}
