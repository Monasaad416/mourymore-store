<?php

use App\Enums\ProductType;
use App\Livewire\Actions\Logout;
use App\Livewire\Store\CheckoutPage;
use App\Livewire\Store\Home;
use App\Models\Cart as CartModel;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Entitlement;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeCatalogProduct(): Product
{
    $category = Category::query()->create([
        'name' => 'Phones',
        'slug' => 'phones',
        'icon' => 'phone',
        'blurb' => 'Phones',
        'sort' => 1,
    ]);

    return Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Test Phone',
        'slug' => 'test-phone',
        'brand' => 'mourymore_store',
        'description' => 'A phone for the test.',
        'highlights' => ['5G', 'Dual SIM'],
        'price' => 1000,
        'compare_price' => 1200,
        'rating' => 4.5,
        'reviews_count' => 10,
        'stock' => 5,
        'badge' => 'express',
        'image' => 'images/catalog/phone.jpg',
        'is_featured' => true,
        'is_deal' => true,
    ]);
}

test('shopper can open a product, save it, and check out with a card payment', function () {
    $product = makeCatalogProduct();

    Offer::query()->create([
        'code' => 'MOURYMORE_STORE10',
        'name' => ['en' => 'Welcome', 'ar' => 'ترحيب'],
        'type' => 'percent',
        'value' => 10,
        'is_active' => true,
        'sort' => 1,
    ]);

    $this->get(route('product', $product))
        ->assertOk()
        ->assertSee('Test Phone');

    Livewire::test(Home::class)
        ->call('toggleWishlist', $product->id)
        ->call('addToCart', $product->id);

    expect(app(Cart::class)->count())->toBe(1);

    Livewire::test(CheckoutPage::class)
        ->set('name', 'Nour Hassan')
        ->set('phone', '01012345678')
        ->set('governorate', 'Cairo')
        ->set('city', 'Nasr City')
        ->set('address', '12 Abbas El Akkad')
        ->set('payment', 'card')
        ->set('promo', 'mourymore_store10')
        ->call('placeOrder')
        ->assertHasNoErrors();

    $order = Order::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toBe('card')
        ->and((float) $order->discount)->toBe(100.0)
        ->and((float) $order->tax)->toBe(0.0)
        ->and($order->currency)->toBe('EGP')
        ->and((float) $order->shipping)->toBe(45.0)
        ->and((float) $order->total)->toBe(945.0)
        ->and($product->fresh()->stock)->toBe(5)
        ->and(StockReservation::query()->where('status', 'held')->count())->toBe(1)
        ->and($order->items()->first()?->displayName())->toBe('Test Phone')
        ->and($order->items()->first()?->name)->toBeArray()
        ->and(CartItem::query()->count())->toBe(0);
});

test('placing the same checkout again returns the original order', function () {
    $product = makeCatalogProduct();
    app(Cart::class)->add($product->id);

    $checkout = Livewire::test(CheckoutPage::class)
        ->set('name', 'Nour Hassan')
        ->set('phone', '01012345678')
        ->set('governorate', 'Cairo')
        ->set('city', 'Nasr City')
        ->set('address', '12 Abbas El Akkad')
        ->set('payment', 'card');

    $checkout->call('placeOrder')->assertHasNoErrors();

    $order = Order::query()->first();

    $checkout->call('placeOrder')
        ->assertHasNoErrors()
        ->assertRedirect(route('orders.show', $order));

    expect(Order::query()->count())->toBe(1)
        ->and(Payment::query()->count())->toBe(1)
        ->and($product->fresh()->stock)->toBe(5)
        ->and(StockReservation::query()->where('status', 'held')->count())->toBe(1);
});

test('a course can be bought without touching stock', function () {
    $category = Category::query()->create([
        'name' => 'Courses',
        'slug' => 'courses',
        'icon' => 'course',
        'sort' => 1,
    ]);

    $course = Product::query()->create([
        'category_id' => $category->id,
        'type' => ProductType::Digital,
        'name' => 'Test Course',
        'slug' => 'test-course',
        'brand' => 'mourymore_store',
        'description' => 'A course for the test.',
        'highlights' => ['Two lessons'],
        'price' => 200,
        'stock' => 0,
        'image' => 'images/catalog/kitchen.jpg',
    ]);

    $course->lessons()->create([
        'title' => 'First lesson',
        'sort' => 1,
        'duration_minutes' => 10,
    ]);

    app(Cart::class)->add($course->id);

    Livewire::test(CheckoutPage::class)
        ->set('name', 'Nour Hassan')
        ->set('phone', '01012345678')
        ->set('governorate', 'Cairo')
        ->set('city', 'Nasr City')
        ->set('address', '12 Abbas El Akkad')
        ->set('payment', 'card')
        ->call('placeOrder')
        ->assertHasNoErrors();

    expect($course->fresh()->stock)->toBe(0)
        ->and(Order::query()->count())->toBe(1);
});

