<?php

namespace App\Services\Paymob;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymobClient
{
    public function isConfigured(): bool
    {
        if (! (bool) config('paymob.enabled', true)) {
            return false;
        }

        return filled(config('paymob.secret_key'))
            && filled(config('paymob.public_key'))
            && filled(config('paymob.hmac_secret'))
            && $this->paymentMethods() !== [];
    }

    /**
     * Create a Paymob Intention and return a hosted Unified Checkout URL.
     *
     * @return array{checkout_url: string, client_secret: string, intention_id: string|null, raw: array<string, mixed>}
     */
    public function createCheckout(Order $order, Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Paymob is not configured.');
        }

        $amountCents = (int) round(((float) $payment->amount) * 100);
        $currency = strtoupper((string) ($payment->currency ?: $order->currency ?: 'EGP'));
        $reference = (string) ($payment->merchant_reference ?: $order->number.'-'.$payment->id);

        [$firstName, $lastName] = $this->splitName($order->customer_name);

        $payload = [
            'amount' => $amountCents,
            'currency' => $currency,
            'payment_methods' => $this->paymentMethods(),
            'items' => $this->intentionItems($order, $amountCents),
            'billing_data' => [
                'apartment' => 'NA',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'street' => filled($order->address) ? mb_substr((string) $order->address, 0, 100) : 'NA',
                'building' => 'NA',
                'phone_number' => (string) $order->phone,
                'city' => filled($order->city) ? (string) $order->city : 'NA',
                'country' => 'EGY',
                'email' => filled($order->email) ? (string) $order->email : 'customer@example.com',
                'floor' => 'NA',
                'state' => filled($order->governorate) ? (string) $order->governorate : 'NA',
            ],
            'special_reference' => $reference,
            // Intention API has one browser return for both outcomes.
            // success=true marks paid; success=false (cancel / decline) marks failed.
            // notification_url is the server webhook. On *.test Paymob cannot call it;
            // the browser return above is what clears or keeps the cart locally.
            'notification_url' => route('paymob.webhook', absolute: true),
            'redirection_url' => route('paymob.return', ['order' => $order->number], absolute: true),
        ];

        $response = Http::baseUrl((string) config('paymob.base_url'))
            ->acceptJson()
            ->asJson()
            ->withToken((string) config('paymob.secret_key'), 'Token')
            ->timeout(30)
            ->post('/v1/intention/', $payload);

        if ($response->failed()) {
            Log::warning('Paymob intention failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
                'reference' => $reference,
            ]);

