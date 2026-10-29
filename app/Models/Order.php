<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $number
 * @property int|null $user_id
 * @property string $customer_name
 * @property string $phone
 * @property string|null $email
 * @property string $governorate
 * @property string $city
 * @property string $address
 * @property string|null $notes
 * @property string $payment_method
 * @property string|null $promo_code
 * @property string $subtotal
 * @property string $discount
 * @property string $tax
 * @property string $shipping
 * @property string $total
 * @property string $currency
 * @property string $status
 */
#[Fillable([
    'number',
    'user_id',
    'customer_name',
    'phone',
    'email',
    'governorate',
    'city',
    'address',
    'notes',
    'payment_method',
    'shipping_method_id',
    'shipping_method_name',
    'tracking_number',
    'payment_status',
    'delivery_status',
    'promo_code',
    'subtotal',
    'discount',
    'tax',
    'shipping',
    'total',
    'currency',
    'status',
])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<StockReservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    /**
     * @return HasMany<Entitlement, $this>
     */
    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function grantDigitalAccess(): void
    {
        $this->loadMissing('items');

        foreach ($this->items as $item) {
            if ($item->product_type !== 'digital' || $item->product_id === null) {
                continue;
            }

            Entitlement::query()->firstOrCreate(
                ['order_item_id' => $item->id],
                [
                    'user_id' => $this->user_id,
                    'order_id' => $this->id,
                    'product_id' => $item->product_id,
                    'granted_at' => now(),
                ],
            );
        }
    }
}
