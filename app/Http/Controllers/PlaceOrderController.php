<?php

namespace App\Http\Controllers;

use App\Services\Cart;
use App\Services\PlaceStoreOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Classic form POST for checkout — returns a real HTTP 302.
 *
 * Livewire AJAX (Network 200 + effects) is unreliable for leaving the page
 * toward Paymob after wire:navigate, so the storefront form posts here.
 */
class PlaceOrderController extends Controller
{
    public function __invoke(Request $request, PlaceStoreOrder $placer): RedirectResponse
    {
        $cart = app(Cart::class);
        $postedToken = $request->input('cart_token');
        $cart->resumePinned(is_string($postedToken) ? $postedToken : null);

        if ($cart->count() < 1) {
            return redirect()->route('cart');
        }

        $token = (string) $request->input('checkout_token', session('store.checkout_token', ''));

        try {
            $result = $placer->handle([
                'name' => (string) $request->input('name', ''),
                'phone' => (string) $request->input('phone', ''),
                'email' => $request->input('email'),
                'governorate' => (string) $request->input('governorate', 'Cairo'),
                'city' => (string) $request->input('city', ''),
                'address' => (string) $request->input('address', ''),
                'notes' => $request->input('notes'),
                'payment' => (string) $request->input('payment', 'paymob'),
                'promo' => $request->input('promo'),
            ], $token);
        } catch (ValidationException $exception) {
            throw $exception->redirectTo(route('checkout'));
        }

        $order = $result['order'];

        if ($result['collects_on_delivery']) {
            return redirect()->route('orders.show', $order);
        }

        return redirect()->route('paymob.go', $order);
    }
}
