<?php

namespace App\Filament\Resources\ShippingMethods;

use App\Filament\Resources\ShippingMethods\Pages\ManageShippingMethods;
use App\Models\ShippingMethod;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Truck;

    protected static ?string $navigationLabel = 'Shipping methods';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.store');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.shipping_methods.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('filament/resources.shipping_methods.fields.code'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort')
                            ->label(__('filament/resources.shipping_methods.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('name.en')
                            ->label(__('filament/resources.shipping_methods.fields.name_en'))
                            ->required(),
                        TextInput::make('name.ar')
                            ->label(__('filament/resources.shipping_methods.fields.name_ar'))
                            ->required(),
                        TextInput::make('eta.en')
                            ->label(__('filament/resources.shipping_methods.fields.eta_en')),
                        TextInput::make('eta.ar')
                            ->label(__('filament/resources.shipping_methods.fields.eta_ar')),
                        TextInput::make('fee')
                            ->label(__('filament/resources.shipping_methods.fields.fee'))
                            ->numeric()
                            ->prefix(fn (): string => Money::currencyCode())
                            ->required(),
                        TextInput::make('free_over')
                            ->numeric()
                            ->prefix(fn (): string => Money::currencyCode())
                            ->label(__('filament/resources.shipping_methods.fields.free_over')),
                        TagsInput::make('governorates')
                            ->label(__('filament/resources.shipping_methods.fields.governorates'))
                            ->placeholder(__('filament/resources.shipping_methods.fields.governorates_placeholder'))
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('filament/resources.shipping_methods.fields.is_active'))
                            ->default(true),
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
                    ->label(__('filament/resources.shipping_methods.fields.name')),
                TextColumn::make('code')
                    ->label(__('filament/resources.shipping_methods.fields.code')),
                TextColumn::make('fee')
                    ->label(__('filament/resources.shipping_methods.fields.fee'))
                    ->money(fn (): string => Money::currencyCode()),
                TextColumn::make('eta')
                    ->label(__('filament/resources.shipping_methods.fields.eta')),
                IconColumn::make('is_active')
                    ->label(__('filament/resources.shipping_methods.fields.is_active'))
                    ->boolean(),
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
            'index' => ManageShippingMethods::route('/'),
        ];
    }
    public static function getModelLabel(): string
    {
        return __('filament/resources.shipping_methods.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.shipping_methods.plural_model_label');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/resources.shipping_methods.plural_model_label');
    }
}
