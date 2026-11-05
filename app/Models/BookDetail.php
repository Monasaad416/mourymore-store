<?php

namespace App\Models;

use App\Enums\BookFormat;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $product_id
 * @property string $author
 * @property int|null $pages
 * @property bool $offers_print
 * @property bool $offers_digital
 * @property string|null $digital_price
 * @property string|null $file_path
 */
#[Fillable([
    'product_id',
    'author',
    'pages',
    'offers_print',
    'offers_digital',
    'digital_price',
    'file_path',
])]
class BookDetail extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['author'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'offers_print' => 'boolean',
            'offers_digital' => 'boolean',
            'digital_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (BookDetail $detail): void {
            $detail->syncProductType();
        });
    }

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

    public function sellsPrint(): bool
    {
        return (bool) $this->offers_print;
    }

    public function sellsDigital(): bool
    {
        return (bool) $this->offers_digital && filled($this->file_path);
    }

    /**
     * @return list<BookFormat>
     */
    public function availableFormats(): array
    {
        $formats = [];

        if ($this->sellsPrint()) {
            $formats[] = BookFormat::Print;
        }

        if ($this->sellsDigital()) {
            $formats[] = BookFormat::Digital;
        }

        return $formats;
    }

    public function syncProductType(): void
    {
        $product = $this->product()->first();

        if (! $product instanceof Product) {
            return;
        }

        // Print (alone or with digital) => physical stock/shipping. Digital-only => digital.
        $type = $this->offers_print
            ? ProductType::Physical
            : ProductType::Digital;

        if ($product->type !== $type) {
            $product->forceFill(['type' => $type])->saveQuietly();
        }
    }
}
