<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
 * @property CarbonInterface|null $starts_at
 * @property CarbonInterface|null $ends_at
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

        if ($this->starts_at instanceof CarbonInterface && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at instanceof CarbonInterface && $this->ends_at->isPast()) {
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

    /**
     * Atomically consume one use. Safe under concurrency when max_uses is set.
     * Prefer calling after lockUsableByCode() inside the same DB transaction.
     */
    public function recordUse(): bool
    {
        if ($this->max_uses === null) {
            $this->increment('used_count');

            return true;
        }

        $affected = static::query()
            ->whereKey($this->id)
            ->where('used_count', '<', $this->max_uses)
            ->increment('used_count');

        if ($affected < 1) {
            return false;
        }

        $this->used_count = (int) $this->used_count + 1;

        return true;
    }

    public static function findUsableByCode(string $code, float $subtotal): ?self
    {
        return static::resolveUsableByCode($code, $subtotal, forUpdate: false);
    }

    /**
     * Same rules as findUsableByCode, but locks the offer row (FOR UPDATE).
     * Call only inside an open DB transaction so concurrent checkouts cannot
     * both pass a max_uses / date check and oversell the last redemption.
     */
    public static function lockUsableByCode(string $code, float $subtotal): ?self
    {
        return static::resolveUsableByCode($code, $subtotal, forUpdate: true);
    }

    private static function resolveUsableByCode(string $code, float $subtotal, bool $forUpdate): ?self
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        $query = static::query()->whereRaw('UPPER(code) = ?', [$normalized]);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $offer = $query->first();

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
