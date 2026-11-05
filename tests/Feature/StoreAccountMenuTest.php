<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests see sign in in the account menu without logout', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(__('store.nav.sign_in'), false)
        ->assertDontSee(__('store.account.logout'), false)
        ->assertDontSee('store-logout-button', false);
});

test('signed-in shoppers can log out from the account menu', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee(__('store.account.logout'), false)
        ->assertSee('data-test="store-logout-button"', false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertDontSee(__('store.nav.sign_in'), false);
});
