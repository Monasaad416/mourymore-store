<?php

namespace App\Filament\Catalog;

use App\Models\Product;
use App\Support\Money;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\VerticalAlignment;

class CatalogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Flex::make([
                            ImageEntry::make('image')
                                ->hiddenLabel()
                                ->disk('public')
                                ->visibility('public')
                                ->defaultImageUrl(fn (Product $record): string => $record->imageUrl())
                                ->imageHeight('18rem')
                                ->imageWidth('14rem')
                                ->extraImgAttributes([
                                    'class' => 'rounded-2xl object-cover ring-1 ring-gray-950/5 dark:ring-white/10',
                                    'alt' => '',
                                ]),
                            Group::make([
                                TextEntry::make('name')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->columnSpanFull(),
                                TextEntry::make('brand')
                                    ->label(__('filament/resources.products.fields.brand'))
                                    ->placeholder('—')
                                    ->color('gray'),
                                TextEntry::make('slug')
                                    ->label(__('filament/resources.products.fields.slug'))
                                    ->copyable()
                                    ->color('gray'),
                                TextEntry::make('category.name')
                                    ->label(__('filament/resources.products.fields.category'))
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('type')
                                    ->badge(),
                                TextEntry::make('kind')
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('badge')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('—'),
                            ])
                                ->columns(2)
                                ->grow(),
                        ])
                            ->from('md')
                            ->verticalAlignment(VerticalAlignment::Start),
                    ]),
                Section::make(__('filament/resources.products.sections.pricing'))
                    ->columnSpanFull()
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ])
                    ->schema([
                        TextEntry::make('price')
                            ->money(fn (): string => Money::currencyCode())
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('compare_price')
                            ->money(fn (): string => Money::currencyCode())
                            ->placeholder('—')
                            ->color('gray'),
                        TextEntry::make('stock')
                            ->badge()
                            ->color(fn ($state): string => ((int) $state) > 0 ? 'success' : 'danger'),
                        TextEntry::make('rating')
                            ->suffix('/5')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('reviews_count')
                            ->label(__('filament/resources.products.fields.reviews_count')),
                        IconEntry::make('is_featured')
                            ->boolean(),
                        IconEntry::make('is_deal')
                            ->boolean(),
                    ]),
                Section::make(__('filament/resources.products.sections.content'))
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextEntry::make('description')
                            ->label(__('filament/resources.products.fields.description'))
                            ->prose(),
                        TextEntry::make('highlights')
                            ->label(__('filament/resources.products.fields.highlights'))
                            ->bulleted()
                            ->listWithLineBreaks()
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
