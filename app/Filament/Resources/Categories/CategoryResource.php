<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Squares2x2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.categories.sections.translations'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name.en')
                            ->label(__('filament/resources.categories.fields.name_en'))
                            ->required(),
                        TextInput::make('name.ar')
                            ->label(__('filament/resources.categories.fields.name_ar'))
                            ->required(),
                        Textarea::make('blurb.en')
                            ->label(__('filament/resources.categories.fields.blurb_en'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('blurb.ar')
                            ->label(__('filament/resources.categories.fields.blurb_ar'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.categories.sections.details'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')
                            ->label(__('filament/resources.categories.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort')
                            ->label(__('filament/resources.categories.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        FileUpload::make('image')
                            ->label(__('filament/resources.categories.fields.image'))
                            ->image()
                            ->directory('categories')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources.categories.fields.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('filament/resources.categories.fields.slug'))
                    ->searchable(),
                TextColumn::make('sort')
                    ->label(__('filament/resources.categories.fields.sort'))
                    ->sortable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('filament/resources.categories.fields.products')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.categories.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.categories.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.categories.plural_model_label');
    }
}
