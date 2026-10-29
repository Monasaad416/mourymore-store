<?php

namespace App\Support;

use App\Models\ShippingMethod;
use App\Models\SiteSetting;

class Shipping
{
    /**
     * @return array{fee: float, eta: string, note: string, method_id: int|null, method_name: string|null, free_over: float|null}
     */
    public static function quote(string $governorate, float $subtotal): array
    {
        $method = ShippingMethod::forGovernorate($governorate);

        if ($method instanceof ShippingMethod) {
            $freeOver = $method->free_over !== null && (float) $method->free_over > 0
                ? (float) $method->free_over
                : null;

            return self::payload($method->feeFor($subtotal), $method->displayEta(), $method->id, $method->displayName(), $freeOver);
        }

        $greaterCairo = self::list('greater_cairo');
        $delta = self::list('delta');
        $freeOver = SiteSetting::current()->freeShippingOver();
        $inCairo = in_array($governorate, $greaterCairo, true);
        $fee = match (true) {
            $inCairo => 45.0,
            in_array($governorate, $delta, true) => 65.0,
            default => 90.0,
        };

        if ($inCairo && $subtotal >= $freeOver) {
            $fee = 0.0;
        }

        $eta = match (true) {
            $inCairo => __('store.shipping.eta_cairo'),
            in_array($governorate, $delta, true) => __('store.shipping.eta_delta'),
            default => __('store.shipping.eta_other'),
        };

        return self::payload($fee, $eta, null, null, $inCairo ? $freeOver : null);
    }

    /**
     * @return array{fee: float, eta: string, note: string, method_id: int|null, method_name: string|null, free_over: float|null}
     */
    private static function payload(float $fee, string $eta, ?int $methodId, ?string $methodName, ?float $freeOver): array
    {
        $note = $fee === 0.0
            ? __('store.shipping.free')
            : __('store.shipping.paid', ['fee' => Money::format($fee)]);

        return [
            'fee' => $fee,
            'eta' => $eta,
            'note' => $note,
            'method_id' => $methodId,
            'method_name' => $methodName,
            'free_over' => $freeOver,
        ];
    }

    /**
     * @return list<string>
     */
    public static function governorates(): array
    {
        return self::list('governorates');
    }

    public static function label(string $governorate): string
    {
        $key = 'store.governorates.'.$governorate;
        $label = __($key);

        return $label === $key ? $governorate : $label;
    }

    /**
     * @return list<string>
     */
    private static function list(string $key): array
    {
        $values = config('store.'.$key, []);

        if (! is_array($values)) {
            return [];
        }

        return array_values(array_map(strval(...), $values));
    }
}
