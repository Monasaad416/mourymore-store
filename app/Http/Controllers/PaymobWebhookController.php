<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Paymob\PaymobClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymobWebhookController extends Controller
{
    public function __invoke(Request $request, PaymobClient $paymob): JsonResponse
    {
        /** @var array<string, mixed> $obj */
        $obj = $request->input('obj', []);
        $hmac = (string) $request->query('hmac', '');

        if ($obj === [] || ! $paymob->verifyTransactionHmac($obj, $hmac)) {
            Log::warning('Paymob webhook rejected', [
                'has_obj' => $obj !== [],
                'hmac_present' => $hmac !== '',
            ]);

            return response()->json(['error' => 'Invalid HMAC'], 401);
        }

        $merchantReference = (string) (
            data_get($obj, 'order.merchant_order_id')
            ?: data_get($obj, 'merchant_order_id')
            ?: ''
        );

        $payment = $this->findPaymentByMerchantReference($merchantReference);

        if (! $payment instanceof Payment) {
            Log::warning('Paymob webhook payment not found', [
                'merchant_reference' => $merchantReference,
                'transaction_id' => $obj['id'] ?? null,
            ]);

            return response()->json(['received' => true]);
        }

        $success = filter_var($obj['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $pending = filter_var($obj['pending'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $transactionId = isset($obj['id']) ? (string) $obj['id'] : null;
        $refunded = $this->isRefundCallback($obj);

        $metadata = array_merge($payment->metadata ?? [], [
            'paymob_order_id' => data_get($obj, 'order.id'),
            'webhook_at' => now()->toIso8601String(),
        ]);

        if ($refunded) {
            $metadata['refund_webhook_at'] = now()->toIso8601String();
            $payment->forceFill(['metadata' => $metadata])->save();

            if ($success && ! $pending) {
                $expectedCents = (int) round(((float) $payment->amount) * 100);
                $receivedCents = (int) ($obj['amount_cents'] ?? 0);

                if ($receivedCents !== $expectedCents) {
                    Log::warning('Paymob refund webhook amount mismatch', [
                        'payment_id' => $payment->id,
                        'expected' => $expectedCents,
                        'received' => $receivedCents,
                    ]);

                    return response()->json(['received' => true]);
                }

                $payment->markRefunded($transactionId, $receivedCents);
            }

            return response()->json(['received' => true]);
        }

        $payment->forceFill([
            'payload' => $obj,
            'metadata' => $metadata,
        ])->save();

        if ($success && ! $pending) {
            $expectedCents = (int) round(((float) $payment->amount) * 100);
            $receivedCents = (int) ($obj['amount_cents'] ?? 0);

            if ($receivedCents > 0 && $receivedCents !== $expectedCents) {
                Log::warning('Paymob webhook amount mismatch', [
                    'payment_id' => $payment->id,
                    'expected' => $expectedCents,
                    'received' => $receivedCents,
                ]);

                return response()->json(['received' => true]);
            }

            $payment->markPaid($transactionId);
        } elseif (! $success && ! $pending) {
            $payment->markFailed(
                isset($obj['data']['txn_response_code']) ? (string) $obj['data']['txn_response_code'] : null,
                isset($obj['data']['message']) ? (string) $obj['data']['message'] : null,
            );
        }

        return response()->json(['received' => true]);
    }

    /**
     * @param  array<string, mixed>  $obj
     */
    private function isRefundCallback(array $obj): bool
    {
        return filter_var($obj['is_refunded'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || filter_var($obj['is_voided'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || filter_var($obj['is_refund'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function findPaymentByMerchantReference(string $merchantReference): ?Payment
    {
        if ($merchantReference === '') {
            return null;
        }

        return Payment::query()
            ->where('gateway', 'paymob')
            ->where(function ($query) use ($merchantReference): void {
                $query->where('merchant_reference', $merchantReference)
                    // Retried intentions keep older refs in metadata.merchant_references.
                    ->orWhere('metadata', 'like', '%"'.$merchantReference.'"%');
            })
            ->latest('id')
            ->first();
    }
}
