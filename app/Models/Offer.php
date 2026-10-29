<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property string|null $name
 * @property string $type
 * @property float $value
 * @property float|null $min_subtotal
 * @property int|null $max_uses
 * @property int $used_count
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $is_active
 * @property int $sort
 */
#[Fillable([
    'code',
    'name',
    'type',
    'value',
    'min_subtotal',
    'max_uses',
    'used_count',
    'starts_at',
    'ends_at',
    'is_active',
    'sort',
])]
class Offer extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_subtotal' => 'decimal:2',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function displayName(): string
    {
        $name = trim((string) $this->name);

        return $name !== '' ? $name : $this->code;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn (Builder $builder): Builder => $builder
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $builder): Builder => $builder
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $now));
    }

    public function isUsable(float $subtotal = 0): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at instanceof Carbon && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at instanceof Carbon && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($this->min_subtotal !== null && $subtotal < (float) $this->min_subtotal) {
            return false;
        }

        return true;
    }

    public function discountAmount(float $subtotal): float
    {
        if (! $this->isUsable($subtotal)) {
            return 0.0;
        }

        $amount = $this->type === 'fixed'
            ? (float) $this->value
            : $subtotal * ((float) $this->value / 100);

        return round(min($amount, $subtotal), 2);
    }

    public function recordUse(): void
    {
        $this->increment('used_count');
    }

    public static function findUsableByCode(string $code, float $subtotal): ?self
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        $offer = static::query()
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();

        if (! $offer instanceof self || ! $offer->isUsable($subtotal)) {
            return null;
        }

        return $offer;
    }

    public static function featuredCode(): string
    {
        $offer = static::query()
            ->currentlyActive()
            ->orderBy('sort')
            ->orderBy('id')
            ->first();

        if ($offer instanceof self) {
            return $offer->code;
        }

        return (string) config('store.promo_code', '');
    }
}
