<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('site cover and logo appear on home and auth pages', function () {
    $settings = SiteSetting::current();

    if (! $settings->exists) {
        $settings = SiteSetting::query()->create(SiteSetting::defaultAttributes());
    }

    $settings->update([
        'logo' => 'branding/shared-logo.png',
        'cover' => 'branding/shared-cover.jpg',
        'name' => [
            'en' => 'Shared Brand Store',
            'ar' => 'متجر الهوية المشتركة',
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/storage/branding/shared-cover.jpg', false)
        ->assertSee('/storage/branding/shared-logo.png', false);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('/storage/branding/shared-cover.jpg', false)
        ->assertSee('/storage/branding/shared-logo.png', false);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('/storage/branding/shared-cover.jpg', false)
        ->assertSee('/storage/branding/shared-logo.png', false);
});
