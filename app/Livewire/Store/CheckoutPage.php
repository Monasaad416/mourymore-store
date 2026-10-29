<?php

namespace App\Livewire\Store;

use App\Enums\ProductType;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\StockReservation;
use App\Services\Cart;
use App\Support\Money;
use App\Support\Payments;
use App\Support\Shipping;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.store')]
class CheckoutPage extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $governorate = 'Cairo';

    public string $city = '';

    public string $address = '';

    public string $notes = '';

    public string $payment = 'card';

    public string $promo = '';

    #[Locked]
    public string $checkoutToken = '';

    public function mount(): void
    {
        $this->checkoutToken = (string) Str::uuid();

        if (app(Cart::class)->count() < 1) {
            $this->redirect(route('cart'), navigate: true);

            return;
        }

        $saved = session('store.governorate', 'Cairo');
        $this->governorate = is_string($saved) && in_array($saved, Shipping::governorates(), true)
            ? $saved
            : 'Cairo';

        $user = auth()->user();

        if ($user !== null) {
            $this->name = $user->name;
            $this->email = $user->email;
        }
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

    public function placeOrder(): void
    {
        if ($this->checkoutToken === '') {
            $this->checkoutToken = (string) Str::uuid();
        }

        if ($this->resumeCheckoutOrder()) {
            return;
        }

        $lock = Cache::lock('store.checkout.lock.'.session()->getId(), 20);

        try {
            $lock->block(10);
        } catch (LockTimeoutException) {
            if ($this->resumeCheckoutOrder()) {
                return;
            }

            throw ValidationException::withMessages([
                'cart' => __('store.checkout.already_placing'),
            ]);
        }

        try {
            if ($this->resumeCheckoutOrder()) {
                return;
            }

            $this->createOrderFromCart();
        } finally {
            $lock->release();
        }
    }

    private function createOrderFromCart(): void
    {
        $cart = app(Cart::class);
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            $this->redirect(route('cart'), navigate: true);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'governorate' => ['required', Rule::in(Shipping::governorates())],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:240'],
            'notes' => ['nullable', 'string', 'max:240'],
            'payment' => ['required', Rule::in(Payments::codes())],
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
        ]);

        $settings = SiteSetting::current();
        $subtotal = $cart->subtotal();
        $offer = Offer::findUsableByCode($this->promo, $subtotal);
        $discount = $offer?->discountAmount($subtotal) ?? 0.0;
        $ships = $lines->contains(fn (array $line): bool => $line['product']->tracksStock());
        $quote = $ships
            ? Shipping::quote($validated['governorate'], $subtotal)
            : ['fee' => 0.0, 'eta' => '', 'note' => '', 'method_id' => null, 'method_name' => null];
        $taxable = max(0, round($subtotal - $discount, 2));
        $tax = $settings->pricesIncludeTax()
            ? $settings->taxOn($taxable)
            : $settings->taxOn($taxable);
        $taxAdded = $settings->pricesIncludeTax() ? 0.0 : $tax;
        $total = round($taxable + $taxAdded + $quote['fee'], 2);
        $promoCode = $offer instanceof Offer ? strtoupper($offer->code) : null;
        $collectsOnDelivery = Payments::collectsOnDelivery($validated['payment']);
        $currency = $settings->currencyCode();

        $order = DB::transaction(function () use ($validated, $lines, $subtotal, $discount, $tax, $quote, $total, $promoCode, $collectsOnDelivery, $ships, $offer, $currency): Order {
            StockReservation::releaseExpired();

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

            if ($offer instanceof Offer) {
                $offer->recordUse();
            }

            foreach ($lines as $line) {
                $product = Product::query()->lockForUpdate()->find($line['product']->id);

                if (! $product instanceof Product || ! $product->canPurchase($line['qty'])) {
                    throw ValidationException::withMessages([
                        'cart' => __('store.checkout.stock', ['name' => $line['product']->displayName()]),
                    ]);
                }

                $type = $product->type ?? ProductType::Physical;

                if ($product->tracksStock() && $collectsOnDelivery) {
                    $product->decrement('stock', $line['qty']);
                }

                if ($product->tracksStock() && ! $collectsOnDelivery) {
                    $order->reservations()->create([
                        'product_id' => $product->id,
                        'qty' => $line['qty'],
                        'status' => 'held',
                        'expires_at' => now()->addMinutes(30),
                    ]);
                }

                $names = $product->getTranslations('name');

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_type' => $type->value,
                    'name' => $names !== [] ? $names : [app()->getLocale() => $product->displayName()],
                    'image' => $product->image,
                    'qty' => $line['qty'],
                    'price' => $product->price,
                    'line_total' => $line['line'],
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

        $this->rememberCheckoutOrder($order);
        $cart->clear();
        session(['store.governorate' => $validated['governorate']]);
        $this->dispatch('basket-updated');
        $this->redirect(route('orders.show', $order), navigate: true);
    }

    private function resumeCheckoutOrder(): bool
    {
        $order = $this->orderForCheckoutToken();

        if (! $order instanceof Order) {
            return false;
        }

        $this->redirect(route('orders.show', $order), navigate: true);

        return true;
    }

    private function orderForCheckoutToken(): ?Order
    {
        $id = Cache::get($this->checkoutCacheKey());

        if (! is_numeric($id)) {
            return null;
        }

        return Order::query()->find((int) $id);
    }

    private function rememberCheckoutOrder(Order $order): void
    {
        Cache::put($this->checkoutCacheKey(), $order->id, now()->addHours(6));
    }

    private function checkoutCacheKey(): string
    {
        return 'store.checkout.'.session()->getId().'.'.$this->checkoutToken;
    }

    public function render(): View
    {
        $settings = SiteSetting::current();
        $cart = app(Cart::class);
        $subtotal = $cart->subtotal();
        $discount = $this->discountFor($subtotal);
        $quote = Shipping::quote($this->governorate, $subtotal);
        $taxable = max(0, round($subtotal - $discount, 2));
        $tax = $settings->taxOn($taxable);
        $taxAdded = $settings->pricesIncludeTax() ? 0.0 : $tax;

        return view('livewire.store.checkout-page', [
            'lines' => $cart->lines(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'taxAdded' => $taxAdded,
            'taxPercent' => $settings->taxPercent(),
            'pricesIncludeTax' => $settings->pricesIncludeTax(),
            'quote' => $quote,
            'total' => round($taxable + $taxAdded + $quote['fee'], 2),
            'governorates' => Shipping::governorates(),
            'payments' => Payments::options(),
            'promoCode' => Offer::featuredCode(),
            'freeOver' => Money::format($settings->freeShippingOver()),
        ])->title(__('store.checkout.meta_title'));
    }

    private function discountFor(float $subtotal): float
    {
        return Offer::findUsableByCode($this->promo, $subtotal)?->discountAmount($subtotal) ?? 0.0;
    }
}
