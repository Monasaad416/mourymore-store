<?php

namespace App\Filament\Resources\Courses;

use App\Enums\ProductKind;
use App\Filament\Catalog\CatalogForm;
use App\Filament\Catalog\CatalogInfolist;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\Pages\ViewCourse;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Products\RelationManagers\LessonsRelationManager;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::AcademicCap;

    protected static ?string $navigationLabel = 'Courses';

    protected static ?string $modelLabel = 'course';

    protected static ?string $pluralModelLabel = 'courses';

    protected static ?string $slug = 'courses';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return CatalogForm::configure($schema, digitalOnly: true, defaultCategorySlug: 'courses');
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
        return parent::getEloquentQuery()->where('kind', ProductKind::Course);
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class,
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'view' => ViewCourse::route('/{record}'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.courses.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.courses.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.courses.plural_model_label');
    }
}
