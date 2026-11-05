<?php

namespace App\Livewire\Store;

use App\Enums\BookFormat;
use App\Models\Offer;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\Cart;
use App\Services\PlaceStoreOrder;
use App\Support\Money;
use App\Support\Payments;
use App\Support\Shipping;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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

    public string $payment = 'paymob';

    public string $promo = '';

    /**
     * Filled by placeOrder() for Livewire tests / fallback JS navigation.
     * The storefront form posts to PlaceOrderController for a real HTTP 302.
     */
    public string $payRedirectUrl = '';

    #[Locked]
    public string $checkoutToken = '';

    public function mount(): void
    {
        $this->bindCheckoutToken();

        if (app(Cart::class)->count() < 1) {
            $this->redirect(route('cart'), navigate: true);

            return;
        }

        $saved = session('store.governorate', 'Cairo');
        $this->governorate = is_string($saved) && in_array($saved, Shipping::governorates(), true)
            ? $saved
            : 'Cairo';

        $this->name = $this->oldString('name', $this->name);
        $this->phone = $this->oldString('phone', $this->phone);
        $this->email = $this->oldString('email', $this->email);
        $this->city = $this->oldString('city', $this->city);
        $this->address = $this->oldString('address', $this->address);
        $this->notes = $this->oldString('notes', $this->notes);
        $this->promo = $this->oldString('promo', $this->promo);

        $oldGovernorate = $this->oldString('governorate');
        if ($oldGovernorate !== '' && in_array($oldGovernorate, Shipping::governorates(), true)) {
            $this->governorate = $oldGovernorate;
        }

        $user = auth()->user();

        if ($user !== null) {
            if ($this->name === '') {
                $this->name = $user->name;
            }
            if ($this->email === '') {
                $this->email = $user->email;
            }
        }

        $codes = Payments::codes();

        if ($codes === []) {
            $this->payment = '';
        } elseif (! in_array($this->payment, $codes, true)) {
            $this->payment = $codes[0];
        }

        $oldPayment = $this->oldString('payment');
        if ($oldPayment !== '' && in_array($oldPayment, $codes, true)) {
            $this->payment = $oldPayment;
        }
    }

    private function oldString(string $key, string $default = ''): string
    {
        $value = old($key, $default);

        return is_string($value) ? $value : $default;
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

    /**
     * Kept for feature tests. The browser form posts to PlaceOrderController.
     */
    public function placeOrder(): void
    {
        app(Cart::class)->resumePinned();

        if ($this->checkoutToken === '') {
            $this->rotateCheckoutToken();
        }

        try {
            $result = app(PlaceStoreOrder::class)->handle([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'governorate' => $this->governorate,
                'city' => $this->city,
                'address' => $this->address,
                'notes' => $this->notes,
                'payment' => $this->payment,
                'promo' => $this->promo,
            ], $this->checkoutToken);
        } catch (ValidationException $exception) {
            if (isset($exception->errors()['cart']) && app(Cart::class)->count() < 1) {
                $this->redirect(route('cart'), navigate: true);

                return;
            }

            throw $exception;
        }

        $order = $result['order'];

        if ($result['collects_on_delivery']) {
            $this->dispatch('basket-updated');
            $this->redirect(route('orders.show', $order), navigate: true);

            return;
        }

        $url = route('paymob.go', $order);
        $this->payRedirectUrl = $url;
        $this->js('window.location.assign('.json_encode($url).')');
    }

    private function bindCheckoutToken(): void
    {
        $token = session('store.checkout_token');

        if (is_string($token) && $token !== '') {
            $this->checkoutToken = $token;
            $order = $this->orderForCheckoutAttempt();

            if ($order instanceof Order && in_array($order->payment_status, ['pending', 'due_on_delivery'], true)) {
                return;
            }
        }

        $this->rotateCheckoutToken();
    }

    private function rotateCheckoutToken(): void
    {
        $this->checkoutToken = (string) Str::uuid();
        session([
            'store.checkout_token' => $this->checkoutToken,
        ]);
        session()->forget('store.checkout_order_id');
    }

    private function orderForCheckoutAttempt(): ?Order
    {
        $sessionOrderId = session('store.checkout_order_id');

        if (is_numeric($sessionOrderId)) {
            $order = Order::query()->find((int) $sessionOrderId);

            if ($order instanceof Order) {
                return $order;
            }
        }

        $id = Cache::get('store.checkout.'.session()->getId().'.'.$this->checkoutToken);

        if (! is_numeric($id)) {
            return null;
        }

        return Order::query()->find((int) $id);
    }

    public function render(): View
    {
        $settings = SiteSetting::current();
        $cart = app(Cart::class);
        $lines = $cart->lines();
        $subtotal = $cart->subtotal();
        $discount = $this->discountFor($subtotal);
        $ships = $lines->contains(function (array $line): bool {
            $format = $line['format'] ?? null;

            return $format !== BookFormat::Digital && $line['product']->tracksStock();
        });
        $quote = $ships
            ? Shipping::quote($this->governorate, $subtotal)
            : ['fee' => 0.0, 'eta' => '', 'note' => '', 'method_id' => null, 'method_name' => null];
        $taxable = max(0, round($subtotal - $discount, 2));
        $tax = $settings->taxOn($taxable);
        $taxAdded = $settings->pricesIncludeTax() ? 0.0 : $tax;
        $payments = Payments::options();

        return view('livewire.store.checkout-page', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'taxAdded' => $taxAdded,
            'taxPercent' => $settings->taxPercent(),
            'pricesIncludeTax' => $settings->pricesIncludeTax(),
            'quote' => $quote,
            'ships' => $ships,
            'total' => round($taxable + $taxAdded + $quote['fee'], 2),
            'governorates' => Shipping::governorates(),
            'payments' => $payments,
            'canPlaceOrder' => $payments !== [],
            'promoCode' => Offer::featuredCode(),
            'freeOver' => Money::format($settings->freeShippingOver()),
        ])->title(__('store.checkout.meta_title'));
    }

    private function discountFor(float $subtotal): float
    {
        return Offer::findUsableByCode($this->promo, $subtotal)?->discountAmount($subtotal) ?? 0.0;
    }
}
