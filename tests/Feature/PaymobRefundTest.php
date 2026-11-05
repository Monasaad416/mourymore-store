<?php

use App\Models\Entitlement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockReservation;
use App\Services\Paymob\PaymobRefundException;
use App\Services\Paymob\RefundPaymobPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'paymob.enabled' => true,
        'paymob.base_url' => 'https://accept.paymob.test',
        'paymob.secret_key' => 'sk_test',
        'paymob.public_key' => 'pk_test',
        'paymob.hmac_secret' => 'test-hmac-secret',
        'paymob.integration_id' => '123456',
    ]);
});

/**
 * @param  array<string, mixed>  $orderOverrides
 * @return array{product: Product, order: Order, payment: Payment}
 */
function refundablePaymobPayment(array $orderOverrides = []): array
{
    $product = Product::query()->create([
        'name' => 'Phone',
        'slug' => 'phone-refund',
        'brand' => 'mourymore_store',
        'description' => 'A phone.',
        'highlights' => ['5G'],
        'price' => 200,
        'stock' => 0,
        'image' => 'images/catalog/fallback.svg',
        'is_featured' => false,
        'is_deal' => false,
    ]);

    $order = Order::query()->create(array_merge([
        'number' => 'mourymore_store-REFUND01',
        'customer_name' => 'Nour Hassan',
        'phone' => '01012345678',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment_method' => 'paymob',
        'payment_status' => 'paid',
        'delivery_status' => 'pending',
        'subtotal' => 200,
        'discount' => 0,
        'tax' => 0,
        'shipping' => 45,
        'total' => 245,
        'currency' => 'EGP',
        'status' => 'placed',
    ], $orderOverrides));

    $item = $order->items()->create([
        'product_id' => $product->id,
        'product_type' => 'digital',
        'name' => ['en' => 'Phone', 'ar' => 'هاتف'],
        'image' => 'images/catalog/fallback.svg',
        'qty' => 1,
        'price' => 200,
        'line_total' => 200,
    ]);

    StockReservation::query()->create([
        'product_id' => $product->id,
        'order_id' => $order->id,
        'qty' => 1,
        'status' => 'committed',
    ]);

    Entitlement::query()->create([
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'product_id' => $product->id,
        'granted_at' => now(),
    ]);

    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'gateway' => 'paymob',
        'transaction_id' => '566998993',
        'merchant_reference' => $order->number.'-1',
        'amount' => 245,
        'currency' => 'EGP',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    return [
        'product' => $product,
        'order' => $order,
        'payment' => $payment,
    ];
}

test('refund sends the full amount to paymob and restores the order', function () {
    Http::fake([
        'https://accept.paymob.test/api/acceptance/void_refund/refund' => Http::response([
            'id' => 778899,
            'success' => true,
            'amount_cents' => 24500,
        ], 201),
    ]);

    ['product' => $product, 'order' => $order, 'payment' => $payment] = refundablePaymobPayment();

    app(RefundPaymobPayment::class)->refund($payment);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://accept.paymob.test/api/acceptance/void_refund/refund'
            && $request->hasHeader('Authorization', 'Token sk_test')
            && $request->data()['transaction_id'] === 566998993
            && $request->data()['amount_cents'] === 24500;
    });

    expect($payment->fresh()->status)->toBe('refunded')
        ->and($payment->fresh()->transaction_id)->toBe('566998993')
        ->and(data_get($payment->fresh()->metadata, 'refund_transaction_id'))->toBe('778899')
        ->and($order->fresh()->payment_status)->toBe('refunded')
        ->and($order->fresh()->status)->toBe('cancelled')
        ->and($product->fresh()->stock)->toBe(1)
        ->and(StockReservation::query()->where('status', 'committed')->count())->toBe(0)
        ->and(Entitlement::query()->count())->toBe(0);
});

test('a second refund does not call paymob again or restore stock twice', function () {
    Http::fake([
        'https://accept.paymob.test/api/acceptance/void_refund/refund' => Http::response([
            'id' => 778899,
            'success' => true,
        ], 201),
    ]);

    ['product' => $product, 'payment' => $payment] = refundablePaymobPayment();

    app(RefundPaymobPayment::class)->refund($payment);
    app(RefundPaymobPayment::class)->refund($payment->fresh());

    Http::assertSentCount(1);
    expect($product->fresh()->stock)->toBe(1);
});

