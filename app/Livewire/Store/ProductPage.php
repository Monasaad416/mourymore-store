<?php

namespace App\Livewire\Store;

use App\Livewire\Store\Concerns\ManagesBasket;
use App\Models\Product;
use App\Services\Cart;
use App\Services\Wishlist;
use App\Support\Shipping;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.store')]
class ProductPage extends Component
{
    use ManagesBasket;

    public Product $product;

    public int $qty = 1;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['category', 'bookDetail', 'lessons', 'images']);
        $this->remember();
    }

    public function increment(): void
    {
        $this->qty = min($this->product->purchaseLimit(), $this->qty + 1);
    }

    public function decrement(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function add(): void
    {
        $this->addToCart($this->product->id, $this->qty);
    }

    public function buyNow(): void
    {
        if (! $this->product->inStock()) {
            $this->dispatch('toast', message: __('store.product.out_of_stock'));

            return;
        }

        app(Cart::class)->add($this->product->id, $this->qty);
        $this->dispatch('basket-updated');
        $this->redirect(route('checkout'), navigate: true);
    }

    public function save(): void
    {
        $this->toggleWishlist($this->product->id);
    }

    #[On('location-updated')]
    public function refreshLocation(): void
    {
        //
    }

    public function render(): View
    {
        $saved = session('store.governorate', 'Cairo');
        $governorate = is_string($saved) ? $saved : 'Cairo';
        $related = Product::query()
            ->where('category_id', $this->product->category_id)
            ->whereKeyNot($this->product->id)
            ->take(4)
            ->get();

        $name = $this->product->displayName();
        $description = Str::limit(
            strip_tags($this->product->displayDescription()),
            160,
        );

        return view('livewire.store.product-page', [
            'related' => $related,
            'wished' => app(Wishlist::class)->contains($this->product->id),
            'wishedIds' => app(Wishlist::class)->ids(),
            'quote' => Shipping::quote($governorate, (float) $this->product->price),
            'governorate' => $governorate,
            'reviews' => $this->reviews(),
        ])
            ->title(__('store.product.title', ['name' => $name]))
            ->layoutData([
                'description' => $description !== '' ? $description : __('store.meta.description'),
                'canonical' => route('product', $this->product),
                'ogType' => 'product',
                'ogImage' => $this->product->imageUrl(),
            ]);
    }

    /**
     * @return list<array{name: string, city: string, stars: int, text: string}>
     */
    private function reviews(): array
    {
        $reviews = __('store.product.reviews');

        if (! is_array($reviews)) {
            return [];
        }

        $rows = [];

        foreach ($reviews as $review) {
            if (! is_array($review)) {
                continue;
            }

            $rows[] = [
                'name' => is_string($review['name'] ?? null) ? $review['name'] : '',
                'city' => is_string($review['city'] ?? null) ? $review['city'] : '',
                'stars' => (int) ($review['stars'] ?? 0),
                'text' => is_string($review['text'] ?? null) ? $review['text'] : '',
            ];
        }

        return $rows;
    }

    private function remember(): void
    {
        $raw = session('store.recent', []);
        $ids = is_array($raw) ? array_map(intval(...), $raw) : [];
        $ids = array_values(array_unique(array_merge([$this->product->id], $ids)));
        session(['store.recent' => array_slice($ids, 0, 8)]);
    }
}
