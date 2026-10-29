<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return 'Paid revenue';
    }

    protected function getData(): array
    {
        $labels = [];
        $revenue = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $month->translatedFormat('M Y');

            $revenue[] = round((float) Payment::query()
                ->where('status', 'paid')
                ->where(function ($query) use ($start, $end): void {
                    $query->whereBetween('paid_at', [$start, $end])
                        ->orWhere(function ($fallback) use ($start, $end): void {
                            $fallback->whereNull('paid_at')
                                ->whereBetween('updated_at', [$start, $end]);
                        });
                })
                ->sum('amount'), 2);

            $counts[] = Payment::query()
                ->where('status', 'paid')
                ->where(function ($query) use ($start, $end): void {
                    $query->whereBetween('paid_at', [$start, $end])
                        ->orWhere(function ($fallback) use ($start, $end): void {
                            $fallback->whereNull('paid_at')
                                ->whereBetween('updated_at', [$start, $end]);
                        });
                })
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue,
                    'borderColor' => '#268f8e',
                    'backgroundColor' => 'rgba(38, 143, 142, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Payments',
                    'data' => $counts,
                    'borderColor' => '#c9a227',
                    'backgroundColor' => 'rgba(201, 162, 39, 0.12)',
                    'fill' => false,
                    'tension' => 0.35,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
