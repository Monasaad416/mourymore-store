<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
