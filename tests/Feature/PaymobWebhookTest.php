<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockReservation;
use App\Services\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePaymobObj(array $overrides = []): array
{
    return array_merge([
        'amount_cents' => 24500,
        'created_at' => '2024-06-13T11:33:44.592345',
        'currency' => 'EGP',
        'error_occured' => false,
        'has_parent_transaction' => false,
        'id' => 1920364654,
        'integration_id' => 123456,
        'is_3d_secure' => true,
        'is_auth' => false,
        'is_capture' => false,
        'is_refunded' => false,
        'is_standalone_payment' => true,
        'is_voided' => false,
        'order' => [
            'id' => 217503754,
            'merchant_order_id' => 'ref-1',
        ],
        'owner' => 302852,
        'pending' => false,
        'source_data' => [
            'pan' => '2346',
            'sub_type' => 'MasterCard',
            'type' => 'card',
        ],
        'success' => true,
    ], $overrides);
}

function hmacFor(array $obj): string
{
    config([
        'paymob.hmac_secret' => 'test-hmac-secret',
        'paymob.secret_key' => 'sk_test',
        'paymob.public_key' => 'pk_test',
        'paymob.integration_id' => '123456',
        'paymob.enabled' => true,
    ]);

    $fields = [
        $obj['amount_cents'], $obj['created_at'], $obj['currency'], $obj['error_occured'],
        $obj['has_parent_transaction'], $obj['id'], $obj['integration_id'], $obj['is_3d_secure'],
        $obj['is_auth'], $obj['is_capture'], $obj['is_refunded'], $obj['is_standalone_payment'],
        $obj['is_voided'], $obj['order']['id'], $obj['owner'], $obj['pending'],
        $obj['source_data']['pan'], $obj['source_data']['sub_type'],
        $obj['source_data']['type'], $obj['success'],
    ];

    $concat = implode('', array_map(function ($value): string {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }, $fields));

    return hash_hmac('sha512', $concat, 'test-hmac-secret');
}

test('paymob webhook marks payment paid when hmac is valid', function () {
    $product = Product::query()->create([
        'name' => 'Phone',
        'slug' => 'phone-paymob',
        'brand' => 'mourymore_store',
        'description' => 'A phone.',
        'highlights' => ['5G'],
        'price' => 200,
        'stock' => 1,
        'image' => 'images/catalog/fallback.svg',
        'is_featured' => false,
        'is_deal' => false,
    ]);

    app(Cart::class)->add($product->id);

    $this->post(route('checkout.place'), [
        'name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment' => 'paymob',
    ])->assertRedirect();

    $payment = Payment::query()->first();
    expect($payment)->not->toBeNull()
        ->and($payment->status)->toBe('pending');

    $obj = makePaymobObj([
        'amount_cents' => (int) round(((float) $payment->amount) * 100),
        'order' => [
            'id' => 217503754,
            'merchant_order_id' => $payment->merchant_reference,
        ],
    ]);

    $this->postJson(route('paymob.webhook', ['hmac' => hmacFor($obj)]), ['obj' => $obj])
        ->assertOk()
        ->assertJson(['received' => true]);

    expect($payment->fresh()->status)->toBe('paid')
        ->and($product->fresh()->stock)->toBe(0)
        ->and(StockReservation::query()->where('status', 'committed')->count())->toBe(1)
        ->and(Order::query()->value('payment_status'))->toBe('paid')
        ->and(CartItem::query()->count())->toBe(0);
});

test('paymob webhook rejects invalid hmac', function () {
    $this->postJson(route('paymob.webhook', ['hmac' => 'bad']), [
        'obj' => makePaymobObj(),
    ])->assertUnauthorized();
});

test('paymob return with valid hmac marks payment paid and clears the cart', function () {
    config([
        'paymob.hmac_secret' => 'test-hmac-secret',
        'paymob.secret_key' => 'sk_test',
        'paymob.public_key' => 'pk_test',
        'paymob.integration_id' => '123456',
        'paymob.enabled' => true,
    ]);

    $product = Product::query()->create([
        'name' => 'Phone Return',
        'slug' => 'phone-paymob-return',
        'brand' => 'mourymore_store',
        'description' => 'A phone.',
        'highlights' => ['5G'],
        'price' => 200,
        'stock' => 1,
        'image' => 'images/catalog/fallback.svg',
        'is_featured' => false,
        'is_deal' => false,
    ]);

    app(Cart::class)->add($product->id);

    $this->post(route('checkout.place'), [
        'name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment' => 'paymob',
    ])->assertRedirect();

    $order = Order::query()->first();
    $payment = Payment::query()->first();

    expect($order)->not->toBeNull()
        ->and($payment)->not->toBeNull()
        ->and(CartItem::query()->count())->toBe(1);

    $query = [
        'amount_cents' => (string) (int) round(((float) $payment->amount) * 100),
        'created_at' => '2024-06-13T11:33:44.592345',
        'currency' => 'EGP',
        'error_occured' => 'false',
        'has_parent_transaction' => 'false',
        'id' => '1920364654',
        'integration_id' => '123456',
        'is_3d_secure' => 'true',
        'is_auth' => 'false',
        'is_capture' => 'false',
        'is_refunded' => 'false',
        'is_standalone_payment' => 'true',
        'is_voided' => 'false',
        'order' => '217503754',
        'owner' => '302852',
        'pending' => 'false',
        'source_data_pan' => '2346',
        'source_data_sub_type' => 'MasterCard',
        'source_data_type' => 'card',
        'success' => 'true',
    ];

    $fields = [
        $query['amount_cents'], $query['created_at'], $query['currency'], $query['error_occured'],
        $query['has_parent_transaction'], $query['id'], $query['integration_id'], $query['is_3d_secure'],
        $query['is_auth'], $query['is_capture'], $query['is_refunded'], $query['is_standalone_payment'],
        $query['is_voided'], $query['order'], $query['owner'], $query['pending'],
        $query['source_data_pan'], $query['source_data_sub_type'], $query['source_data_type'], $query['success'],
    ];
    $query['hmac'] = hash_hmac('sha512', implode('', $fields), 'test-hmac-secret');

    $this->get(route('paymob.return', ['order' => $order->number]).'?'.http_build_query($query))
        ->assertRedirect(route('orders.show', $order));

    expect($payment->fresh()->status)->toBe('paid')
        ->and($order->fresh()->payment_status)->toBe('paid')
        ->and(CartItem::query()->count())->toBe(0);
});
