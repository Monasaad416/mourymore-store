<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $product_id
 * @property string $author
 * @property int|null $pages
 * @property string|null $file_path
 */
#[Fillable([
    'product_id',
    'author',
    'pages',
    'file_path',
])]
class BookDetail extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['author'];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayAuthor(): string
    {
        return (string) $this->author;
    }

    public function isDigital(): bool
    {
        return filled($this->file_path);
    }
}
