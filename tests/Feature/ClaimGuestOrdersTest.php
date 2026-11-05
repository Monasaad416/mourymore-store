<?php

use App\Models\Entitlement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ClaimGuestOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

/**
 * @return array{order: Order, entitlement: Entitlement}
 */
function guestOrderWithDigital(string $email): array
{
    $product = Product::query()->create([
        'name' => 'Guest Ebook',
        'slug' => 'guest-ebook',
        'brand' => 'mourymore_store',
        'description' => 'A digital book.',
        'highlights' => ['PDF'],
        'price' => 180,
        'stock' => 10,
        'image' => 'images/catalog/fallback.svg',
        'is_featured' => false,
        'is_deal' => false,
        'type' => 'digital',
    ]);

    $order = Order::query()->create([
        'number' => 'mourymore_store-GUEST001',
        'user_id' => null,
        'customer_name' => 'Guest Buyer',
        'phone' => '01012345678',
        'email' => $email,
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment_method' => 'paymob',
        'payment_status' => 'paid',
        'delivery_status' => 'not_required',
        'subtotal' => 180,
        'discount' => 0,
        'tax' => 0,
        'shipping' => 0,
        'total' => 180,
        'currency' => 'EGP',
        'status' => 'placed',
    ]);

    $item = $order->items()->create([
        'product_id' => $product->id,
        'product_type' => 'digital',
        'name' => ['en' => 'Guest Ebook', 'ar' => 'كتاب زائر'],
        'image' => 'images/catalog/fallback.svg',
        'qty' => 1,
        'price' => 180,
        'line_total' => 180,
    ]);

    $entitlement = Entitlement::query()->create([
        'user_id' => null,
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'product_id' => $product->id,
        'granted_at' => now(),
    ]);

    return compact('order', 'entitlement');
}

test('guest order page shows keep-number note and download', function () {
    ['order' => $order] = guestOrderWithDigital('guest@example.com');
    $order->load('items.entitlement');

    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee(__('store.order.keep_number_with_email'), false)
        ->assertSee(__('store.order.download'), false);
});

test('claiming attaches guest orders and entitlements by email', function () {
    ['order' => $order, 'entitlement' => $entitlement] = guestOrderWithDigital('Guest@Example.com');
    $user = User::factory()->create(['email' => 'guest@example.com']);

    expect(app(ClaimGuestOrders::class)->claimFor($user))->toBe(1);

    expect($order->fresh()->user_id)->toBe($user->id)
        ->and($entitlement->fresh()->user_id)->toBe($user->id);
});

test('registering with the guest email attaches past purchases', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    ['order' => $order, 'entitlement' => $entitlement] = guestOrderWithDigital('buyer@example.com');

    $this->post(route('register.store'), [
        'name' => 'Buyer',
        'email' => 'buyer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticated();

    expect($order->fresh()->user_id)->toBe(auth()->id())
        ->and($entitlement->fresh()->user_id)->toBe(auth()->id());

    $this->get(route('orders.index'))
        ->assertOk()
        ->assertSee($order->number);
});

test('logging in with the guest email attaches past purchases', function () {
    ['order' => $order] = guestOrderWithDigital('return@example.com');
    $user = User::factory()->create(['email' => 'return@example.com']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasNoErrors();

    expect($order->fresh()->user_id)->toBe($user->id);
});

test('orders already owned by another account are not claimed', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $other = User::factory()->create(['email' => 'other@example.com']);

    $order = Order::query()->create([
        'number' => 'mourymore_store-OWNED001',
        'user_id' => $owner->id,
        'customer_name' => 'Owner',
        'phone' => '01012345678',
        'email' => 'other@example.com',
        'governorate' => 'Cairo',
        'city' => 'Nasr City',
        'address' => '12 Abbas El Akkad',
        'payment_method' => 'paymob',
        'payment_status' => 'paid',
        'delivery_status' => 'pending',
        'subtotal' => 100,
        'discount' => 0,
        'tax' => 0,
        'shipping' => 0,
        'total' => 100,
        'currency' => 'EGP',
        'status' => 'placed',
    ]);

    expect(app(ClaimGuestOrders::class)->claimFor($other))->toBe(0)
        ->and($order->fresh()->user_id)->toBe($owner->id);
});
