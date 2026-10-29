<?php

namespace App\Models;

use App\Models\Concerns\PublishesContent;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $excerpt
 * @property string $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $image
 * @property bool $is_published
 */
#[Fillable(['slug', 'title', 'excerpt', 'body', 'meta_title', 'meta_description', 'image', 'is_published', 'published_at'])]
class Article extends Model
{
    use HasTranslations, PublishesContent;

    /** @var list<string> */
    public array $translatable = ['title', 'excerpt', 'body', 'meta_title', 'meta_description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function displayTitle(): string
    {
        return (string) $this->title;
    }

    public function displayExcerpt(): string
    {
        return (string) $this->excerpt;
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

        return $meta !== '' ? $meta : $this->displayExcerpt();
    }

    public function imageUrl(): ?string
    {
        if ($this->image === null || $this->image === '') {
            return null;
        }

        return Media::url($this->image);
    }
}
