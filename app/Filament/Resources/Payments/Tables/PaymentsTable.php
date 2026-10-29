<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order.number')
                    ->label(__('filament/resources.payments.fields.order'))
                    ->searchable(),
                TextColumn::make('gateway')
                    ->label(__('filament/resources.payments.fields.gateway'))
                    ->placeholder(__('filament/resources.payments.fields.gateway_cash')),
                TextColumn::make('merchant_reference')
                    ->label(__('filament/resources.payments.fields.merchant_reference'))
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->label(__('filament/resources.payments.fields.transaction_id'))
                    ->searchable(),
                TextColumn::make('amount')
                    ->label(__('filament/resources.payments.fields.amount'))
                    ->money(fn (Payment $record): string => filled($record->currency)
                        ? (string) $record->currency
                        : Money::currencyCode())
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament/resources.payments.fields.status'))
                    ->badge(),
                TextColumn::make('paid_at')
                    ->label(__('filament/resources.payments.fields.paid_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
