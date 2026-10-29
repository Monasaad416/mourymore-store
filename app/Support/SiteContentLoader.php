<?php

namespace App\Support;

use App\Models\SiteContent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteContentLoader
{
    public static function forgetCache(): void
    {
        foreach (['en', 'ar'] as $locale) {
            Cache::forget(static::cacheKey($locale));
        }

        if (app()->bound('translator')) {
            app('translator')->setLoaded([]);
        }
    }

    public static function cacheKey(string $locale): string
    {
        return 'site_contents.'.$locale;
    }

    /**
     * @return array<string, string>
     */
    public static function flatForLocale(string $locale): array
    {
        try {
            if (! Schema::hasTable('site_contents')) {
                return [];
            }
        } catch (Throwable) {
            return [];
        }

        return Cache::remember(static::cacheKey($locale), now()->addHour(), function () use ($locale): array {
            $lines = [];

            foreach (SiteContent::query()->orderBy('sort')->orderBy('id')->cursor() as $content) {
                $value = trim((string) $content->getTranslation('value', $locale, false));

                if ($value === '') {
                    continue;
                }

                $lines[$content->key] = $value;
            }

            return $lines;
        });
    }

    /**
     * @param  array<string, mixed>  $store
     * @return array<string, mixed>
     */
    public static function mergeIntoStore(array $store, string $locale): array
    {
        foreach (static::flatForLocale($locale) as $key => $value) {
            if (str_starts_with($key, 'settings.')) {
                continue;
            }

            Arr::set($store, $key, $value);
        }

        return $store;
    }
}
