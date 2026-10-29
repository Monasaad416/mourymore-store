<?php

namespace App\Filament\Widgets;

use App\Enums\ProductKind;
use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Products', (string) Product::query()->where('kind', ProductKind::Product)->count()),
            Stat::make('Books', (string) Product::query()->where('kind', ProductKind::Book)->count()),
            Stat::make('Courses', (string) Product::query()->where('kind', ProductKind::Course)->count()),
            Stat::make('Categories', (string) Category::query()->count()),
        ];
    }
}
