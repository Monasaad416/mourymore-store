<?php

namespace App\Http\Controllers;

use App\Enums\BookFormat;
use App\Models\Offer;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Cart;
use App\Support\Money;
use App\Support\Payments;
use App\Support\Shipping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Full-page checkout outside Livewire so the order form can POST natively.
 * Livewire intercepts forms inside components (even without wire:submit).
 */
class CheckoutPageController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $cart = app(Cart::class);

        if ($cart->count() < 1) {
            return redirect()->route('cart');
        }

        $token = session('store.checkout_token');

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();
            session(['store.checkout_token' => $token]);
        }

        $saved = session('store.governorate', 'Cairo');
        $governorate = is_string($saved) && in_array($saved, Shipping::governorates(), true)
            ? $saved
            : 'Cairo';

        $oldGovernorate = old('governorate');
        if (is_string($oldGovernorate) && in_array($oldGovernorate, Shipping::governorates(), true)) {
            $governorate = $oldGovernorate;
        }

        $codes = Payments::codes();
        $payment = $codes[0] ?? '';
        $oldPayment = old('payment');
        if (is_string($oldPayment) && in_array($oldPayment, $codes, true)) {
            $payment = $oldPayment;
        }

        $user = auth()->user();
        $name = old('name', $user instanceof User ? $user->name : '');
        $email = old('email', $user instanceof User ? $user->email : '');

        $settings = SiteSetting::current();
        $lines = $cart->lines();
        $subtotal = $cart->subtotal();
        $promo = is_string(old('promo')) ? (string) old('promo') : '';
        $discount = Offer::findUsableByCode($promo, $subtotal)?->discountAmount($subtotal) ?? 0.0;
        $ships = $lines->contains(function (array $line): bool {
            $format = $line['format'] ?? null;

            return $format !== BookFormat::Digital && $line['product']->tracksStock();
        });
        $quote = $ships
            ? Shipping::quote($governorate, $subtotal)
            : ['fee' => 0.0, 'eta' => '', 'note' => '', 'method_id' => null, 'method_name' => null];
        $taxable = max(0, round($subtotal - $discount, 2));
        $tax = $settings->taxOn($taxable);
        $taxAdded = $settings->pricesIncludeTax() ? 0.0 : $tax;
        $payments = Payments::options();

        return view('store.checkout', [
            'title' => __('store.checkout.meta_title'),
            'checkoutToken' => $token,
            'cartToken' => (string) ($cart->token() ?? ''),
            'name' => is_string($name) ? $name : '',
            'phone' => is_string(old('phone')) ? (string) old('phone') : '',
            'email' => is_string($email) ? $email : '',
            'governorate' => $governorate,
            'city' => is_string(old('city')) ? (string) old('city') : '',
            'address' => is_string(old('address')) ? (string) old('address') : '',
            'notes' => is_string(old('notes')) ? (string) old('notes') : '',
            'payment' => $payment,
            'promo' => $promo,
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
        ]);
    }
}
