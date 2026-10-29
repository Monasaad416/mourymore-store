<?php

namespace App\Models;

use App\Enums\ProductKind;
use App\Enums\ProductType;
use App\Support\Media;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int|null $category_id
 * @property ProductType $type
 * @property ProductKind $kind
 * @property string $name
 * @property string $slug
 * @property string|null $brand
 * @property string $description
 * @property array<int, string> $highlights
 * @property string $price
 * @property string|null $compare_price
 * @property string $rating
 * @property int $reviews_count
 * @property int $stock
 * @property string|null $badge
 * @property string $image
 * @property bool $is_featured
 * @property bool $is_deal
 */
#[Fillable([
    'category_id',
    'type',
    'kind',
    'name',
    'slug',
    'brand',
    'description',
    'highlights',
    'price',
    'compare_price',
    'rating',
    'reviews_count',
    'stock',
    'badge',
    'image',
    'is_featured',
    'is_deal',
])]
class Product extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['name', 'brand', 'description', 'highlights'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'kind' => ProductKind::class,
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_featured' => 'boolean',
            'is_deal' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasOne<BookDetail, $this>
     */
    public function bookDetail(): HasOne
    {
        return $this->hasOne(BookDetail::class);
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    /**
     * @return HasMany<StockReservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function heldQuantity(): int
    {
        return (int) $this->reservations()
            ->where('status', 'held')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->sum('qty');
    }

    public function availableQuantity(): int
    {
        if (! $this->tracksStock()) {
            return PHP_INT_MAX;
        }

        return max(0, $this->stock - $this->heldQuantity());
    }

    public function purchaseLimit(): int
    {
        if (! $this->tracksStock()) {
            return 1;
        }

        return min(8, $this->availableQuantity());
    }

    public function imageUrl(): string
    {
        return Media::url($this->image);
    }

    public function formattedPrice(): string
    {
        return Money::format($this->price);
    }

    public function formattedComparePrice(): ?string
    {
        if ($this->compare_price === null) {
            return null;
        }

        return Money::format($this->compare_price);
    }

    public function discountPercent(): ?int
    {
        $compare = (float) $this->compare_price;
        $price = (float) $this->price;

        if ($compare <= $price || $compare <= 0) {
            return null;
        }

        return (int) round((1 - ($price / $compare)) * 100);
    }

    public function tracksStock(): bool
    {
        return ($this->type ?? ProductType::Physical) !== ProductType::Digital;
    }

    public function inStock(): bool
    {
        return $this->availableQuantity() > 0;
    }

    public function canPurchase(int $qty): bool
    {
        return $qty > 0 && $qty <= $this->purchaseLimit();
    }

    public function displayName(): string
    {
        return (string) $this->name;
    }

    public function displayDescription(): string
    {
        return (string) $this->description;
    }

    /**
     * @return list<string>
     */
    public function displayHighlights(): array
    {
        $highlights = $this->highlights;

        if (! is_array($highlights)) {
            return [];
        }

        return array_values(array_map(strval(...), $highlights));
    }
}
