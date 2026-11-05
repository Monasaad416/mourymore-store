<?php

use App\Models\StockReservation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('store:clear-expired-carts')->daily();
Schedule::call(static fn () => StockReservation::releaseExpired())
    ->everyFiveMinutes()
    ->name('store-release-expired-stock-reservations');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
