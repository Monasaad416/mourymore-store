<?php

namespace App\Services;

use App\Enums\BookFormat;
use App\Enums\ProductType;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\StockReservation;
use App\Support\Payments;
use App\Support\Shipping;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlaceStoreOrder
{
    /**
     * Create or resume a checkout order.
     *
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     *     governorate: string,
     *     city: string,
     *     address: string,
     *     notes?: string|null,
     *     payment: string,
     *     promo?: string|null,
     * }  $input
     * @return array{order: Order, collects_on_delivery: bool}
     */
    public function handle(array $input, string $checkoutToken): array
    {
        if ($checkoutToken === '') {
            $checkoutToken = (string) Str::uuid();
            session(['store.checkout_token' => $checkoutToken]);
        }

        $resumed = $this->resumableOrder($checkoutToken);

        if ($resumed instanceof Order) {
            return [
                'order' => $resumed,
                'collects_on_delivery' => $resumed->payment_status === 'due_on_delivery',
            ];
        }

        $lock = Cache::lock('store.checkout.lock.'.session()->getId(), 60);

        try {
            $lock->block(15);
        } catch (LockTimeoutException) {
            $resumed = $this->resumableOrder($checkoutToken);

            if ($resumed instanceof Order) {
                return [
                    'order' => $resumed,
                    'collects_on_delivery' => $resumed->payment_status === 'due_on_delivery',
                ];
            }

            throw ValidationException::withMessages([
                'cart' => __('store.checkout.already_placing'),
            ]);
        }

        try {
            $resumed = $this->resumableOrder($checkoutToken);

            if ($resumed instanceof Order) {
                return [
                    'order' => $resumed,
                    'collects_on_delivery' => $resumed->payment_status === 'due_on_delivery',
                ];
            }

            return $this->createOrderFromCart($input, $checkoutToken);
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     *     governorate: string,
     *     city: string,
     *     address: string,
     *     notes?: string|null,
     *     payment: string,
     *     promo?: string|null,
     * }  $input
     * @return array{order: Order, collects_on_delivery: bool}
     */
    private function createOrderFromCart(array $input, string $checkoutToken): array
    {
        $cart = app(Cart::class);
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('store.cart.empty_title'),
            ]);
        }

        $codes = Payments::codes();

        if ($codes === []) {
            throw ValidationException::withMessages([
                'payment' => __('store.checkout.payment_unavailable'),
            ]);
        }

        $validated = validator($input, [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'governorate' => ['required', Rule::in(Shipping::governorates())],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:240'],
            'notes' => ['nullable', 'string', 'max:240'],
            'payment' => ['required', Rule::in($codes)],
            'promo' => ['nullable', 'string', 'max:40'],
        ], [
            'phone.regex' => __('store.checkout.phone_invalid'),
        ], [
            'name' => __('store.checkout.full_name'),
            'phone' => __('store.checkout.mobile'),
            'email' => __('store.checkout.email'),
            'governorate' => __('store.checkout.governorate'),
            'city' => __('store.checkout.city'),
            'address' => __('store.checkout.address_label'),
            'notes' => __('store.checkout.notes'),
            'payment' => __('store.checkout.payment'),
            'promo' => __('store.checkout.promo'),
        ])->validate();

        $validated['email'] = $validated['email'] ?? '';
        $validated['notes'] = $validated['notes'] ?? '';
        $validated['promo'] = $validated['promo'] ?? '';

        $collectsOnDelivery = Payments::collectsOnDelivery($validated['payment']);
        $settings = SiteSetting::current();
        $currency = $settings->currencyCode();
        $promoInput = (string) $validated['promo'];

        $order = DB::transaction(function () use ($validated, $lines, $collectsOnDelivery, $settings, $currency, $promoInput): Order {
            StockReservation::releaseExpired();

            /** @var list<array{product: Product, qty: int, unit: float, line: float, type: ProductType, needs_stock: bool, names: array<string, string>}> $priced */
            $priced = [];
            $subtotal = 0.0;
            $ships = false;

            foreach ($lines as $line) {
                $product = Product::query()->with('bookDetail')->lockForUpdate()->find($line['product']->id);
                $format = $line['format'] ?? null;
                $format = $format instanceof BookFormat ? $format : null;

                if (! $product instanceof Product || ! $product->canPurchaseFormat($format, (int) $line['qty'])) {
                    throw ValidationException::withMessages([
                        'cart' => __('store.checkout.stock', ['name' => $line['product']->displayName()]),
                    ]);
                }

                $isDigitalLine = $format === BookFormat::Digital
                    || ($format === null && ! $product->tracksStock());
                $type = $isDigitalLine ? ProductType::Digital : ProductType::Physical;
                $unit = $product->priceForFormat($format);
                $lineTotal = round($unit * (int) $line['qty'], 2);
                $subtotal += $lineTotal;
                $needsShipping = ! $isDigitalLine && $product->tracksStock();
                $ships = $ships || $needsShipping;
                $names = $product->getTranslations('name');

                $priced[] = [
                    'product' => $product,
                    'qty' => (int) $line['qty'],
                    'unit' => $unit,
                    'line' => $lineTotal,
                    'type' => $type,
                    'needs_stock' => $needsShipping,
                    'names' => $names !== [] ? $names : [app()->getLocale() => $product->displayName()],
                ];
            }

            $subtotal = round($subtotal, 2);
            $promoNormalized = strtoupper(trim($promoInput));

            $offer = $promoNormalized !== ''
                ? Offer::lockUsableByCode($promoInput, $subtotal)
                : null;

            if ($promoNormalized !== '' && ! $offer instanceof Offer) {
                throw ValidationException::withMessages([
                    'promo' => __('store.checkout.promo_invalid'),
                ]);
            }

            $discount = $offer?->discountAmount($subtotal) ?? 0.0;
            $quote = $ships
                ? Shipping::quote($validated['governorate'], $subtotal)
                : ['fee' => 0.0, 'eta' => '', 'note' => '', 'method_id' => null, 'method_name' => null];
            $taxable = max(0, round($subtotal - $discount, 2));
            $tax = $settings->taxOn($taxable);
            $taxAdded = $settings->pricesIncludeTax() ? 0.0 : $tax;
            $total = round($taxable + $taxAdded + $quote['fee'], 2);
            $promoCode = $offer instanceof Offer ? strtoupper($offer->code) : null;

            $order = Order::query()->create([
                'number' => 'mourymore_store-'.strtoupper(Str::random(8)),
                'user_id' => auth()->id(),
                'customer_name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] !== '' ? $validated['email'] : null,
                'governorate' => $validated['governorate'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'notes' => $validated['notes'] !== '' ? $validated['notes'] : null,
                'payment_method' => $validated['payment'],
                'shipping_method_id' => $quote['method_id'],
                'shipping_method_name' => $quote['method_name'],
                'payment_status' => $collectsOnDelivery ? 'due_on_delivery' : 'pending',
                'delivery_status' => $ships ? 'pending' : 'not_required',
                'promo_code' => $promoCode,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping' => $quote['fee'],
                'total' => $total,
                'currency' => $currency,
                'status' => 'placed',
            ]);

            if ($offer instanceof Offer && ! $offer->recordUse()) {
                throw ValidationException::withMessages([
                    'promo' => __('store.checkout.promo_invalid'),
                ]);
            }

            foreach ($priced as $row) {
                $product = $row['product'];

                if ($row['needs_stock'] && $collectsOnDelivery) {
                    $product->decrement('stock', $row['qty']);
                }

                if ($row['needs_stock'] && ! $collectsOnDelivery) {
                    $order->reservations()->create([
                        'product_id' => $product->id,
                        'qty' => $row['qty'],
                        'status' => 'held',
                        'expires_at' => now()->addMinutes(30),
                    ]);
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_type' => $row['type']->value,
                    'name' => $row['names'],
                    'image' => $product->image,
                    'qty' => $row['qty'],
                    'price' => $row['unit'],
                    'line_total' => $row['line'],
                ]);
            }

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'gateway' => $collectsOnDelivery ? null : 'paymob',
                'amount' => $total,
                'currency' => $currency,
                'status' => $collectsOnDelivery ? 'due_on_delivery' : 'pending',
            ]);

            if (! $collectsOnDelivery) {
                $payment->update([
                    'merchant_reference' => $order->number.'-'.$payment->id,
                ]);
            }

            return $order;
        });

        $this->rememberCheckoutOrder($order, $checkoutToken);

        if ($collectsOnDelivery) {
            $cart->clear();
        } else {
            $payment = $order->payments()->latest('id')->first();

            if ($payment instanceof Payment) {
                $cartId = $cart->id();

                if ($cartId !== null) {
                    $payment->forceFill([
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'cart_id' => $cartId,
                        ]),
                    ])->save();
                }
            }
        }

        session(['store.governorate' => $validated['governorate']]);

        return [
            'order' => $order,
            'collects_on_delivery' => $collectsOnDelivery,
        ];
    }

    private function resumableOrder(string $checkoutToken): ?Order
    {
        $sessionOrderId = session('store.checkout_order_id');

        if (is_numeric($sessionOrderId)) {
            $order = Order::query()->find((int) $sessionOrderId);

            if ($order instanceof Order && $this->isResumable($order)) {
                return $order;
            }
        }

        $id = Cache::get($this->checkoutCacheKey($checkoutToken));

        if (! is_numeric($id)) {
            return null;
        }

        $order = Order::query()->find((int) $id);

        return $order instanceof Order && $this->isResumable($order) ? $order : null;
    }

    private function isResumable(Order $order): bool
    {
        if ($order->payment_status === 'due_on_delivery') {
            return true;
        }

        if ($order->payment_status !== 'pending') {
            return false;
        }

        $payment = $order->payments()->latest('id')->first();

        return $payment instanceof Payment && $payment->status === 'pending';
    }

    private function rememberCheckoutOrder(Order $order, string $checkoutToken): void
    {
        Cache::put($this->checkoutCacheKey($checkoutToken), $order->id, now()->addHours(6));
        session([
            'store.checkout_order_id' => $order->id,
            'store.checkout_token' => $checkoutToken,
        ]);
    }

    private function checkoutCacheKey(string $checkoutToken): string
    {
        return 'store.checkout.'.session()->getId().'.'.$checkoutToken;
    }
}
