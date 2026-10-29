<?php

namespace App\Filament\Resources\Books;

use App\Enums\ProductKind;
use App\Enums\ProductType;
use App\Filament\Catalog\CatalogForm;
use App\Filament\Catalog\CatalogInfolist;
use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\EditBook;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Filament\Resources\Books\Pages\ViewBook;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\RelationManagers\BookDetailRelationManager;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::BookOpen;

    protected static ?string $navigationLabel = 'Books';

    protected static ?string $modelLabel = 'book';

    protected static ?string $pluralModelLabel = 'books';

    protected static ?string $slug = 'books';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return CatalogForm::configure($schema, typeOptions: [
            ProductType::Physical->value => __('filament/resources.products.fields.type_printed_book'),
            ProductType::Digital->value => __('filament/resources.products.fields.type_digital_book'),
        ], defaultCategorySlug: 'books');
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatalogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductResource::table($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('kind', ProductKind::Book);
    }

    public static function getRelations(): array
    {
        return [
            BookDetailRelationManager::class,
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'view' => ViewBook::route('/{record}'),
            'edit' => EditBook::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.books.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.books.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.books.plural_model_label');
    }
}
