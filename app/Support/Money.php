<?php

namespace App\Support;

use App\Models\SiteSetting;

class Money
{
    public static function currencyCode(): string
    {
        return SiteSetting::current()->currencyCode();
    }

    public static function format(float|int|string|null $amount): string
    {
        $value = (float) $amount;
        $formatted = fmod($value, 1.0) === 0.0
            ? number_format($value, 0)
            : number_format($value, 2);

        $symbol = SiteSetting::current()->currencySymbol();

        if (app()->getLocale() === 'ar') {
            return $formatted.' '.$symbol;
        }

        return $symbol.' '.$formatted;
    }
}
