<?php

namespace App\Services;

use App\Models\Entitlement;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClaimGuestOrders
{
    /**
     * Attach guest checkout orders (and their digital entitlements) that used
     * the same email, without creating accounts from checkout alone.
     */
    public function claimFor(User $user): int
    {
        $email = strtolower(trim((string) $user->email));

        if ($email === '') {
            return 0;
        }

        return (int) DB::transaction(function () use ($user, $email): int {
            $orderIds = Order::query()
                ->whereNull('user_id')
                ->whereNotNull('email')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->pluck('id');

            if ($orderIds->isEmpty()) {
                return 0;
            }

            Order::query()
                ->whereIn('id', $orderIds)
                ->update(['user_id' => $user->id]);

            Entitlement::query()
                ->whereIn('order_id', $orderIds)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);

            return $orderIds->count();
        });
    }
}
