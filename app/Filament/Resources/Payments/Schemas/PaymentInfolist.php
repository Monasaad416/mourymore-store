<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use App\Support\Money;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.payments.sections.form'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order.number')
                            ->label(__('filament/resources.payments.fields.order')),
                        TextEntry::make('gateway')
                            ->label(__('filament/resources.payments.fields.gateway'))
                            ->placeholder(__('filament/resources.payments.fields.gateway_cash')),
                        TextEntry::make('status')
                            ->label(__('filament/resources.payments.fields.status'))
                            ->badge(),
                        TextEntry::make('amount')
                            ->label(__('filament/resources.payments.fields.amount'))
                            ->money(fn (?Payment $record): string => filled($record?->currency)
                                ? (string) $record->currency
                                : Money::currencyCode()),
                        TextEntry::make('currency')
                            ->label(__('filament/resources.payments.fields.currency')),
                        TextEntry::make('paid_at')
                            ->label(__('filament/resources.payments.fields.paid_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('merchant_reference')
                            ->label(__('filament/resources.payments.fields.merchant_reference'))
                            ->placeholder('-'),
                        TextEntry::make('transaction_id')
                            ->label(__('filament/resources.payments.fields.transaction_id'))
                            ->placeholder('-'),
                        TextEntry::make('reference')
                            ->label(__('filament/resources.payments.fields.reference'))
                            ->placeholder('-'),
                        TextEntry::make('failure_code')
                            ->label(__('filament/resources.payments.fields.failure_code'))
                            ->placeholder('-'),
                        TextEntry::make('failure_message')
                            ->label(__('filament/resources.payments.fields.failure_message'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('checkout_url')
                            ->label(__('filament/resources.payments.fields.checkout_url'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
