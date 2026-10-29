<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;

class ClearExpiredCarts extends Command
{
    protected $signature = 'store:clear-expired-carts';

    protected $description = 'Delete carts that were not checked out before they expired';

    public function handle(): int
    {
        $deleted = Cart::query()->where('expires_at', '<=', now())->delete();

        $this->info("Deleted {$deleted} expired carts.");

        return self::SUCCESS;
    }
}
