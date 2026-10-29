<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $startOfMonth = now()->startOfMonth();
        $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = now()->subMonthNoOverflow()->endOfMonth();

        $revenueThisMonth = (float) Payment::query()
            ->where('status', 'paid')
            ->where(function ($query) use ($startOfMonth): void {
                $query->where('paid_at', '>=', $startOfMonth)
                    ->orWhere(function ($fallback) use ($startOfMonth): void {
                        $fallback->whereNull('paid_at')
                            ->where('updated_at', '>=', $startOfMonth);
                    });
            })
            ->sum('amount');

        $revenueLastMonth = (float) Payment::query()
            ->where('status', 'paid')
            ->where(function ($query) use ($startOfLastMonth, $endOfLastMonth): void {
                $query->whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
                    ->orWhere(function ($fallback) use ($startOfLastMonth, $endOfLastMonth): void {
                        $fallback->whereNull('paid_at')
                            ->whereBetween('updated_at', [$startOfLastMonth, $endOfLastMonth]);
                    });
            })
            ->sum('amount');

        $ordersThisMonth = Order::query()->where('created_at', '>=', $startOfMonth)->count();
        $ordersLastMonth = Order::query()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        $paidOrders = Order::query()->where('payment_status', 'paid')->count();
        $pendingOrders = Order::query()->where('payment_status', 'pending')->count();

        $revenueTotal = (float) Payment::query()->where('status', 'paid')->sum('amount');

        $paymentsThisMonth = Payment::query()
            ->where('status', 'paid')
            ->where(function ($query) use ($startOfMonth): void {
                $query->where('paid_at', '>=', $startOfMonth)
                    ->orWhere(function ($fallback) use ($startOfMonth): void {
                        $fallback->whereNull('paid_at')
                            ->where('updated_at', '>=', $startOfMonth);
                    });
            })
            ->count();

        $newCustomers = User::query()
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        return [
            Stat::make('Revenue this month', $this->money($revenueThisMonth))
                ->description($this->deltaDescription($revenueThisMonth, $revenueLastMonth))
                ->descriptionIcon($revenueThisMonth >= $revenueLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueThisMonth >= $revenueLastMonth ? 'success' : 'danger')
                ->chart($this->sparklineForPaidPayments()),

            Stat::make('Revenue', $this->money($revenueTotal))
                ->description('Paid orders: '.$paidOrders)
                ->color('success'),

            Stat::make('Orders this month', (string) $ordersThisMonth)
                ->description($this->deltaDescription($ordersThisMonth, $ordersLastMonth, absolute: true))
                ->descriptionIcon($ordersThisMonth >= $ordersLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary'),

            Stat::make('Pending payment', (string) $pendingOrders)
                ->description('Paid payments this month: '.$paymentsThisMonth)
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),

            Stat::make('New customers', (string) $newCustomers)
                ->color('info'),
        ];
    }

    protected function money(float $amount): string
    {
        return Money::format($amount);
    }

    protected function deltaDescription(float|int $current, float|int $previous, bool $absolute = false): string
    {
        $diff = $current - $previous;

        if ($absolute) {
            $value = ($diff >= 0 ? '+' : '').$diff;
        } else {
            $value = ($diff >= 0 ? '+' : '').$this->money((float) $diff);
        }

        return 'Vs last month: '.$value;
    }

    /**
     * @return list<float|int>
     */
    protected function sparklineForPaidPayments(): array
    {
        $points = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $points[] = (float) Payment::query()
                ->where('status', 'paid')
                ->whereDate('paid_at', $day)
                ->sum('amount');
        }

        return $points;
    }
}
