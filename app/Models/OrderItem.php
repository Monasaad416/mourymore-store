<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property array<string, string> $name
 * @property string|null $image
 * @property int $qty
 * @property string $price
 * @property string $line_total
 */
#[Fillable([
    'order_id',
    'product_id',
    'product_type',
    'name',
    'image',
    'qty',
    'price',
    'line_total',
])]
class OrderItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'price' => 'decimal:2',
            'line_total' => 'decimal:2',
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
     * @return HasOne<Entitlement, $this>
     */
    public function entitlement(): HasOne
    {
        return $this->hasOne(Entitlement::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayName(): string
    {
        return self::labelFromName($this->name);
    }

    public static function labelFromName(mixed $name): string
    {
        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : $name;
        }

        if (is_string($name)) {
            return $name;
        }

        if (! is_array($name)) {
            return '';
        }

        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');
        $value = $name[$locale] ?? (is_string($fallback) ? ($name[$fallback] ?? null) : null);
        $value ??= reset($name);

        return is_string($value) ? $value : '';
    }

    public function imageUrl(): string
    {
        return Media::url($this->image);
    }
}
