<?php

namespace App\Models;

use App\Support\SiteContentLoader;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $group
 * @property string $key
 * @property string|null $value
 * @property string $input
 * @property int $sort
 */
#[Fillable([
    'group',
    'key',
    'value',
    'input',
    'sort',
])]
class SiteContent extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['value'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => SiteContentLoader::forgetCache());
        static::deleted(fn () => SiteContentLoader::forgetCache());
    }

    public static function line(string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $value = SiteContentLoader::flatForLocale($locale)[$key] ?? null;

        if (filled($value)) {
            return (string) $value;
        }

        if ($locale !== 'en') {
            $fallback = SiteContentLoader::flatForLocale('en')[$key] ?? null;

            if (filled($fallback)) {
                return (string) $fallback;
            }
        }

        return null;
    }

    public static function setting(string $key, ?string $fallback = null): string
    {
        $site = SiteSetting::current();

        return match ($key) {
            'name' => $site->displayName(),
            'tagline' => $site->displayTagline() !== ''
                ? $site->displayTagline()
                : (string) ($fallback ?? config('store.tagline', '')),
            'logo' => $site->logoUrl(),
            'favicon' => $site->faviconUrl(),
            'cover' => $site->coverUrl(),
            default => static::line('settings.'.$key)
                ?? (string) ($fallback ?? config('store.'.$key, '')),
        };
    }
}
