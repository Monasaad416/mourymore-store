<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Support\Money;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.payments.sections.form'))
                    ->columns(2)
                    ->schema([
                        Select::make('order_id')
                            ->label(__('filament/resources.payments.fields.order'))
                            ->relationship('order', 'number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('payment_method_id')
                            ->label(__('filament/resources.payments.fields.payment_method'))
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('gateway')
                            ->label(__('filament/resources.payments.fields.gateway'))
                            ->placeholder('paymob'),
                        TextInput::make('merchant_reference')
                            ->label(__('filament/resources.payments.fields.merchant_reference')),
                        TextInput::make('transaction_id')
                            ->label(__('filament/resources.payments.fields.transaction_id')),
                        TextInput::make('reference')
                            ->label(__('filament/resources.payments.fields.reference')),
                        TextInput::make('amount')
                            ->numeric()
                            ->label(__('filament/resources.payments.fields.amount'))
                            ->prefix(fn (): string => Money::currencyCode())
                            ->required(),
                        TextInput::make('currency')
                            ->default(fn (): string => Money::currencyCode())
                            ->label(__('filament/resources.payments.fields.currency'))
                            ->required()
                            ->maxLength(8),
                        Select::make('status')
                            ->label(__('filament/resources.payments.fields.status'))
                            ->options([
                                'pending' => __('filament/resources.statuses.pending'),
                                'paid' => __('filament/resources.statuses.paid'),
                                'failed' => __('filament/resources.statuses.failed'),
                                'due_on_delivery' => __('filament/resources.statuses.due_on_delivery'),
                                'refunded' => __('filament/resources.statuses.refunded'),
                            ])
                            ->required(),
                        DateTimePicker::make('paid_at')
                            ->label(__('filament/resources.payments.fields.paid_at')),
                        TextInput::make('failure_code')
                            ->label(__('filament/resources.payments.fields.failure_code')),
                        Textarea::make('failure_message')
                            ->label(__('filament/resources.payments.fields.failure_message'))
                            ->columnSpanFull(),
                        TextInput::make('checkout_url')
                            ->url()
                            ->label(__('filament/resources.payments.fields.checkout_url'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
