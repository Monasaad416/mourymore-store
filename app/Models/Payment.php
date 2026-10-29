<?php

namespace App\Models;

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

    public function markPaid(?string $transactionId = null): void
    {
        if ($this->status === 'paid') {
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
            ])->save();
        });
    }

    public function markFailed(?string $failureCode = null, ?string $failureMessage = null): void
    {
        if ($this->status === 'paid') {
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
