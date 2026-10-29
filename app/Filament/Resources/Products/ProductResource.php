<?php

namespace App\Filament\Resources\Products;

use App\Enums\ProductKind;
use App\Filament\Catalog\CatalogForm;
use App\Filament\Catalog\CatalogInfolist;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ShoppingBag;

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return CatalogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatalogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->visibility('public')
                    ->defaultImageUrl(fn (Product $record): string => $record->imageUrl())
                    ->square()
                    ->imageSize(48)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                TextColumn::make('name')
                    ->label(__('filament/resources.products.fields.name'))
                    ->searchable()
                    ->wrap()
                    ->description(fn (Product $record): ?string => $record->brand),
                TextColumn::make('category.name')
                    ->label(__('filament/resources.products.fields.category'))
                    ->toggleable(),
                TextColumn::make('type')
                    ->label(__('filament/resources.products.fields.type'))
                    ->badge(),
                TextColumn::make('price')
                    ->label(__('filament/resources.products.fields.price'))
                    ->money(fn (): string => Money::currencyCode())
                    ->sortable(),
                TextColumn::make('stock')
                    ->label(__('filament/resources.products.fields.stock'))
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_featured')
                    ->label(__('filament/resources.products.fields.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_deal')
                    ->label(__('filament/resources.products.fields.is_deal'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('kind', ProductKind::Product);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.products.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.products.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.products.plural_model_label');
    }
}