test('paymob holds stock until the payment is marked paid', function () {
    $product = makeCatalogProduct();
    $product->update(['stock' => 1, 'price' => 200, 'compare_price' => null, 'is_deal' => false]);

    app(Cart::class)->add($product->id);

    Livewire::test(CheckoutPage::class)
        ->set('name', 'Nour Hassan')
        ->set('phone', '01012345678')
        ->set('governorate', 'Cairo')
        ->set('city', 'Nasr City')
        ->set('address', '12 Abbas El Akkad')
        ->set('payment', 'card')
        ->call('placeOrder')
        ->assertHasNoErrors();

    expect($product->fresh()->stock)->toBe(1)
        ->and(StockReservation::query()->where('status', 'held')->count())->toBe(1)
        ->and($product->fresh()->canPurchase(1))->toBeFalse()
        ->and(Payment::query()->value('gateway'))->toBe('paymob')
        ->and(Payment::query()->value('currency'))->toBe('EGP')
        ->and((float) Payment::query()->value('amount'))->toBe(245.0)
        ->and(Payment::query()->value('merchant_reference'))->not->toBeNull()
        ->and(Payment::query()->value('status'))->toBe('pending');

    $payment = Payment::query()->first();
    $payment->markPaid('paymob-1');

    expect($product->fresh()->stock)->toBe(0)
        ->and($payment->fresh()->status)->toBe('paid')
        ->and(StockReservation::query()->where('status', 'committed')->count())->toBe(1)
        ->and($payment->fresh()->transaction_id)->toBe('paymob-1');
});

test('a paid digital order grants access', function () {
    $category = Category::query()->create([
        'name' => 'Books',
        'slug' => 'books',
        'icon' => 'book',
        'sort' => 1,
    ]);

    $book = Product::query()->create([
        'category_id' => $category->id,
        'type' => ProductType::Digital,
        'name' => 'Digital Book',
        'slug' => 'digital-book',
        'brand' => 'mourymore_store',
        'description' => 'A file.',
        'highlights' => ['PDF'],
        'price' => 100,
        'stock' => 0,
        'image' => 'images/catalog/fallback.svg',
    ]);

    app(Cart::class)->add($book->id);

    Livewire::test(CheckoutPage::class)
        ->set('name', 'Nour Hassan')
        ->set('phone', '01012345678')
        ->set('governorate', 'Cairo')
        ->set('city', 'Nasr City')
        ->set('address', '12 Abbas El Akkad')
        ->set('payment', 'card')
        ->call('placeOrder')
        ->assertHasNoErrors();

    expect(Entitlement::query()->count())->toBe(0);

    Payment::query()->first()->markPaid('paymob-2');

    expect(Entitlement::query()->count())->toBe(1);
});

test('a guest cart is stored and stays with the customer after login', function () {
    $product = makeCatalogProduct();
    app(Cart::class)->add($product->id);

    $stored = CartModel::query()->first();

    expect($stored)->not->toBeNull()
        ->and($stored->user_id)->toBeNull()
        ->and($stored->items()->where('product_id', $product->id)->value('qty'))->toBe(1);

    $user = User::factory()->create();
    auth()->login($user);

    expect($stored->fresh()->user_id)->toBe($user->id)
        ->and(app(Cart::class)->count())->toBe(1);

    app(Logout::class)();
    app()->forgetInstance(Cart::class);

    expect(app(Cart::class)->count())->toBe(0)
        ->and(CartItem::query()->count())->toBe(1);

    auth()->login($user);

    expect(app(Cart::class)->count())->toBe(1)
        ->and(CartModel::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('logging in merges a guest cart into the account cart', function () {
    $product = makeCatalogProduct();
    $user = User::factory()->create();
    $account = CartModel::query()->create([
        'user_id' => $user->id,
        'token' => 'account-cart-token',
        'expires_at' => now()->addDays(30),
    ]);
    $account->items()->create([
        'product_id' => $product->id,
        'qty' => 1,
    ]);

    app(Cart::class)->add($product->id);

    auth()->login($user);

    expect(CartModel::query()->count())->toBe(1)
        ->and($account->fresh()->items()->where('product_id', $product->id)->value('qty'))->toBe(2)
        ->and(app(Cart::class)->count())->toBe(2);
});

test('an expired cart is removed when it is read and by the cleanup command', function () {
    $product = makeCatalogProduct();
    app(Cart::class)->add($product->id);

    $stored = CartModel::query()->first();
    $stored->update(['expires_at' => now()->subMinute()]);
    app()->forgetInstance(Cart::class);

    $this->withCookie(Cart::COOKIE, $stored->token)->get(route('cart'));

    expect(CartModel::query()->count())->toBe(0);

    CartModel::query()->create([
        'token' => 'expired-cart-token',
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('store:clear-expired-carts')->assertSuccessful();

    expect(CartModel::query()->count())->toBe(0);
});

test('the cart shows how close the order is to free delivery', function () {
    $product = makeCatalogProduct();
    app(Cart::class)->add($product->id);

    $this->get(route('cart'))
        ->assertOk()
        ->assertSee('Add EGP 500 more for free delivery.')
        ->assertSee('role="progressbar"', false);

    app(Cart::class)->set($product->id, 2);

    $this->get(route('cart'))
        ->assertOk()
        ->assertSee('Free delivery is unlocked for this order.');
});

test('the storefront can switch between arabic and english', function () {
    $this->get(route('locale', 'ar'))
        ->assertRedirect();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('اطلب قبل الساعة الثانية ظهرًا، ويخرج توصيل القاهرة والجيزة في اليوم نفسه.');

    $this->get(route('locale', 'en'))
        ->assertRedirect();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('dir="ltr"', false)
        ->assertSee('Order before 2 PM and Greater Cairo delivery can still leave today.');
});
