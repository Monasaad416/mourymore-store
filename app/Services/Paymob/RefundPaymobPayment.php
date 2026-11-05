<?php

namespace App\Services\Paymob;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefundPaymobPayment
{
    public function __construct(private PaymobClient $paymob) {}

    public function refund(Payment $payment): void
    {
        $lock = Cache::lock('paymob-refund-'.$payment->id, 30);

        if (! $lock->get()) {
            throw new PaymobRefundException(__('filament/resources.payments.refund.in_progress'));
        }

        try {
            $payment->refresh();

            if ($payment->status === 'refunded') {
                return;
            }

            if (! $payment->isRefundable()) {
                throw new PaymobRefundException(__('filament/resources.payments.refund.not_refundable'));
            }

            $amountCents = (int) round(((float) $payment->amount) * 100);
            $result = $this->paymob->refund((int) $payment->transaction_id, $amountCents);
            $refundId = isset($result['id']) ? (string) $result['id'] : null;

            $payment->markRefunded($refundId, $amountCents);
            $payment->refresh();

            if ($payment->status !== 'refunded') {
                Log::error('Paymob refund succeeded but the payment was not marked refunded', [
                    'payment_id' => $payment->id,
                ]);

                throw new PaymobRefundException(__('filament/resources.payments.refund.ambiguous'), true);
            }
        } finally {
            $lock->release();
        }
    }
}
