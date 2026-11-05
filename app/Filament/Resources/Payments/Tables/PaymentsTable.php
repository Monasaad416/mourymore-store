<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Actions\RefundPaymentAction;
use App\Models\Payment;
use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament/resources.payments.fields.status'))
                    ->options([
                        'pending' => __('filament/resources.statuses.pending'),
                        'paid' => __('filament/resources.statuses.paid'),
                        'failed' => __('filament/resources.statuses.failed'),
                        'due_on_delivery' => __('filament/resources.statuses.due_on_delivery'),
                        'refunded' => __('filament/resources.statuses.refunded'),
                    ]),
                SelectFilter::make('gateway')
                    ->label(__('filament/resources.payments.fields.gateway'))
                    ->options(fn (): array => Payment::query()
                        ->whereNotNull('gateway')
                        ->distinct()
                        ->orderBy('gateway')
                        ->pluck('gateway', 'gateway')
                        ->all()),
                Filter::make('paid_at')
                    ->label(__('filament/resources.payments.fields.paid_at'))
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
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('paid_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('paid_at', '<=', $date),
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
