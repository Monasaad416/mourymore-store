<?php

namespace App\Models;

use App\Models\Concerns\PublishesContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_published
 * @property bool $show_in_footer
 * @property int $sort
 */
#[Fillable([
    'slug',
    'title',
    'body',
    'meta_title',
    'meta_description',
    'is_published',
    'show_in_footer',
    'sort',
    'published_at',
])]
class Page extends Model
{
    use HasTranslations, PublishesContent;

    /** @var list<string> */
    public array $translatable = ['title', 'body', 'meta_title', 'meta_description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_footer' => 'boolean',
            'sort' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeInFooter(Builder $query): Builder
    {
        return $query->published()->where('show_in_footer', true)->orderBy('sort')->orderBy('id');
    }

    public function displayTitle(): string
    {
        return (string) $this->title;
    }

    public function displayBody(): string
    {
        return (string) $this->body;
    }

    public function displayMetaTitle(): string
    {
        $meta = trim((string) $this->meta_title);

        return $meta !== '' ? $meta : $this->displayTitle();
    }

    public function displayMetaDescription(): string
    {
        $meta = trim((string) $this->meta_description);

        if ($meta !== '') {
            return $meta;
        }

        return Str::limit(strip_tags($this->displayBody()), 160);
    }
}
