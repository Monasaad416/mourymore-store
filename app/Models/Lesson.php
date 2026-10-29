<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property int $sort
 * @property int|null $duration_minutes
 * @property bool $is_preview
 */
#[Fillable([
    'product_id',
    'title',
    'sort',
    'duration_minutes',
    'is_preview',
])]
class Lesson extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['title'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayTitle(): string
    {
        return (string) $this->title;
    }
}
