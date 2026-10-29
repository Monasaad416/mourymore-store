<?php

namespace App\Support;

class Media
{
    public static function url(?string $path): string
    {
        if ($path === null || $path === '') {
            return self::rootRelative('images/catalog/fallback.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, 'storage/')) {
            return self::rootRelative($path);
        }

        // Prefer root-relative URLs so Filament/img tags work even when
        // APP_URL has an underscore host (invalid for FILTER_VALIDATE_URL).
        return self::rootRelative('storage/'.ltrim($path, '/'));
    }

    private static function rootRelative(string $path): string
    {
        return '/'.ltrim($path, '/');
    }
}
