<?php

namespace App\Filament\Catalog;

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
    ): Schema {
        $type = $digitalOnly
            ? TextInput::make('type')
                ->label(__('filament/resources.products.fields.type'))
                ->default(ProductType::Digital->value)
                ->disabled()
                ->dehydrated()
            : Select::make('type')
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

        return $schema
            ->components([
                Section::make(__('filament/resources.products.sections.details'))
                    ->columns(2)
                    ->schema([
                        $category,
                        $type,
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
                    ->schema([
                        TextInput::make('price')
                            ->label(__('filament/resources.products.fields.price'))
                            ->numeric()
                            ->prefix(fn (): string => Money::currencyCode())
                            ->required(),
                        TextInput::make('compare_price')
                            ->label(__('filament/resources.products.fields.compare_price'))
                            ->numeric()
                            ->prefix(fn (): string => Money::currencyCode()),
                        TextInput::make('stock')
                            ->label(__('filament/resources.products.fields.stock'))
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText(function () use ($digitalOnly): ?string {
                                if (! $digitalOnly) {
                                    return null;
                                }

                                $help = __('filament/resources.products.fields.stock_courses_help');

                                return is_string($help) ? $help : null;
                            }),
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
                            // Include octet-stream: Windows/Laragon often misdetects JPG MIME types.
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
                    ])
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
