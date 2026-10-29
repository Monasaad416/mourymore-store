<?php

namespace App\Support;

class Theme
{
    /** @var array<string, string> */
    private const DEFAULTS = [
        'accent' => '#51649e',
        'ink' => '#262f42',
        'surface' => '#f3f5f7',
        'deal' => '#b15d6a',
        'star' => '#8d9bc9',
        'text' => '#1b2330',
    ];

    /**
     * The shipped storefront palette. Used until the matching base color changes.
     *
     * @var array<string, array<int, string>>
     */
    private const PRESET = [
        'nile' => [
            50 => '#f2f4f8',
            100 => '#e4e9f2',
            200 => '#d0d8e8',
            700 => '#4e5d78',
            800 => '#343f56',
            900 => '#262f42',
            950 => '#1a2233',
        ],
        'gold' => [
            300 => '#d7def3',
            400 => '#b8c4e8',
            500 => '#51649e',
            600 => '#425488',
        ],
        'sand' => [
            50 => '#f3f5f7',
            100 => '#e7ebf0',
            200 => '#d5dce5',
        ],
    ];

    public static function accent(): string
    {
        return self::color('accent');
    }

    public static function ink(): string
    {
        return self::color('ink');
    }

    public static function star(): string
    {
        return self::color('star');
    }

    /**
     * Filament shade map that keeps the brand hex vivid.
     * Color::hex() washes chroma into a muddy coral, so buttons stay on mixes of the real color.
     *
     * @return array<int, string>
     */
    public static function filamentPalette(string $hex): array
    {
        $hex = self::normalizeHex($hex) ?? self::DEFAULTS['accent'];

        return [
            50 => self::mix($hex, '#ffffff', 0.95),
            100 => self::mix($hex, '#ffffff', 0.88),
            200 => self::mix($hex, '#ffffff', 0.75),
            300 => self::mix($hex, '#ffffff', 0.55),
            400 => self::mix($hex, '#ffffff', 0.3),
            500 => self::mix($hex, '#ffffff', 0.12),
            600 => $hex,
            700 => self::mix($hex, '#000000', 0.14),
            800 => self::mix($hex, '#000000', 0.28),
            900 => self::mix($hex, '#000000', 0.4),
            950 => self::mix($hex, '#000000', 0.62),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function variables(): array
    {
        $accent = self::accent();
        $ink = self::ink();
        $surface = self::color('surface');
        $text = $ink === self::DEFAULTS['ink'] ? self::DEFAULTS['text'] : $ink;

        $variables = [
            'theme-ink' => $text,
            'theme-deal' => self::color('deal'),
            'theme-star' => self::color('star'),
            'theme-accent' => $accent,
            'theme-on-accent' => self::isLight($accent) ? $text : '#ffffff',
        ];

        foreach (self::nile($ink) as $shade => $hex) {
            $variables['theme-nile-'.$shade] = $hex;
        }

        foreach (self::gold($accent) as $shade => $hex) {
            $variables['theme-gold-'.$shade] = $hex;
        }

        foreach (self::sand($surface, $ink) as $shade => $hex) {
            $variables['theme-sand-'.$shade] = $hex;
        }

        return $variables;
    }

    /**
     * @return array<int, string>
     */
    private static function nile(string $ink): array
    {
        if ($ink === self::DEFAULTS['ink']) {
            return self::PRESET['nile'];
        }

        return [
            50 => self::mix($ink, '#ffffff', 0.94),
            100 => self::mix($ink, '#ffffff', 0.88),
            200 => self::mix($ink, '#ffffff', 0.78),
            700 => self::mix($ink, '#ffffff', 0.38),
            800 => self::mix($ink, '#ffffff', 0.18),
            900 => $ink,
            950 => self::mix($ink, '#000000', 0.28),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function gold(string $accent): array
    {
        if ($accent === self::DEFAULTS['accent']) {
            return self::PRESET['gold'];
        }

        return [
            300 => self::mix($accent, '#ffffff', 0.72),
            400 => self::mix($accent, '#ffffff', 0.55),
            500 => $accent,
            600 => self::mix($accent, '#000000', 0.16),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function sand(string $surface, string $ink): array
    {
        if ($surface === self::DEFAULTS['surface']) {
            return self::PRESET['sand'];
        }

        return [
            50 => $surface,
            100 => self::mix($surface, $ink, 0.05),
            200 => self::mix($surface, $ink, 0.1),
        ];
    }

    private static function color(string $key): string
    {
        return self::normalizeHex((string) config('theme.'.$key)) ?? self::DEFAULTS[$key];
    }

    private static function normalizeHex(string $value): ?string
    {
        $value = strtolower(trim($value));

        if (preg_match('/^#[0-9a-f]{6}$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^#([0-9a-f]{3})$/', $value, $matches) === 1) {
            return '#'.$matches[1][0].$matches[1][0].$matches[1][1].$matches[1][1].$matches[1][2].$matches[1][2];
        }

        return null;
    }

    private static function mix(string $hex, string $with, float $amount): string
    {
        [$red, $green, $blue] = self::rgb($hex);
        [$targetRed, $targetGreen, $targetBlue] = self::rgb($with);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($red + (($targetRed - $red) * $amount)),
            (int) round($green + (($targetGreen - $green) * $amount)),
            (int) round($blue + (($targetBlue - $blue) * $amount)),
        );
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function rgb(string $hex): array
    {
        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }

    private static function isLight(string $hex): bool
    {
        [$red, $green, $blue] = array_map(
            function (int $channel): float {
                $channel /= 255;

                return $channel <= 0.03928
                    ? $channel / 12.92
                    : (($channel + 0.055) / 1.055) ** 2.4;
            },
            self::rgb($hex),
        );

        return (0.2126 * $red) + (0.7152 * $green) + (0.0722 * $blue) > 0.55;
    }
}
