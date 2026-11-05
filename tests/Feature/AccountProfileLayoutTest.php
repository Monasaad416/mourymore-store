<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('profile uses store layout not laravel starter chrome', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee(__('store.account.title'), false)
        ->assertDontSee('Repository', false)
        ->assertDontSee('Documentation', false)
        ->assertDontSee(__('Security'), false)
        ->assertDontSee('github.com/laravel/livewire-starter-kit', false);
});
