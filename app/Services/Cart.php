<?php

namespace App\Services;

use App\Models\Cart as CartModel;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class Cart
{
    public const COOKIE = 'store_cart';

    private ?CartModel $cart = null;

    private bool $resolved = false;

    public function __construct(private Request $request) {}

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        $cart = $this->current();

        if (! $cart instanceof CartModel) {
            return [];
        }

        $items = [];

        foreach ($cart->items()->get(['product_id', 'qty']) as $item) {
            $items[$item->product_id] = max(1, $item->qty);
        }

        return $items;
    }

    public function add(int $productId, int $qty = 1): void
    {
        $product = Product::query()->find($productId);

        if (! $product instanceof Product || ! $product->inStock()) {
            return;
        }

        $cart = $this->ensure();
        $current = (int) $cart->items()->where('product_id', $productId)->value('qty');
        $next = min($product->purchaseLimit(), $current + max(1, $qty));

        $cart->items()->updateOrCreate(
            ['product_id' => $productId],
            ['qty' => max(1, $next)],
        );
        $this->extend($cart);
    }

    public function set(int $productId, int $qty): void
    {
        if ($qty < 1) {
            $this->remove($productId);

            return;
        }

        $product = Product::query()->find($productId);

        if (! $product instanceof Product || ! $product->inStock()) {
            return;
        }

        $cart = $this->ensure();
        $cart->items()->updateOrCreate(
            ['product_id' => $productId],
            ['qty' => min($product->purchaseLimit(), max(1, $qty))],
        );
        $this->extend($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->current();

        if (! $cart instanceof CartModel) {
            return;
        }

        $cart->items()->where('product_id', $productId)->delete();
        $this->extend($cart);
    }

    public function clear(): void
    {
        $cart = $this->current();

        if ($cart instanceof CartModel) {
            $cart->delete();
        }

        $this->cart = null;
        $this->resolved = true;
        $this->forgetCookie();
    }

    public function count(): int
    {
        return (int) array_sum($this->items());
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('line'), 2);
    }

    /**
     * @return Collection<int, array{product: Product, qty: int, line: float}>
     */
    public function lines(): Collection
    {
        $items = $this->items();
        $lines = collect();

        if ($items === []) {
            return $lines;
        }

        $products = Product::query()->whereIn('id', array_keys($items))->get()->keyBy('id');

        foreach ($items as $id => $qty) {
            $product = $products->get($id);

            if (! $product instanceof Product) {
                continue;
            }

            $lines->push([
                'product' => $product,
                'qty' => $qty,
                'line' => round((float) $product->price * $qty, 2),
            ]);
        }

        return $lines;
    }

    public function claimFor(User $user): void
    {
        $guest = $this->guestCart();
        $account = CartModel::query()->where('user_id', $user->id)->first();
        $account = $account instanceof CartModel ? $this->dropIfExpired($account) : null;

        if ($guest instanceof CartModel && $guest->user_id === $user->id) {
            $this->keep($guest);

            return;
        }

        if ($guest instanceof CartModel && $guest->user_id === null && ! $account instanceof CartModel) {
            $guest->update([
                'user_id' => $user->id,
                'expires_at' => $this->expiry(),
            ]);
            $this->keep($guest);

            return;
        }

        if ($guest instanceof CartModel && $guest->user_id === null && $account instanceof CartModel) {
            $this->merge($guest, $account);
            $guest->delete();
            $account->update(['expires_at' => $this->expiry()]);
            $this->keep($account);

            return;
        }

        if ($account instanceof CartModel) {
            $this->keep($account);
        }
    }

    public function releaseCookie(): void
    {
        $this->cart = null;
        $this->resolved = true;
        $this->forgetCookie();
    }

    private function guestCart(): ?CartModel
    {
        if ($this->resolved && $this->cart instanceof CartModel && $this->cart->user_id === null) {
            return $this->dropIfExpired($this->cart);
        }

        return $this->cartFromCookie();
    }

    private function current(): ?CartModel
    {
        if ($this->resolved) {
            return $this->cart;
        }

        $this->resolved = true;
        $cart = $this->cartFromCookie();

        if (! $cart instanceof CartModel && auth()->check()) {
            $owned = CartModel::query()->where('user_id', auth()->id())->first();
            $cart = $owned instanceof CartModel ? $this->dropIfExpired($owned) : null;

            if ($cart instanceof CartModel) {
                $this->rememberCookie($cart);
            }
        }

        return $this->cart = $cart;
    }

    private function cartFromCookie(): ?CartModel
    {
        $token = $this->request->cookie(self::COOKIE);

        if (! is_string($token) || $token === '') {
            return null;
        }

        $cart = CartModel::query()->where('token', $token)->first();

        if (! $cart instanceof CartModel) {
            return null;
        }

        $cart = $this->dropIfExpired($cart);

        if (! $cart instanceof CartModel) {
            return null;
        }

        if ($cart->user_id !== null && (! auth()->check() || $cart->user_id !== auth()->id())) {
            return null;
        }

        return $cart;
    }

    private function ensure(): CartModel
    {
        $cart = $this->current();

        if ($cart instanceof CartModel) {
            return $cart;
        }

        $cart = CartModel::query()->create([
            'user_id' => auth()->id(),
            'token' => Str::random(40),
            'expires_at' => $this->expiry(),
        ]);
        $this->keep($cart);

        return $cart;
    }

    private function merge(CartModel $guest, CartModel $account): void
    {
        foreach ($guest->items()->get() as $item) {
            $product = Product::query()->find($item->product_id);

            if (! $product instanceof Product) {
                continue;
            }

            $limit = $product->purchaseLimit();

            if ($limit < 1) {
                continue;
            }

            $existing = (int) $account->items()->where('product_id', $item->product_id)->value('qty');
            $account->items()->updateOrCreate(
                ['product_id' => $item->product_id],
                ['qty' => min($limit, $existing + $item->qty)],
            );
        }
    }

    private function dropIfExpired(CartModel $cart): ?CartModel
    {
        if (! $cart->isExpired()) {
            return $cart;
        }

        $cart->delete();

        if ($this->cart?->id === $cart->id) {
            $this->cart = null;
        }

        return null;
    }

    private function keep(CartModel $cart): void
    {
        $this->cart = $cart;
        $this->resolved = true;
        $this->rememberCookie($cart);
    }

    private function extend(CartModel $cart): void
    {
        $cart->update(['expires_at' => $this->expiry()]);
        $this->rememberCookie($cart);
    }

    private function expiry(): CarbonInterface
    {
        return now()->addDays((int) config('store.cart_ttl_days', 30));
    }

    private function rememberCookie(CartModel $cart): void
    {
        Cookie::queue(self::COOKIE, $cart->token, $this->cookieMinutes());
    }

    private function forgetCookie(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    private function cookieMinutes(): int
    {
        return (int) config('store.cart_ttl_days', 30) * 24 * 60;
    }
}
