<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $name
 * @property string|null $role
 * @property string $quote
 * @property float|null $rating
 * @property int $sort
 * @property bool $is_published
 */
#[Fillable(['name', 'role', 'quote', 'rating', 'sort', 'is_published'])]
class Testimonial extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['role', 'quote'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function displayRole(): ?string
    {
        $role = trim((string) $this->role);

        return $role !== '' ? $role : null;
    }

    public function displayQuote(): string
    {
        return (string) $this->quote;
    }
}
