<?php

use App\Support\Theme;

test('the store keeps its current colors until theme config changes', function () {
    config([
        'theme.accent' => '#51649e',
        'theme.ink' => '#262f42',
        'theme.surface' => '#f3f5f7',
        'theme.deal' => '#b15d6a',
        'theme.star' => '#8d9bc9',
    ]);

    $css = implode('', Theme::variables());

    expect($css)
        ->toContain('#51649e')
        ->toContain('#262f42')
        ->toContain('#f3f5f7')
        ->toContain('#b15d6a')
        ->toContain('#8d9bc9');
});

test('changing the accent recolors the storefront scale', function () {
    config(['theme.accent' => '#0f6e56']);

    $variables = Theme::variables();

    expect($variables['theme-accent'])->toBe('#0f6e56')
        ->and($variables['theme-gold-500'])->toBe('#0f6e56')
        ->and($variables['theme-gold-600'])->not->toBe('#425488')
        ->and(Theme::accent())->toBe('#0f6e56');
});

test('an invalid theme color falls back to the shipped palette', function () {
    config(['theme.accent' => 'not-a-color']);

    expect(Theme::accent())->toBe('#51649e');
});

test('the filament palette keeps the brand hex on shade 600', function () {
    $palette = Theme::filamentPalette('#e02010');

    expect($palette[600])->toBe('#e02010')
        ->and($palette[500])->not->toBe('#e02010')
        ->and($palette[50])->toStartWith('#');
});
