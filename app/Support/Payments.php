<?php

namespace App\Support;

use App\Models\PaymentMethod;

class Payments
{
    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        $codes = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->pluck('code')
            ->all();

        if ($codes !== []) {
            return array_values(array_map(strval(...), $codes));
        }

        $raw = config('store.payments', []);

        return is_array($raw) ? array_values(array_filter(array_keys($raw), is_string(...))) : [];
    }

    public static function collectsOnDelivery(string $code): bool
    {
        $method = PaymentMethod::query()->where('code', $code)->first();

        if ($method instanceof PaymentMethod) {
            return $method->collects_on_delivery;
        }

        return $code === 'cod';
    }

    /**
     * @return array<string, array{label: string, hint: string, tag: string}>
     */
    public static function options(): array
    {
        $methods = PaymentMethod::query()->where('is_active', true)->orderBy('sort')->get();

        if ($methods->isNotEmpty()) {
            $options = [];

            foreach ($methods as $method) {
                $options[$method->code] = [
                    'label' => $method->displayName(),
                    'hint' => $method->displayHint(),
                    'tag' => $method->collects_on_delivery ? __('store.payments.cod.tag') : __('store.payments.card.tag'),
                ];
            }

            return $options;
        }

        $raw = config('store.payments', []);

        if (! is_array($raw)) {
            return [];
        }

        $options = [];

        foreach (array_keys($raw) as $key) {
            if (! is_string($key)) {
                continue;
            }

            $options[$key] = [
                'label' => self::line($key, 'label'),
                'hint' => self::line($key, 'hint'),
                'tag' => self::line($key, 'tag'),
            ];
        }

        return $options;
    }

    public static function label(string $key): string
    {
        $method = PaymentMethod::query()->where('code', $key)->first();

        if ($method instanceof PaymentMethod) {
            return $method->displayName();
        }

        return self::line($key, 'label');
    }

    private static function line(string $key, string $field): string
    {
        $translationKey = 'store.payments.'.$key.'.'.$field;
        $label = __($translationKey);

        if ($label !== $translationKey) {
            return $label;
        }

        $raw = config('store.payments.'.$key.'.'.$field);

        return is_string($raw) ? $raw : $key;
    }
}
