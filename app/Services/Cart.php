<?php

namespace App\Services;

use App\Enums\BookFormat;
use App\Enums\ProductKind;
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
            $items[$item->product_id] = ($items[$item->product_id] ?? 0) + max(1, $item->qty);
        }

        return $items;
    }

    public function add(int $productId, int $qty = 1, ?string $format = null): void
    {
        $product = Product::query()->with('bookDetail')->find($productId);

        if (! $product instanceof Product) {
            return;
        }

        $formatKey = $this->resolveFormatKey($product, $format);
        $bookFormat = BookFormat::tryFrom($formatKey);

        if (! $product->canPurchaseFormat($bookFormat, max(1, $qty))) {
            return;
        }

        $cart = $this->ensure();
        $current = (int) $cart->items()
            ->where('product_id', $productId)
            ->where('format', $formatKey)
            ->value('qty');
        $limit = $product->purchaseLimitFor($bookFormat);
        $next = min($limit, $current + max(1, $qty));

        if ($next < 1) {
            return;
        }

        $cart->items()->updateOrCreate(
            ['product_id' => $productId, 'format' => $formatKey],
            ['qty' => $next],
        );
        $this->extend($cart);
    }

    public function set(int $productId, int $qty, ?string $format = null): void
    {
        $product = Product::query()->with('bookDetail')->find($productId);
        $formatKey = $product instanceof Product
            ? $this->resolveFormatKey($product, $format)
            : ($format ?: 'default');

        if ($qty < 1) {
            $this->remove($productId, $formatKey);

            return;
        }

        if (! $product instanceof Product) {
            return;
        }

        $bookFormat = BookFormat::tryFrom($formatKey);

        if (! $product->canPurchaseFormat($bookFormat, $qty)) {
            return;
        }

        $cart = $this->ensure();
        $limit = $product->purchaseLimitFor($bookFormat);
        $cart->items()->updateOrCreate(
            ['product_id' => $productId, 'format' => $formatKey],
            ['qty' => min($limit, max(1, $qty))],
        );
        $this->extend($cart);
    }

    public function remove(int $productId, ?string $format = null): void
    {
        $cart = $this->current();

        if (! $cart instanceof CartModel) {
            return;
        }

        $query = $cart->items()->where('product_id', $productId);

        if ($format !== null && $format !== '') {
            $query->where('format', $format);
        }

        $query->delete();
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

    public function id(): ?int
    {
        return $this->current()?->id;
    }

    public function token(): ?string
    {
        return $this->current()?->token;
    }

    /**
     * Bind the cart the shopper just saw when the store_cart cookie is absent.
     *
     * Checkout is a full-page POST. The cart page is Livewire, which can show
     * lines from the logged-in cart while the next document POST has no cookie.
     * The session pin and the hidden cart_token are set on the page that
     * rendered those lines, so place-order does not bounce back to /cart.
     */
    public function resumePinned(?string $postedToken = null): void
    {
        if ($this->count() > 0) {
            return;
        }

        foreach ([$this->sessionCartToken(), $postedToken] as $token) {
            if ($this->adoptToken($token)) {
                return;
            }
        }
    }

    public static function deleteById(?int $cartId): void
    {
        if ($cartId === null || $cartId < 1) {
            return;
        }

        CartModel::query()->whereKey($cartId)->delete();
    }

    public function count(): int
    {
        $cart = $this->current();

        if (! $cart instanceof CartModel) {
            return 0;
        }

        return (int) $cart->items()->sum('qty');
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('line'), 2);
    }

    /**
     * @return Collection<int, array{product: Product, qty: int, line: float, unit: float, format: BookFormat|null, format_key: string}>
     */
    public function lines(): Collection
    {
        $cart = $this->current();
        $lines = collect();

        if (! $cart instanceof CartModel) {
            return $lines;
        }

        $items = $cart->items()->get(['product_id', 'format', 'qty']);

        if ($items->isEmpty()) {
            return $lines;
        }

        $products = Product::query()
            ->with('bookDetail')
            ->whereIn('id', $items->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item->product_id);

            if (! $product instanceof Product) {
                continue;
            }

            $formatKey = (string) ($item->format ?: 'default');
            $bookFormat = BookFormat::tryFrom($formatKey);
            $unit = $product->priceForFormat($bookFormat);
            $qty = max(1, (int) $item->qty);

            $lines->push([
                'product' => $product,
                'qty' => $qty,
                'unit' => $unit,
                'line' => round($unit * $qty, 2),
                'format' => $bookFormat,
                'format_key' => $formatKey,
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

    private function resolveFormatKey(Product $product, ?string $format): string
    {
        if ($product->kind !== ProductKind::Book) {
            return 'default';
        }

        if (is_string($format) && $format !== '' && $format !== 'default') {
            $chosen = BookFormat::tryFrom($format);

            if ($chosen instanceof BookFormat && $product->canPurchaseFormat($chosen, 1)) {
                return $chosen->value;
            }
        }

        $default = $product->defaultBookFormat();

        return $default instanceof BookFormat ? $default->value : 'default';
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

        if ($cart instanceof CartModel) {
            $this->rememberInSession($cart);
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
            $product = Product::query()->with('bookDetail')->find($item->product_id);

            if (! $product instanceof Product) {
                continue;
            }

            $formatKey = (string) ($item->format ?: 'default');
            $bookFormat = BookFormat::tryFrom($formatKey);
            $limit = $product->purchaseLimitFor($bookFormat);

            if ($limit < 1) {
                continue;
            }

            $existing = (int) $account->items()
                ->where('product_id', $item->product_id)
                ->where('format', $formatKey)
                ->value('qty');

            $account->items()->updateOrCreate(
                ['product_id' => $item->product_id, 'format' => $formatKey],
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
        $this->rememberInSession($cart);
    }

    private function sessionCartToken(): ?string
    {
        $token = session('store.checkout_cart_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function adoptToken(?string $token): bool
    {
        if (! is_string($token) || preg_match('/^[A-Za-z0-9]{20,80}$/', $token) !== 1) {
            return false;
        }

        $cart = CartModel::query()->where('token', $token)->first();

        if (! $cart instanceof CartModel) {
            return false;
        }

        $cart = $this->dropIfExpired($cart);

        if (! $cart instanceof CartModel || ! $this->belongsToViewer($cart)) {
            return false;
        }

        if (! $cart->items()->exists()) {
            return false;
        }

        $this->keep($cart);

        return true;
    }

    private function belongsToViewer(CartModel $cart): bool
    {
        if ($cart->user_id === null) {
            return true;
        }

        return auth()->check() && (int) $cart->user_id === (int) auth()->id();
    }

    private function rememberInSession(CartModel $cart): void
    {
        if (session('store.checkout_cart_token') === $cart->token) {
            return;
        }

        session(['store.checkout_cart_token' => $cart->token]);
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
