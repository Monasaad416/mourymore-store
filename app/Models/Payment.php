<?php

namespace App\Models;

use App\Services\Cart;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $payment_method_id
 * @property string|null $gateway
 * @property string|null $transaction_id
 * @property string|null $reference
 * @property string|null $merchant_reference
 * @property string $amount
 * @property string $currency
 * @property string $status
 * @property string|null $failure_code
 * @property string|null $failure_message
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $metadata
 * @property string|null $checkout_url
 * @property Carbon|null $paid_at
 */
#[Fillable([
    'order_id',
    'payment_method_id',
    'gateway',
    'transaction_id',
    'reference',
    'merchant_reference',
    'amount',
    'currency',
    'status',
    'failure_code',
    'failure_message',
    'payload',
    'metadata',
    'checkout_url',
    'paid_at',
])]
class Payment extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payload' => 'array',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function isRefundable(): bool
    {
        return $this->status === 'paid'
            && $this->gateway === 'paymob'
            && ctype_digit((string) $this->transaction_id)
            && (int) round(((float) $this->amount) * 100) > 0;
    }

    public function markPaid(?string $transactionId = null): void
    {
        if (in_array($this->status, ['paid', 'refunded'], true)) {
            return;
        }

        DB::transaction(function () use ($transactionId): void {
            $order = $this->order()->lockForUpdate()->first();

            if (! $order instanceof Order) {
                return;
            }

            foreach ($order->reservations()->where('status', 'held')->get() as $reservation) {
                $product = Product::query()->lockForUpdate()->find($reservation->product_id);

                if ($product instanceof Product) {
                    $product->decrement('stock', min($product->stock, $reservation->qty));
                }

                $reservation->update(['status' => 'committed']);
            }

            $order->forceFill(['payment_status' => 'paid'])->save();
            $order->grantDigitalAccess();

            $this->forceFill([
                'status' => 'paid',
                'transaction_id' => $transactionId ?? $this->transaction_id,
                'paid_at' => now(),
                'checkout_url' => null,
            ])->save();

            $cartId = data_get($this->metadata, 'cart_id');
            Cart::deleteById(is_numeric($cartId) ? (int) $cartId : null);
        });
    }

    public function markRefunded(?string $refundTransactionId = null, ?int $amountCents = null): void
    {
        DB::transaction(function () use ($refundTransactionId, $amountCents): void {
            $payment = self::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $payment instanceof self || $payment->status !== 'paid') {
                return;
            }

            $order = $payment->order()->lockForUpdate()->first();

            if ($order instanceof Order) {
                foreach ($order->reservations()->where('status', 'committed')->lockForUpdate()->get() as $reservation) {
                    $product = Product::withTrashed()->lockForUpdate()->find($reservation->product_id);

                    if ($product instanceof Product && ! $product->trashed()) {
                        $product->increment('stock', $reservation->qty);
                    }

                    $reservation->update(['status' => 'released']);
                }

                $order->entitlements()->delete();

                $orderUpdates = ['payment_status' => 'refunded'];

                if (in_array($order->delivery_status, ['pending', 'not_required'], true)) {
                    $orderUpdates['status'] = 'cancelled';
                }

                $order->forceFill($orderUpdates)->save();
            }

            $metadata = $payment->metadata ?? [];
            $metadata['refunded_at'] = now()->toIso8601String();

            if ($refundTransactionId !== null && $refundTransactionId !== '') {
                $metadata['refund_transaction_id'] = $refundTransactionId;
            }

            if ($amountCents !== null) {
                $metadata['refund_amount_cents'] = $amountCents;
            }

            $payment->forceFill([
                'status' => 'refunded',
                'metadata' => $metadata,
            ])->save();

            $this->setRawAttributes($payment->getAttributes(), true);
            $this->syncOriginal();
        });
    }

    public function markFailed(?string $failureCode = null, ?string $failureMessage = null): void
    {
        if (in_array($this->status, ['paid', 'refunded'], true)) {
            return;
        }

        DB::transaction(function () use ($failureCode, $failureMessage): void {
            $order = $this->order;

            if ($order instanceof Order) {
                $order->reservations()->where('status', 'held')->update(['status' => 'released']);
                $order->forceFill(['payment_status' => 'failed'])->save();
            }

            $this->forceFill([
                'status' => 'failed',
                'failure_code' => $failureCode,
                'failure_message' => $failureMessage,
            ])->save();
        });
    }
}
