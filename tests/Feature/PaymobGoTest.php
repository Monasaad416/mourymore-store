<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

test('place order reaches paymob when the cart cookie is missing', function () {
    Http::fake([
        'https://accept.paymob.test/v1/intention/' => Http::response([
            'id' => 'intention-cookie-1',
            'client_secret' => 'secret-cookie-1',
        ], 201),
    ]);

    $product = Product::query()->create([
        'name' => 'Phone Cookie',
        'slug' => 'phone-paymob-cookie',
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
    $token = app(Cart::class)->token();
    session()->forget('store.checkout_cart_token');
    app()->forgetInstance(Cart::class);

    $this->post(route('checkout.place'), [
        'checkout_token' => (string) Str::uuid(),
        'cart_token' => $token,
        'name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment' => 'paymob',
    ])->assertRedirect(route('paymob.go', Order::query()->first()));

    expect(Order::query()->count())->toBe(1)
        ->and(app(Cart::class)->count())->toBe(1);
});

test('classic checkout post redirects to paymob go then away to hosted checkout', function () {
    Http::fake([
        'https://accept.paymob.test/v1/intention/' => Http::response([
            'id' => 'intention-go-1',
            'client_secret' => 'secret-go-1',
        ], 201),
    ]);

    $product = Product::query()->create([
        'name' => 'Phone Go',
        'slug' => 'phone-paymob-go',
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
        'checkout_token' => (string) Str::uuid(),
        'name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment' => 'paymob',
    ])->assertRedirect(route('paymob.go', Order::query()->first()));

    $order = Order::query()->first();
    $payment = Payment::query()->first();

    expect($order)->not->toBeNull()
        ->and($payment)->not->toBeNull()
        ->and($payment->status)->toBe('pending')
        ->and(Order::query()->count())->toBe(1);

    // Resume posts back to the same pending order.
    $this->post(route('checkout.place'), [
        'checkout_token' => session('store.checkout_token'),
        'name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment' => 'paymob',
    ])->assertRedirect(route('paymob.go', $order));

    expect(Order::query()->count())->toBe(1);

    $this->get(route('paymob.go', $order))
        ->assertRedirect('https://accept.paymob.test/unifiedcheckout/?publicKey=pk_test&clientSecret=secret-go-1');

    expect($payment->fresh()->checkout_url)->toContain('clientSecret=secret-go-1')
        ->and(data_get($payment->fresh()->metadata, 'intention_id'))->toBe('intention-go-1');
});

test('paymob go reopens a failed payment for another attempt', function () {
    Http::fake([
        'https://accept.paymob.test/v1/intention/' => Http::response([
            'id' => 'intention-retry',
            'client_secret' => 'secret-retry',
        ], 201),
    ]);

    $order = Order::query()->create([
        'number' => 'mourymore_store-RETRY01',
        'customer_name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment_method' => 'paymob',
        'payment_status' => 'failed',
        'delivery_status' => 'pending',
        'subtotal' => 200,
        'discount' => 0,
        'tax' => 0,
        'shipping' => 0,
        'total' => 200,
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
        'amount' => 200,
        'currency' => 'EGP',
        'status' => 'failed',
        'failure_code' => 'return_failed',
        'merchant_reference' => $order->number.'-1',
    ]);

    $this->get(route('paymob.go', $order))
        ->assertRedirect();

    expect($payment->fresh()->status)->toBe('pending')
        ->and($order->fresh()->payment_status)->toBe('pending')
        ->and($payment->fresh()->checkout_url)->toContain('clientSecret=secret-retry');
});

test('paymob cancel return marks payment failed and keeps the cart', function () {
    config(['paymob.hmac_secret' => 'test-hmac-secret']);

    $product = Product::query()->create([
        'name' => 'Phone Cancel',
        'slug' => 'phone-paymob-cancel',
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

    $query = [
        'amount_cents' => (string) (int) round(((float) $payment->amount) * 100),
        'created_at' => '2024-06-13T11:33:44.592345',
        'currency' => 'EGP',
        'error_occured' => 'false',
        'has_parent_transaction' => 'false',
        'id' => '1920364655',
        'integration_id' => '123456',
        'is_3d_secure' => 'false',
        'is_auth' => 'false',
        'is_capture' => 'false',
        'is_refunded' => 'false',
        'is_standalone_payment' => 'true',
        'is_voided' => 'false',
        'order' => '217503755',
        'owner' => '302852',
        'pending' => 'false',
        'source_data_pan' => '2346',
        'source_data_sub_type' => 'MasterCard',
        'source_data_type' => 'card',
        'success' => 'false',
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

    expect($payment->fresh()->status)->toBe('failed')
        ->and($order->fresh()->payment_status)->toBe('failed')
        ->and(CartItem::query()->count())->toBe(1);
});
