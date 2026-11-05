<?php

use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', Profile::class)->name('profile.edit');

    // Old starter-kit URLs — keep names for Fortify/passkeys, always send to account profile.
    Route::redirect('settings/security', '/settings/profile')->name('security.edit');
    Route::redirect('settings/appearance', '/settings/profile')->name('appearance.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('profile.edit'),
        'manage' => route('profile.edit'),
    ]);
})->name('well-known.passkeys');
