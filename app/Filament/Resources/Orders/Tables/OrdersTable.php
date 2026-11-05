<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Actions\RefundPaymentAction;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label(__('filament/resources.orders.fields.number'))
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label(__('filament/resources.orders.fields.customer_name'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('filament/resources.orders.fields.phone'))
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('filament/resources.orders.fields.payment_method'))
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label(__('filament/resources.orders.fields.payment_status'))
                    ->badge(),
                TextColumn::make('delivery_status')
                    ->label(__('filament/resources.orders.fields.delivery_status'))
                    ->badge(),
                TextColumn::make('total')
                    ->label(__('filament/resources.orders.fields.total'))
                    ->money(fn (Order $record): string => filled($record->currency)
                        ? (string) $record->currency
                        : Money::currencyCode())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filament/resources.orders.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('filament/resources.orders.fields.payment_status'))
                    ->options([
                        'pending' => __('filament/resources.statuses.pending'),
                        'paid' => __('filament/resources.statuses.paid'),
                        'failed' => __('filament/resources.statuses.failed'),
                        'due_on_delivery' => __('filament/resources.statuses.due_on_delivery'),
                        'refunded' => __('filament/resources.statuses.refunded'),
                    ]),
                SelectFilter::make('delivery_status')
                    ->label(__('filament/resources.orders.fields.delivery_status'))
                    ->options([
                        'pending' => __('filament/resources.statuses.pending'),
                        'shipped' => __('filament/resources.statuses.shipped'),
                        'delivered' => __('filament/resources.statuses.delivered'),
                        'not_required' => __('filament/resources.statuses.not_required'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('filament/resources.orders.fields.status'))
                    ->options([
                        'placed' => __('filament/resources.statuses.placed'),
                        'cancelled' => __('filament/resources.statuses.cancelled'),
                    ]),
                SelectFilter::make('payment_method')
                    ->label(__('filament/resources.orders.fields.payment_method'))
                    ->options(fn (): array => Order::query()
                        ->whereNotNull('payment_method')
                        ->distinct()
                        ->orderBy('payment_method')
                        ->pluck('payment_method', 'payment_method')
                        ->all()),
                Filter::make('created_at')
                    ->label(__('filament/resources.orders.fields.created_at'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('filament/resources.filters.from')),
                        DatePicker::make('until')
                            ->label(__('filament/resources.filters.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                RefundPaymentAction::make(),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
