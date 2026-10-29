<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('filament/resources.orders.widgets.latest_heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::query()->with('user')->latest('id')->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('number')
                    ->label(__('filament/resources.orders.fields.number')),
                TextColumn::make('customer_name')
                    ->label(__('filament/resources.orders.fields.customer_name')),
                TextColumn::make('total')
                    ->label(__('filament/resources.orders.fields.total'))
                    ->money(fn (Order $record): string => filled($record->currency)
                        ? (string) $record->currency
                        : Money::currencyCode()),
                TextColumn::make('payment_status')
                    ->label(__('filament/resources.orders.widgets.payment'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('filament/resources.orders.fields.created_at'))
                    ->since(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading(__('filament/resources.orders.widgets.empty'));
    }
}
