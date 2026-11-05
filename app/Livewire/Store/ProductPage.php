<?php

namespace App\Livewire\Store;

use App\Enums\BookFormat;
use App\Enums\ProductKind;
use App\Livewire\Store\Concerns\ManagesBasket;
use App\Models\Product;
use App\Services\Cart;
use App\Services\Wishlist;
use App\Support\Money;
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

    public string $format = '';

    public function mount(Product $product): void
    {
        $this->product = $product->load(['category', 'bookDetail', 'lessons', 'images']);
        $defaultFormat = $this->product->defaultBookFormat();
        $this->format = $defaultFormat instanceof BookFormat
            ? $defaultFormat->value
            : ($this->product->kind === ProductKind::Book ? BookFormat::Print->value : '');
        $this->remember();
    }

    public function updatedFormat(string $value): void
    {
        $chosen = BookFormat::tryFrom($value);

        if ($chosen === null || ! $this->product->canPurchaseFormat($chosen, 1)) {
            $fallback = $this->product->defaultBookFormat();
            $this->format = $fallback instanceof BookFormat ? $fallback->value : '';

            return;
        }

        $this->qty = min($this->qty, $this->product->purchaseLimitFor($chosen));
    }

    public function increment(): void
    {
        $limit = $this->product->purchaseLimitFor($this->selectedFormat());
        $this->qty = min($limit, $this->qty + 1);
    }

    public function decrement(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function add(): void
    {
        $format = $this->selectedFormat();
        $this->addToCart(
            $this->product->id,
            $this->qty,
            $format?->value,
        );
    }

    public function buyNow(): void
    {
        $format = $this->selectedFormat();

        if (! $this->product->canPurchaseFormat($format, $this->qty)) {
            $this->notifyCustomer(__('store.product.out_of_stock'));

            return;
        }

        app(Cart::class)->add($this->product->id, $this->qty, $format?->value);
        $this->dispatch('basket-updated');
        $this->redirect(route('checkout'));
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

        $format = $this->selectedFormat();
        $unitPrice = $this->product->priceForFormat($format);
        $canBuy = $this->product->canPurchaseFormat($format, max(1, $this->qty));
        $needsShipping = $format !== BookFormat::Digital && $this->product->tracksStock();

        $name = $this->product->displayName();
        $description = Str::limit(
            strip_tags($this->product->displayDescription()),
            160,
        );

        return view('livewire.store.product-page', [
            'related' => $related,
            'wished' => app(Wishlist::class)->contains($this->product->id),
            'wishedIds' => app(Wishlist::class)->ids(),
            'quote' => Shipping::quote($governorate, $unitPrice),
            'governorate' => $governorate,
            'reviews' => $this->reviews(),
            'formats' => $this->product->availableBookFormats(),
            'selectedFormat' => $format,
            'unitPrice' => $unitPrice,
            'formattedUnitPrice' => Money::format($unitPrice),
            'canBuy' => $canBuy,
            'needsShipping' => $needsShipping,
        ])
            ->title(__('store.product.title', ['name' => $name]))
            ->layoutData([
                'description' => $description !== '' ? $description : __('store.meta.description'),
                'canonical' => route('product', $this->product),
                'ogType' => 'product',
                'ogImage' => $this->product->imageUrl(),
            ]);
    }

    private function selectedFormat(): ?BookFormat
    {
        if ($this->product->kind !== ProductKind::Book) {
            return null;
        }

        return BookFormat::tryFrom($this->format) ?? $this->product->defaultBookFormat();
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
                'name' => (string) ($review['name'] ?? ''),
                'city' => (string) ($review['city'] ?? ''),
                'stars' => (int) ($review['stars'] ?? 5),
                'text' => (string) ($review['text'] ?? ''),
            ];
        }

        return $rows;
    }

    private function remember(): void
    {
        $ids = session('store.recent', []);
        $ids = is_array($ids) ? $ids : [];
        $ids = array_values(array_unique(array_merge([$this->product->id], array_map('intval', $ids))));
        session(['store.recent' => array_slice($ids, 0, 12)]);
    }
}
