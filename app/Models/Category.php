<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $image
 * @property string|null $blurb
 * @property int $sort
 */
#[Fillable(['name', 'slug', 'image', 'blurb', 'sort'])]
class Category extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'blurb'];

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function displayName(): string
    {
        return (string) $this->name;
    }

    public function displayBlurb(): ?string
    {
        $blurb = (string) $this->blurb;

        return $blurb !== '' ? $blurb : null;
    }
}
