<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Paymob\PaymobClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymobReturnController extends Controller
{
    /**
     * Browser return after Unified Checkout.
     *
     * Production should still rely on the server webhook, but on local/dev
     * Paymob cannot reach *.test notification URLs — so a verified return
     * HMAC is enough to mark the payment paid (idempotent with the webhook).
     */
    public function __invoke(Request $request, Order $order, PaymobClient $paymob): RedirectResponse
    {
        $this->forgetCheckoutResume();

        $query = $request->query();
        $hmac = (string) ($query['hmac'] ?? '');
        unset($query['hmac']);

        if ($hmac !== '' && $paymob->verifyReturnHmac($query, $hmac)) {
            $this->applyVerifiedReturn($order, $query);
        } else {
            Log::info('Paymob return skipped status update', [
                'order' => $order->number,
                'hmac_present' => $hmac !== '',
                'success' => $query['success'] ?? null,
            ]);
        }

        $order->refresh();

        if ($order->payment_status === 'paid') {
            session()->flash('store.payment_success', __('store.order.payment_success'));
        } elseif ($order->payment_status === 'failed') {
            session()->flash('store.payment_error', __('store.order.payment_cancelled'));
        }

        return redirect()->route('orders.show', $order);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function applyVerifiedReturn(Order $order, array $query): void
    {
        $success = filter_var($query['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $pending = filter_var($query['pending'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $payment = $order->payments()->latest('id')->first();

        if (! $payment instanceof Payment) {
            return;
        }

        $transactionId = isset($query['id']) ? (string) $query['id'] : null;

        $payment->forceFill([
            'metadata' => array_merge($payment->metadata ?? [], [
                'return_at' => now()->toIso8601String(),
                'return_query' => [
                    'success' => $query['success'] ?? null,
                    'pending' => $query['pending'] ?? null,
                    'id' => $query['id'] ?? null,
                ],
            ]),
        ])->save();

        if ($success && ! $pending) {
            $expectedCents = (int) round(((float) $payment->amount) * 100);
            $receivedCents = (int) ($query['amount_cents'] ?? 0);

            if ($receivedCents > 0 && $receivedCents !== $expectedCents) {
                Log::warning('Paymob return amount mismatch', [
                    'payment_id' => $payment->id,
                    'expected' => $expectedCents,
                    'received' => $receivedCents,
                ]);

                return;
            }

            $payment->markPaid($transactionId);

            return;
        }

        if (! $success && ! $pending) {
            $payment->markFailed(
                isset($query['txn_response_code']) ? (string) $query['txn_response_code'] : 'return_failed',
                isset($query['data.message']) ? (string) $query['data.message'] : null,
            );
        }
    }

    private function forgetCheckoutResume(): void
    {
        $token = session('store.checkout_token');

        if (is_string($token) && $token !== '') {
            Cache::forget('store.checkout.'.session()->getId().'.'.$token);
        }

        session()->forget([
            'store.checkout_order_id',
            'store.checkout_token',
        ]);
    }
}