test('a refused refund leaves the payment paid', function () {
    Http::fake([
        'https://accept.paymob.test/api/acceptance/void_refund/refund' => Http::response([
            'message' => 'Transaction not refundable',
        ], 400),
    ]);

    ['product' => $product, 'order' => $order, 'payment' => $payment] = refundablePaymobPayment();

    expect(fn () => app(RefundPaymobPayment::class)->refund($payment))
        ->toThrow(PaymobRefundException::class, 'Transaction not refundable');

    expect($payment->fresh()->status)->toBe('paid')
        ->and($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->status)->toBe('placed')
        ->and($product->fresh()->stock)->toBe(0)
        ->and(Entitlement::query()->count())->toBe(1);
});

test('a paymob timeout does not mark the payment refunded', function () {
    Http::fake(function (): void {
        throw new ConnectionException('timed out');
    });

    ['payment' => $payment] = refundablePaymobPayment();

    expect(fn () => app(RefundPaymobPayment::class)->refund($payment))
        ->toThrow(PaymobRefundException::class);

    expect($payment->fresh()->status)->toBe('paid');
});

test('refunding a shipped order keeps the delivery state', function () {
    Http::fake([
        'https://accept.paymob.test/api/acceptance/void_refund/refund' => Http::response([
            'id' => 778900,
            'success' => true,
        ], 201),
    ]);

    ['order' => $order, 'payment' => $payment] = refundablePaymobPayment([
        'number' => 'mourymore_store-REFUND02',
        'delivery_status' => 'shipped',
        'status' => 'placed',
    ]);

    app(RefundPaymobPayment::class)->refund($payment);

    expect($order->fresh()->payment_status)->toBe('refunded')
        ->and($order->fresh()->status)->toBe('placed')
        ->and($order->fresh()->delivery_status)->toBe('shipped');
});

test('paymob refund webhook restores the order and a later success callback does not mark it paid', function () {
    ['product' => $product, 'order' => $order, 'payment' => $payment] = refundablePaymobPayment();

    $obj = [
        'amount_cents' => 24500,
        'created_at' => '2024-06-13T11:33:44.592345',
        'currency' => 'EGP',
        'error_occured' => false,
        'has_parent_transaction' => true,
        'id' => 778899,
        'integration_id' => 123456,
        'is_3d_secure' => true,
        'is_auth' => false,
        'is_capture' => false,
        'is_refunded' => true,
        'is_standalone_payment' => false,
        'is_voided' => false,
        'order' => [
            'id' => 217503754,
            'merchant_order_id' => $payment->merchant_reference,
        ],
        'owner' => 302852,
        'pending' => false,
        'source_data' => [
            'pan' => '2346',
            'sub_type' => 'MasterCard',
            'type' => 'card',
        ],
        'success' => true,
    ];

    $this->postJson(route('paymob.webhook', ['hmac' => refundWebhookHmac($obj)]), ['obj' => $obj])
        ->assertOk();

    expect($payment->fresh()->status)->toBe('refunded')
        ->and($payment->fresh()->transaction_id)->toBe('566998993')
        ->and($order->fresh()->payment_status)->toBe('refunded')
        ->and($product->fresh()->stock)->toBe(1)
        ->and(Entitlement::query()->count())->toBe(0);

    $paidAgain = $obj;
    $paidAgain['is_refunded'] = false;
    $paidAgain['has_parent_transaction'] = false;
    $paidAgain['id'] = 566998993;

    $this->postJson(route('paymob.webhook', ['hmac' => refundWebhookHmac($paidAgain)]), ['obj' => $paidAgain])
        ->assertOk();

    expect($payment->fresh()->status)->toBe('refunded')
        ->and($order->fresh()->payment_status)->toBe('refunded')
        ->and($product->fresh()->stock)->toBe(1);
});

/**
 * @param  array<string, mixed>  $obj
 */
function refundWebhookHmac(array $obj): string
{
    $fields = [
        $obj['amount_cents'], $obj['created_at'], $obj['currency'], $obj['error_occured'],
        $obj['has_parent_transaction'], $obj['id'], $obj['integration_id'], $obj['is_3d_secure'],
        $obj['is_auth'], $obj['is_capture'], $obj['is_refunded'], $obj['is_standalone_payment'],
        $obj['is_voided'], $obj['order']['id'], $obj['owner'], $obj['pending'],
        $obj['source_data']['pan'], $obj['source_data']['sub_type'],
        $obj['source_data']['type'], $obj['success'],
    ];

    $concat = implode('', array_map(function (mixed $value): string {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }, $fields));

    return hash_hmac('sha512', $concat, 'test-hmac-secret');
}
