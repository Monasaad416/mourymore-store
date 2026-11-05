<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Cookie;

class CustomerFlash
{
    public const COOKIE = 'store_flash';

    /**
     * @return array{0: string, 1: Cookie}
     */
    public static function redirectWith(string $key, string $message): array
    {
        return [
            $message,
            cookie(self::COOKIE, $key, 1, '/', null, false, true, false, 'lax'),
        ];
    }

    public static function messageFromRequest(): ?string
    {
        $sessionMessage = session('flash_success') ?? session('status');

        if (filled($sessionMessage)) {
            return (string) $sessionMessage;
        }

        $key = request()->cookie(self::COOKIE);

        return match ($key) {
            'login' => __('store.auth.flash.login'),
            'register' => __('store.auth.flash.register'),
            'logout' => __('store.auth.flash.logout'),
            default => null,
        };
    }

    public static function forgetCookie(): Cookie
    {
        return cookie()->forget(self::COOKIE);
    }
}
