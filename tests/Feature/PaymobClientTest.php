<?php

use App\Models\Order;
use App\Models\Payment;
use App\Services\Paymob\PaymobClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'paymob.enabled' => true,
        'paymob.base_url' => 'https://accept.paymob.test',
        'paymob.secret_key' => 'sk_test',
        'paymob.public_key' => 'pk_test',
        'paymob.hmac_secret' => 'hmac_test',
        'paymob.integration_id' => '123456',
    ]);
});

test('paymob intention amount matches the sum of item amounts including shipping', function () {
    Http::fake([
        'https://accept.paymob.test/v1/intention/' => Http::response([
            'id' => 'intention-1',
            'client_secret' => 'secret-1',
        ], 201),
    ]);

    $order = Order::query()->create([
        'number' => 'mourymore_store-TESTORD1',
        'customer_name' => 'Nour Hassan',
        'phone' => '01012345678',
        'email' => 'nour@example.com',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment_method' => 'paymob',
        'payment_status' => 'pending',
        'delivery_status' => 'pending',
        'subtotal' => 200,
        'discount' => 0,
        'tax' => 0,
        'shipping' => 45,
        'total' => 245,
        'currency' => 'EGP',
        'status' => 'placed',
    ]);

    $order->items()->create([
        'product_id' => null,
        'product_type' => 'physical',
        'name' => ['en' => 'Phone', 'ar' => 'Phone'],
        'image' => 'images/catalog/fallback.svg',
        'qty' => 2,
        'price' => 100,
        'line_total' => 200,
    ]);

    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'gateway' => 'paymob',
        'amount' => 245,
        'currency' => 'EGP',
        'status' => 'pending',
        'merchant_reference' => $order->number.'-1',
    ]);

    $checkout = app(PaymobClient::class)->createCheckout($order->load('items'), $payment);

    expect($checkout['client_secret'])->toBe('secret-1')
        ->and($checkout['checkout_url'])->toContain('clientSecret=secret-1');

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        $amount = (int) $payload['amount'];
        $itemsSum = collect($payload['items'])->sum(
            fn (array $item): int => (int) $item['amount'] * (int) ($item['quantity'] ?? 1),
        );

        return $request->url() === 'https://accept.paymob.test/v1/intention/'
            && $amount === 24500
            && $itemsSum === $amount
            && collect($payload['items'])->contains(
                fn (array $item): bool => $item['name'] === 'Shipping' && $item['amount'] === 4500,
            );
    });
});

test('paymob intention keeps amount matched when a discount is applied', function () {
    Http::fake([
        'https://accept.paymob.test/v1/intention/' => Http::response([
            'id' => 'intention-2',
            'client_secret' => 'secret-2',
        ], 201),
    ]);

    $order = Order::query()->create([
        'number' => 'mourymore_store-TESTORD2',
        'customer_name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment_method' => 'paymob',
        'payment_status' => 'pending',
        'delivery_status' => 'pending',
        'subtotal' => 200,
        'discount' => 20,
        'tax' => 0,
        'shipping' => 45,
        'total' => 225,
        'currency' => 'EGP',
        'status' => 'placed',
    ]);

    $order->items()->create([
        'product_id' => null,
        'product_type' => 'physical',
        'name' => ['en' => 'Phone', 'ar' => 'Phone'],
        'image' => 'images/catalog/fallback.svg',
        'qty' => 1,
        'price' => 200,
        'line_total' => 200,
    ]);

    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'gateway' => 'paymob',
        'amount' => 225,
        'currency' => 'EGP',
        'status' => 'pending',
        'merchant_reference' => $order->number.'-1',
    ]);

    app(PaymobClient::class)->createCheckout($order->load('items'), $payment);

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        $amount = (int) $payload['amount'];
        $itemsSum = collect($payload['items'])->sum(
            fn (array $item): int => (int) $item['amount'] * (int) ($item['quantity'] ?? 1),
        );

        return $amount === 22500 && $itemsSum === $amount;
    });
});
