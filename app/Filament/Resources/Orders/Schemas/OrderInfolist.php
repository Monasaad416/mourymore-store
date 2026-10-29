<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $currency = fn (?Order $record = null): string => filled($record?->currency)
            ? (string) $record->currency
            : Money::currencyCode();

        return $schema
            ->components([
                Section::make(__('filament/resources.orders.sections.order'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('number')
                            ->label(__('filament/resources.orders.fields.number')),
                        TextEntry::make('customer_name')
                            ->label(__('filament/resources.orders.fields.customer_name')),
                        TextEntry::make('phone')
                            ->label(__('filament/resources.orders.fields.phone')),
                        TextEntry::make('email')
                            ->label(__('filament/resources.orders.fields.email'))
                            ->placeholder('-'),
                        TextEntry::make('payment_method')
                            ->label(__('filament/resources.orders.fields.payment_method')),
                        TextEntry::make('payment_status')
                            ->label(__('filament/resources.orders.fields.payment_status'))
                            ->badge(),
                        TextEntry::make('delivery_status')
                            ->label(__('filament/resources.orders.fields.delivery_status'))
                            ->badge(),
                        TextEntry::make('tracking_number')
                            ->label(__('filament/resources.orders.fields.tracking_number'))
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label(__('filament/resources.orders.fields.status'))
                            ->badge(),
                        TextEntry::make('promo_code')
                            ->label(__('filament/resources.orders.fields.promo_code'))
                            ->placeholder('—'),
                        TextEntry::make('currency')
                            ->label(__('filament/resources.orders.fields.currency')),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.orders.sections.address'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('governorate')
                            ->label(__('filament/resources.orders.fields.governorate')),
                        TextEntry::make('city')
                            ->label(__('filament/resources.orders.fields.city')),
                        TextEntry::make('address')
                            ->label(__('filament/resources.orders.fields.address'))
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label(__('filament/resources.orders.fields.notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.orders.sections.totals'))
                    ->columns(5)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label(__('filament/resources.orders.fields.subtotal'))
                            ->money($currency),
                        TextEntry::make('discount')
                            ->label(__('filament/resources.orders.fields.discount'))
                            ->money($currency),
                        TextEntry::make('tax')
                            ->label(__('filament/resources.orders.fields.tax'))
                            ->money($currency),
                        TextEntry::make('shipping')
                            ->label(__('filament/resources.orders.fields.shipping'))
                            ->money($currency),
                        TextEntry::make('total')
                            ->label(__('filament/resources.orders.fields.total'))
                            ->money($currency),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.orders.sections.items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('filament/resources.orders.fields.item_name'))
                                    ->formatStateUsing(fn (mixed $state): string => OrderItem::labelFromName($state)),
                                TextEntry::make('product_type')
                                    ->label(__('filament/resources.orders.fields.product_type')),
                                TextEntry::make('qty')
                                    ->label(__('filament/resources.orders.fields.qty')),
                                TextEntry::make('price')
                                    ->label(__('filament/resources.orders.fields.price'))
                                    ->money(fn (): string => Money::currencyCode()),
                                TextEntry::make('line_total')
                                    ->label(__('filament/resources.orders.fields.line_total'))
                                    ->money(fn (): string => Money::currencyCode()),
                            ])
                            ->columns(5),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
