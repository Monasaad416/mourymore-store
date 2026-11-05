<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use App\Support\CustomerFlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('login redirects home with a customer flash notification', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('home', absolute: false))
        ->assertSessionHas('flash_success')
        ->assertCookie(CustomerFlash::COOKIE, 'login');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('window.storeToast', false)
        ->assertSee(__('store.auth.flash.login'), false);
});

test('logout redirects home with a customer flash notification', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'))
        ->assertCookie(CustomerFlash::COOKIE, 'logout');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(__('store.auth.flash.logout'), false);
});

test('profile updates notify the customer', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Nour Hassan')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors()
        ->assertRedirect(route('profile.edit', absolute: false));

    expect(session('flash_success'))->toBe(__('store.account.updated'));
});

test('password updates notify the customer', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password-123')
        ->set('password_confirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('profile.edit', absolute: false));

    expect(session('flash_success'))->toBe(__('store.account.password_updated'));

    $this->assertTrue(Auth::attempt([
        'email' => $user->email,
        'password' => 'new-password-123',
    ]));
});
