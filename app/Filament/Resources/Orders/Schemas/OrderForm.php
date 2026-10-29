<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.orders.sections.fulfillment'))
                    ->columns(2)
                    ->schema([
                        Select::make('payment_status')
                            ->label(__('filament/resources.orders.fields.payment_status'))
                            ->options([
                                'pending' => __('filament/resources.statuses.pending'),
                                'paid' => __('filament/resources.statuses.paid'),
                                'failed' => __('filament/resources.statuses.failed'),
                                'due_on_delivery' => __('filament/resources.statuses.due_on_delivery'),
                            ])
                            ->required(),
                        Select::make('delivery_status')
                            ->label(__('filament/resources.orders.fields.delivery_status'))
                            ->options([
                                'pending' => __('filament/resources.statuses.pending'),
                                'shipped' => __('filament/resources.statuses.shipped'),
                                'delivered' => __('filament/resources.statuses.delivered'),
                                'not_required' => __('filament/resources.statuses.not_required'),
                            ])
                            ->required(),
                        Select::make('status')
                            ->label(__('filament/resources.orders.fields.status'))
                            ->options([
                                'placed' => __('filament/resources.statuses.placed'),
                                'cancelled' => __('filament/resources.statuses.cancelled'),
                            ])
                            ->required(),
                        TextInput::make('tracking_number')
                            ->label(__('filament/resources.orders.fields.tracking_number')),
                        Textarea::make('notes')
                            ->label(__('filament/resources.orders.fields.notes'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
