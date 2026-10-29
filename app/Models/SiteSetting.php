<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Spatie\Translatable\HasTranslations;
use Throwable;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $tagline
 * @property string|null $logo
 * @property string|null $favicon
 * @property string|null $cover
 * @property string $currency_code
 * @property string $currency_symbol_en
 * @property string $currency_symbol_ar
 * @property float $tax_percent
 * @property bool $prices_include_tax
 * @property float|null $free_shipping_over
 * @property string|null $support_email
 * @property string|null $support_phone
 * @property string|null $support_whatsapp
 */
#[Fillable([
    'name',
    'tagline',
    'logo',
    'favicon',
    'cover',
    'currency_code',
    'currency_symbol_en',
    'currency_symbol_ar',
    'tax_percent',
    'prices_include_tax',
    'free_shipping_over',
    'support_email',
    'support_phone',
    'support_whatsapp',
])]
class SiteSetting extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'tagline'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
            'prices_include_tax' => 'boolean',
            'free_shipping_over' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_setting.id'));
        static::deleted(fn () => Cache::forget('site_setting.id'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultAttributes(): array
    {
        return [
            'name' => [
                'en' => (string) config('store.name', 'mourymore_store'),
                'ar' => (string) config('store.name', 'mourymore_store'),
            ],
            'tagline' => [
                'en' => (string) config('store.tagline', ''),
                'ar' => (string) config('store.tagline', ''),
            ],
            'logo' => (string) config('store.logo'),
            'favicon' => (string) config('store.logo'),
            'cover' => (string) config('store.cover'),
            'currency_code' => 'EGP',
            'currency_symbol_en' => 'EGP',
            'currency_symbol_ar' => 'ج.م',
            'tax_percent' => 0,
            'prices_include_tax' => true,
            'free_shipping_over' => (float) config('store.free_shipping_over', 1500),
            'support_email' => null,
            'support_phone' => null,
            'support_whatsapp' => null,
        ];
    }

    public static function current(): self
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return static::defaults();
            }
        } catch (Throwable) {
            return static::defaults();
        }

        $id = Cache::remember('site_setting.id', now()->addHour(), function (): int {
            $settings = static::query()->first();

            if ($settings instanceof self) {
                return (int) $settings->id;
            }

            return (int) static::query()->create(static::defaultAttributes())->id;
        });

        return static::query()->find($id) ?? static::defaults();
    }

    public static function defaults(): self
    {
        $settings = new self;
        $settings->forceFill(self::defaultAttributes());

        return $settings;
    }

    public function displayName(): string
    {
        $name = trim((string) $this->name);

        return $name !== '' ? $name : (string) config('store.name', 'mourymore_store');
    }

    public function displayTagline(): string
    {
        return trim((string) $this->tagline);
    }

    public function logoUrl(): string
    {
        return Media::url($this->logo ?: (string) config('store.logo'));
    }

    public function faviconUrl(): string
    {
        return Media::url($this->favicon ?: $this->logo ?: (string) config('store.logo'));
    }

    public function coverUrl(): string
    {
        return Media::url($this->cover ?: (string) config('store.cover'));
    }

    public function currencyCode(): string
    {
        $code = strtoupper(trim((string) $this->currency_code));

        return $code !== '' ? $code : 'EGP';
    }

    public function currencySymbol(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $symbol = $locale === 'ar'
            ? trim((string) $this->currency_symbol_ar)
            : trim((string) $this->currency_symbol_en);

        if ($symbol === '') {
            return $this->currencyCode();
        }

        return $symbol;
    }

    public function taxPercent(): float
    {
        return max(0, (float) $this->tax_percent);
    }

    public function pricesIncludeTax(): bool
    {
        return (bool) $this->prices_include_tax;
    }

    public function freeShippingOver(): float
    {
        if ($this->free_shipping_over === null) {
            return (float) config('store.free_shipping_over', 1500);
        }

        return max(0, (float) $this->free_shipping_over);
    }

    public function taxOn(float $amount): float
    {
        $rate = $this->taxPercent();

        if ($rate <= 0 || $amount <= 0) {
            return 0.0;
        }

        if ($this->pricesIncludeTax()) {
            return round($amount - ($amount / (1 + ($rate / 100))), 2);
        }

        return round($amount * ($rate / 100), 2);
    }

    public function addTaxTo(float $amount): float
    {
        if ($this->pricesIncludeTax() || $this->taxPercent() <= 0) {
            return round($amount, 2);
        }

        return round($amount + $this->taxOn($amount), 2);
    }
}
