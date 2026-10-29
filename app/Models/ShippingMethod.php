<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $eta
 * @property string $fee
 * @property string|null $free_over
 * @property list<string> $governorates
 * @property bool $is_active
 * @property int $sort
 */
#[Fillable([
    'code',
    'name',
    'eta',
    'fee',
    'free_over',
    'governorates',
    'is_active',
    'sort',
])]
class ShippingMethod extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'eta'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'free_over' => 'decimal:2',
            'governorates' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<ShippingMethod>  $query
     * @return Builder<ShippingMethod>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    public static function forGovernorate(string $governorate): ?self
    {
        $methods = static::query()->active()->get();
        $match = $methods->first(fn (self $method): bool => in_array($governorate, $method->governorates, true));

        return $match ?? $methods->firstWhere('code', 'other');
    }

    public function displayName(): string
    {
        return (string) $this->name;
    }

    public function displayEta(): string
    {
        return (string) $this->eta;
    }

    public function feeFor(float $subtotal): float
    {
        $freeOver = $this->free_over;

        if ($freeOver !== null && $subtotal >= (float) $freeOver) {
            return 0.0;
        }

        return (float) $this->fee;
    }
}
