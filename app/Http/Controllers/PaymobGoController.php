<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Paymob\PaymobClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Same-origin hop before Paymob hosted checkout.
 *
 * Livewire AJAX redirects to external URLs are unreliable in the browser
 * (response is still HTTP 200 with an effects.redirect payload). Sending the
 * shopper here first guarantees a real 302 away to Unified Checkout.
 */
class PaymobGoController extends Controller
{
    public function __invoke(Order $order, PaymobClient $paymob): RedirectResponse
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        $payment = $order->payments()->latest('id')->first();

        if (! $payment instanceof Payment || $payment->gateway !== 'paymob') {
            return redirect()->route('orders.show', $order);
        }

        if ($payment->status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        if (! $paymob->isConfigured()) {
            session()->flash('store.payment_error', __('store.checkout.paymob_unconfigured'));

            return redirect()->route('orders.show', $order);
        }

        // Cancel / soft failure leaves the row failed — reopen for another try.
        if ($payment->status === 'failed') {
            $payment->forceFill([
                'status' => 'pending',
                'failure_code' => null,
                'failure_message' => null,
                'checkout_url' => null,
            ])->save();

            if ($order->payment_status === 'failed') {
                $order->forceFill(['payment_status' => 'pending'])->save();
            }

            $this->reholdReservations($order);
        }

        try {
            $checkout = $this->mintIntention($order, $payment, $paymob);
        } catch (RequestException|RuntimeException $exception) {
            report($exception);
            session()->flash('store.payment_error', __('store.checkout.paymob_failed'));

            return redirect()->route('orders.show', $order);
        }

        return redirect()->away($checkout['checkout_url']);
    }

    /**
     * @return array{checkout_url: string, client_secret: string, intention_id: string|null, raw: array<string, mixed>}
     */
    private function mintIntention(Order $order, Payment $payment, PaymobClient $paymob): array
    {
        // Never reuse a hosted URL — Paymob marks paid / expired links as spent.
        // Always mint a unique special_reference or Paymob returns 400 "already exists".
        $attempt = (int) data_get($payment->metadata, 'intention_attempts', 0) + 1;
        $reference = $order->number.'-'.$payment->id.'-a'.$attempt.'-'.Str::lower(Str::random(6));
        /** @var list<string> $references */
        $references = array_values(array_unique([
            ...array_map('strval', data_get($payment->metadata, 'merchant_references', [])),
            $reference,
        ]));

        $payment->forceFill([
            'status' => 'pending',
            'merchant_reference' => $reference,
            'checkout_url' => null,
            'metadata' => array_merge($payment->metadata ?? [], [
                'intention_attempts' => $attempt,
                'merchant_references' => $references,
            ]),
        ])->save();

        $order->loadMissing('items');
        $payment = $payment->fresh() ?? $payment;

        $checkout = $paymob->createCheckout($order->fresh(['items']) ?? $order, $payment);

        $payment->forceFill([
            'checkout_url' => $checkout['checkout_url'],
            'metadata' => array_merge($payment->metadata ?? [], [
                'intention_id' => $checkout['intention_id'],
                'client_secret' => $checkout['client_secret'],
                'intention_attempts' => $attempt,
                'merchant_references' => $references,
            ]),
            'payload' => $checkout['raw'],
        ])->save();

        Log::info('Paymob intention created', [
            'order' => $order->number,
            'reference' => $reference,
            'intention_id' => $checkout['intention_id'],
        ]);

        return $checkout;
    }

    private function reholdReservations(Order $order): void
    {
        $released = $order->reservations()->where('status', 'released')->get();

        foreach ($released as $reservation) {
            $reservation->update([
                'status' => 'held',
                'expires_at' => now()->addMinutes(30),
            ]);
        }
    }
}
