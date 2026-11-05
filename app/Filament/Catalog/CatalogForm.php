<?php

namespace App\Filament\Catalog;

use App\Enums\BookFormat;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use App\Support\Money;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\File;

class CatalogForm
{
    /**
     * @param  array<string, string>|null  $typeOptions
     */
    public static function configure(
        Schema $schema,
        bool $digitalOnly = false,
        ?array $typeOptions = null,
        ?string $defaultCategorySlug = null,
        bool $bookFormats = false,
    ): Schema {
        if ($bookFormats) {
            $typeOrFormats = Select::make('formats')
                ->label(__('filament/resources.products.fields.formats'))
                ->helperText(__('filament/resources.products.fields.formats_help'))
                ->multiple()
                ->options([
                    BookFormat::Print->value => __('filament/resources.products.fields.format_print'),
                    BookFormat::Digital->value => __('filament/resources.products.fields.format_digital'),
                ])
                ->default([BookFormat::Print->value])
                ->required()
                ->minItems(1)
                ->live();
        } elseif ($digitalOnly) {
            $typeOrFormats = TextInput::make('type')
                ->label(__('filament/resources.products.fields.type'))
                ->default(ProductType::Digital->value)
                ->disabled()
                ->dehydrated();
        } else {
            $typeOrFormats = Select::make('type')
                ->label(__('filament/resources.products.fields.type'))
                ->options($typeOptions ?? [
                    ProductType::Physical->value => __('filament/resources.products.fields.type_physical'),
                    ProductType::Digital->value => __('filament/resources.products.fields.type_digital'),
                ])
                ->default(ProductType::Physical->value)
                ->required()
                ->helperText(function () use ($typeOptions): ?string {
                    if ($typeOptions === null) {
                        return null;
                    }

                    $help = __('filament/resources.products.fields.type_book_help');

                    return is_string($help) ? $help : null;
                });
        }

        $category = Select::make('category_id')
            ->label(__('filament/resources.products.fields.category'))
            ->relationship('category', 'name')
            ->searchable()
            ->preload()
            ->required();

        if (filled($defaultCategorySlug)) {
            $categoryId = Category::query()->where('slug', $defaultCategorySlug)->value('id');

            $category
                ->default($categoryId)
                ->disabled()
                ->dehydrated();
        }

        $pricingFields = [
            TextInput::make('price')
                ->label(__('filament/resources.products.fields.price'))
                ->numeric()
                ->prefix(fn (): string => Money::currencyCode())
                ->required()
                ->helperText(function () use ($bookFormats): ?string {
                    if (! $bookFormats) {
                        return null;
                    }

                    $help = __('filament/resources.products.fields.price_print_help');

                    return is_string($help) ? $help : null;
                }),
            TextInput::make('compare_price')
                ->label(__('filament/resources.products.fields.compare_price'))
                ->numeric()
                ->prefix(fn (): string => Money::currencyCode()),
            TextInput::make('stock')
                ->label(__('filament/resources.products.fields.stock'))
                ->numeric()
                ->default(0)
                ->required()
                ->helperText(function ($get) use ($bookFormats, $digitalOnly): ?string {
                    if ($digitalOnly) {
                        $help = __('filament/resources.products.fields.stock_courses_help');

                        return is_string($help) ? $help : null;
                    }

                    if ($bookFormats) {
                        $formats = $get('formats');
                        $formats = is_array($formats) ? $formats : [];

                        if (! in_array(BookFormat::Print->value, $formats, true)) {
                            return __('filament/resources.products.fields.stock_digital_only_help');
                        }

                        return __('filament/resources.products.fields.stock_print_help');
                    }

                    return null;
                }),
        ];

        if ($bookFormats) {
            $pricingFields[] = TextInput::make('digital_price')
                ->label(__('filament/resources.products.fields.digital_price'))
                ->numeric()
                ->minValue(0)
                ->prefix(fn (): string => Money::currencyCode())
                ->helperText(__('filament/resources.products.fields.digital_price_help'))
                ->visible(fn ($get): bool => in_array(BookFormat::Digital->value, is_array($get('formats')) ? $get('formats') : [], true));

            $pricingFields[] = FileUpload::make('file_path')
                ->label(__('filament/resources.products.fields.digital_file'))
                ->disk('public')
                ->directory('books')
                ->acceptedFileTypes(['application/pdf'])
                ->helperText(__('filament/resources.products.fields.digital_file_help'))
                ->visible(fn ($get): bool => in_array(BookFormat::Digital->value, is_array($get('formats')) ? $get('formats') : [], true))
                ->required(fn ($get): bool => in_array(BookFormat::Digital->value, is_array($get('formats')) ? $get('formats') : [], true));
        }

        $pricingFields = [
            ...$pricingFields,
            TextInput::make('rating')
                ->label(__('filament/resources.products.fields.rating'))
                ->numeric()
                ->minValue(0)
                ->maxValue(5)
                ->step(0.1)
                ->default(0)
                ->helperText(__('filament/resources.products.fields.rating_help')),
            TextInput::make('reviews_count')
                ->label(__('filament/resources.products.fields.reviews_count'))
                ->numeric()
                ->integer()
                ->minValue(0)
                ->default(0),
            FileUpload::make('image')
                ->label(__('filament/resources.products.fields.image'))
                ->disk('public')
                ->directory('products')
                ->visibility('public')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/jpg',
                    'image/pjpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'application/octet-stream',
                ])
                ->rules([
                    File::default()
                        ->extensions(['jpg', 'jpeg', 'png', 'webp', 'gif'])
                        ->max(5 * 1024),
                ])
                ->required(),
            Toggle::make('is_featured')
                ->label(__('filament/resources.products.fields.is_featured')),
            Toggle::make('is_deal')
                ->label(__('filament/resources.products.fields.is_deal')),
        ];

        return $schema
            ->components([
                Section::make(__('filament/resources.products.sections.details'))
                    ->columns(2)
                    ->schema([
                        $category,
                        $typeOrFormats,
                        TextInput::make('name.en')
                            ->label(__('filament/resources.products.fields.name_en'))
                            ->required(),
                        TextInput::make('name.ar')
                            ->label(__('filament/resources.products.fields.name_ar'))
                            ->required(),
                        TextInput::make('slug')
                            ->label(__('filament/resources.products.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('brand.en')
                            ->label(__('filament/resources.products.fields.brand_en')),
                        TextInput::make('brand.ar')
                            ->label(__('filament/resources.products.fields.brand_ar')),
                        Select::make('badge')
                            ->label(__('filament/resources.products.fields.badge'))
                            ->options([
                                'express' => __('filament/resources.products.fields.badge_express'),
                                'bestseller' => __('filament/resources.products.fields.badge_bestseller'),
                                'deal' => __('filament/resources.products.fields.badge_deal'),
                                'new' => __('filament/resources.products.fields.badge_new'),
                            ])
                            ->placeholder(__('filament/resources.products.fields.badge_none')),
                        Textarea::make('description.en')
                            ->label(__('filament/resources.products.fields.description_en'))
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('description.ar')
                            ->label(__('filament/resources.products.fields.description_ar'))
                            ->rows(4)
                            ->columnSpanFull(),
                        TagsInput::make('highlights.en')
                            ->label(__('filament/resources.products.fields.highlights_en'))
                            ->placeholder(__('filament/resources.products.fields.highlights_placeholder'))
                            ->columnSpanFull(),
                        TagsInput::make('highlights.ar')
                            ->label(__('filament/resources.products.fields.highlights_ar'))
                            ->placeholder(__('filament/resources.products.fields.highlights_placeholder'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.products.sections.pricing'))
                    ->columns(3)
                    ->schema($pricingFields)
                    ->columnSpanFull(),
                Section::make(__('filament/resources.products.sections.share'))
                    ->description(__('filament/resources.products.sections.share_help'))
                    ->schema([
                        TextInput::make('share_link')
                            ->label(__('filament/resources.products.fields.share_link'))
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable()
                            ->visible(fn (?Product $record): bool => $record instanceof Product)
                            ->formatStateUsing(fn (?Product $record): ?string => $record instanceof Product
                                ? route('product', $record)
                                : null),
                    ])
                    ->visible(fn (?Product $record): bool => $record instanceof Product)
                    ->columnSpanFull(),
            ]);
    }
}