            throw new RequestException($response);
        }

        /** @var array<string, mixed> $data */
        $data = $response->json() ?? [];
        $clientSecret = (string) ($data['client_secret'] ?? '');

        if ($clientSecret === '') {
            throw new RuntimeException('Paymob intention response missing client_secret.');
        }

        return [
            'checkout_url' => $this->checkoutUrl($clientSecret),
            'client_secret' => $clientSecret,
            'intention_id' => isset($data['id']) ? (string) $data['id'] : null,
            'raw' => $data,
        ];
    }

    public function canRefund(): bool
    {
        return (bool) config('paymob.enabled', true)
            && filled(config('paymob.secret_key'));
    }

    /**
     * Refund a captured Paymob transaction. A timeout is ambiguous: Paymob may already have accepted it.
     *
     * @return array<string, mixed>
     */
    public function refund(int $transactionId, int $amountCents): array
    {
        if (! $this->canRefund()) {
            throw new PaymobRefundException(__('filament/resources.payments.refund.not_configured'));
        }

        if ($transactionId < 1 || $amountCents < 1) {
            throw new PaymobRefundException(__('filament/resources.payments.refund.not_refundable'));
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('paymob.base_url'), '/'))
                ->acceptJson()
                ->asJson()
                ->withToken((string) config('paymob.secret_key'), 'Token')
                ->timeout(30)
                ->post('/api/acceptance/void_refund/refund', [
                    'transaction_id' => $transactionId,
                    'amount_cents' => $amountCents,
                ]);
        } catch (ConnectionException) {
            Log::warning('Paymob refund request did not complete', [
                'transaction_id' => $transactionId,
            ]);

            throw new PaymobRefundException(__('filament/resources.payments.refund.ambiguous'), true);
        }

        $decoded = $response->json();
        /** @var array<string, mixed> $data */
        $data = is_array($decoded) ? $decoded : [];
        $accepted = ! array_key_exists('success', $data) || filter_var($data['success'], FILTER_VALIDATE_BOOLEAN);

        if ($response->failed() || ! $accepted) {
            $message = $this->refundErrorMessage($data);

            Log::warning('Paymob refund refused', [
                'status' => $response->status(),
                'transaction_id' => $transactionId,
                'message' => $message,
            ]);

            throw new PaymobRefundException($message);
        }

        return $data;
    }

    public function checkoutUrl(string $clientSecret): string
    {
        $base = (string) config('paymob.base_url');
        $publicKey = (string) config('paymob.public_key');

        return $base.'/unifiedcheckout/?publicKey='.urlencode($publicKey).'&clientSecret='.urlencode($clientSecret);
    }

    /**
     * Paymob requires intention amount === sum(items[].amount) (with quantity=1 line totals).
     * Product lines alone miss shipping / tax / discount, which caused unmatched_item_prices.
     *
     * @return list<array{name: string, amount: int, description: string, quantity: int}>
     */
    private function intentionItems(Order $order, int $amountCents): array
    {
        $fallback = [
            $this->moneyLine('Order '.$order->number, $amountCents, 'Order '.$order->number),
        ];

        if ($amountCents < 1) {
            return $fallback;
        }

        /** @var list<array{name: string, amount: int, description: string, quantity: int}> $items */
        $items = [];

        foreach ($order->items as $item) {
            $lineCents = (int) round(((float) $item->line_total) * 100);

            if ($lineCents < 1) {
                continue;
            }

            $items[] = $this->moneyLine($item->displayName(), $lineCents, $item->displayName());
        }

        $shippingCents = (int) round(((float) $order->shipping) * 100);

        if ($shippingCents > 0) {
            $items[] = $this->moneyLine('Shipping', $shippingCents, 'Delivery');
        }

        $sum = $this->itemsAmountSum($items);
        $delta = $amountCents - $sum;

        if ($delta > 0) {
            $items[] = $this->moneyLine('Tax & fees', $delta, 'Tax and fees');
        } elseif ($delta < 0) {
            $items = $this->reduceItemsAmount($items, abs($delta));
        }

        if ($items === [] || $this->itemsAmountSum($items) !== $amountCents) {
            return $fallback;
        }

        return $items;
    }

    /**
     * @return array{name: string, amount: int, description: string, quantity: int}
     */
    private function moneyLine(string $name, int $amountCents, string $description): array
    {
        return [
            'name' => mb_substr($name, 0, 50),
            'amount' => $amountCents,
            'description' => mb_substr($description, 0, 120),
            'quantity' => 1,
        ];
    }

    /**
     * @param  list<array{name: string, amount: int, description: string, quantity: int}>  $items
     */
    private function itemsAmountSum(array $items): int
    {
        return array_sum(array_map(
            static fn (array $row): int => $row['amount'],
            $items,
        ));
    }

    /**
     * @param  list<array{name: string, amount: int, description: string, quantity: int}>  $items
     * @return list<array{name: string, amount: int, description: string, quantity: int}>
     */
    private function reduceItemsAmount(array $items, int $reduceBy): array
    {
        $reduced = [];

        for ($i = count($items) - 1; $i >= 0; $i--) {
            $amount = $items[$i]['amount'];

            if ($reduceBy > 0) {
                $cut = min($amount, $reduceBy);
                $amount -= $cut;
                $reduceBy -= $cut;
            }

            if ($amount > 0) {
                array_unshift($reduced, $this->moneyLine(
                    $items[$i]['name'],
                    $amount,
                    $items[$i]['description'],
                ));
            }
        }

        return $reduced;
    }

    /**
     * @param  array<string, mixed>  $obj
     */
    public function verifyTransactionHmac(array $obj, string $receivedHmac): bool
    {
        if ($receivedHmac === '' || ! filled(config('paymob.hmac_secret'))) {
            return false;
        }

        $fields = [
            $obj['amount_cents'] ?? '',
            $obj['created_at'] ?? '',
            $obj['currency'] ?? '',
            $obj['error_occured'] ?? '',
            $obj['has_parent_transaction'] ?? '',
            $obj['id'] ?? '',
            $obj['integration_id'] ?? '',
            $obj['is_3d_secure'] ?? '',
            $obj['is_auth'] ?? '',
            $obj['is_capture'] ?? '',
            $obj['is_refunded'] ?? '',
            $obj['is_standalone_payment'] ?? '',
            $obj['is_voided'] ?? '',
            data_get($obj, 'order.id', ''),
            $obj['owner'] ?? '',
            $obj['pending'] ?? '',
            data_get($obj, 'source_data.pan', ''),
            data_get($obj, 'source_data.sub_type', ''),
            data_get($obj, 'source_data.type', ''),
            $obj['success'] ?? '',
        ];

        $concat = implode('', array_map($this->stringifyForHmac(...), $fields));
        $computed = hash_hmac('sha512', $concat, (string) config('paymob.hmac_secret'));

        return hash_equals($computed, $receivedHmac);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function verifyReturnHmac(array $query, string $receivedHmac): bool
    {
        if ($receivedHmac === '' || ! filled(config('paymob.hmac_secret'))) {
            return false;
        }

        $fields = [
            $query['amount_cents'] ?? '',
            $query['created_at'] ?? '',
            $query['currency'] ?? '',
            $query['error_occured'] ?? '',
            $query['has_parent_transaction'] ?? '',
            $query['id'] ?? '',
            $query['integration_id'] ?? '',
            $query['is_3d_secure'] ?? '',
            $query['is_auth'] ?? '',
            $query['is_capture'] ?? '',
            $query['is_refunded'] ?? '',
            $query['is_standalone_payment'] ?? '',
            $query['is_voided'] ?? '',
            $query['order'] ?? ($query['order_id'] ?? ''),
            $query['owner'] ?? '',
            $query['pending'] ?? '',
            $query['source_data_pan'] ?? '',
            $query['source_data_sub_type'] ?? '',
            $query['source_data_type'] ?? '',
            $query['success'] ?? '',
        ];

        $concat = implode('', array_map($this->stringifyForHmac(...), $fields));
        $computed = hash_hmac('sha512', $concat, (string) config('paymob.hmac_secret'));

        return hash_equals($computed, $receivedHmac);
    }

    private function stringifyForHmac(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function refundErrorMessage(array $data): string
    {
        $message = data_get($data, 'message') ?? data_get($data, 'detail');

        if (is_array($message)) {
            $message = collect($message)->flatten()->first();
        }

        if (! is_string($message) || trim($message) === '') {
            return __('filament/resources.payments.refund.refused');
        }

        return Str::limit(trim(strip_tags($message)), 180);
    }

    /**
     * @return list<int>
     */
    private function paymentMethods(): array
    {
        $raw = config('paymob.integration_id');

        if (is_array($raw)) {
            $parts = $raw;
        } else {
            $parts = preg_split('/\s*,\s*/', trim((string) $raw), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $id): int => (int) $id,
            $parts,
        ), static fn (int $id): bool => $id > 0));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? 'Customer';
        $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Customer';

        return [$first, $last];
    }
}
