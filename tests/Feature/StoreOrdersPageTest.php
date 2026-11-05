<?php

use App\Livewire\Store\OrdersPage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests cannot view the orders index', function () {
    $this->get(route('orders.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users see only their own orders', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Order::query()->create([
        'number' => 'mourymore_store-MINE0001',
        'user_id' => $user->id,
        'customer_name' => $user->name,
        'phone' => '01012345678',
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

    Order::query()->create([
        'number' => 'mourymore_store-OTHER001',
        'user_id' => $other->id,
        'customer_name' => $other->name,
        'phone' => '01087654321',
        'governorate' => 'Cairo',
        'city' => 'Maadi',
        'address' => '9 Road 9',
        'payment_method' => 'paymob',
        'payment_status' => 'pending',
        'delivery_status' => 'pending',
        'subtotal' => 50,
        'discount' => 0,
        'tax' => 0,
        'shipping' => 0,
        'total' => 50,
        'currency' => 'EGP',
        'status' => 'placed',
    ]);

    Livewire::actingAs($user)
        ->test(OrdersPage::class)
        ->assertSuccessful()
        ->assertSee($mine->number)
        ->assertDontSee('mourymore_store-OTHER001');
});
