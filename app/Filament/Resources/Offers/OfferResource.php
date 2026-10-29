<?php

namespace App\Filament\Resources\Offers;

use App\Filament\Resources\Offers\Pages\ManageOffers;
use App\Models\Offer;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Ticket;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.sales');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.offers.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('filament/resources.offers.fields.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state))),
                        TextInput::make('sort')
                            ->label(__('filament/resources.offers.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('name.en')
                            ->label(__('filament/resources.offers.fields.name_en')),
                        TextInput::make('name.ar')
                            ->label(__('filament/resources.offers.fields.name_ar')),
                        Select::make('type')
                            ->label(__('filament/resources.offers.fields.type'))
                            ->options([
                                'percent' => __('filament/resources.offers.fields.type_percent'),
                                'fixed' => __('filament/resources.offers.fields.type_fixed'),
                            ])
                            ->default('percent')
                            ->required()
                            ->live(),
                        TextInput::make('value')
                            ->label(__('filament/resources.offers.fields.value'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(fn ($get): string => $get('type') === 'fixed' ? Money::currencyCode() : '%'),
                        TextInput::make('min_subtotal')
                            ->label(__('filament/resources.offers.fields.min_subtotal'))
                            ->numeric()
                            ->prefix(fn (): string => Money::currencyCode()),
                        TextInput::make('max_uses')
                            ->label(__('filament/resources.offers.fields.max_uses'))
                            ->numeric()
                            ->minValue(1),
                        DateTimePicker::make('starts_at')
                            ->label(__('filament/resources.offers.fields.starts_at')),
                        DateTimePicker::make('ends_at')
                            ->label(__('filament/resources.offers.fields.ends_at')),
                        Toggle::make('is_active')
                            ->label(__('filament/resources.offers.fields.is_active'))
                            ->default(true),
                        TextInput::make('used_count')
                            ->label(__('filament/resources.offers.fields.used_count'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('code')
                    ->label(__('filament/resources.offers.fields.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('filament/resources.offers.fields.name'))
                    ->placeholder('—'),
                TextColumn::make('type')
                    ->label(__('filament/resources.offers.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'fixed' => __('filament/resources.offers.fields.type_fixed'),
                        default => __('filament/resources.offers.fields.type_percent'),
                    }),
                TextColumn::make('value')
                    ->label(__('filament/resources.offers.fields.value'))
                    ->formatStateUsing(fn ($state, Offer $record): string => $record->type === 'fixed'
                        ? number_format((float) $state, 2).' '.Money::currencyCode()
                        : rtrim(rtrim(number_format((float) $state, 2), '0'), '.').'%'),
                TextColumn::make('used_count')
                    ->label(__('filament/resources.offers.fields.used_count'))
                    ->formatStateUsing(fn ($state, Offer $record): string => $record->max_uses === null
                        ? (string) $state
                        : $state.'/'.$record->max_uses),
                IconColumn::make('is_active')
                    ->label(__('filament/resources.offers.fields.is_active'))
                    ->boolean(),
                TextColumn::make('ends_at')
                    ->label(__('filament/resources.offers.fields.ends_at'))
                    ->dateTime()
                    ->placeholder('—'),
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
            'index' => ManageOffers::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.offers.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.offers.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.offers.plural_model_label');
    }
}
