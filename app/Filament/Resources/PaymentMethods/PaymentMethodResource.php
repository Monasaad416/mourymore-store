<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Filament\Resources\PaymentMethods\Pages\ManagePaymentMethods;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CreditCard;

    protected static ?string $navigationLabel = 'Payment methods';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.store');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.payment_methods.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('filament/resources.payment_methods.fields.code'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort')
                            ->label(__('filament/resources.payment_methods.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('name.en')
                            ->label(__('filament/resources.payment_methods.fields.name_en'))
                            ->required(),
                        TextInput::make('name.ar')
                            ->label(__('filament/resources.payment_methods.fields.name_ar'))
                            ->required(),
                        Textarea::make('hint.en')
                            ->label(__('filament/resources.payment_methods.fields.hint_en'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('hint.ar')
                            ->label(__('filament/resources.payment_methods.fields.hint_ar'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Toggle::make('collects_on_delivery')
                            ->label(__('filament/resources.payment_methods.fields.collects_on_delivery')),
                        Toggle::make('is_active')
                            ->label(__('filament/resources.payment_methods.fields.is_active'))
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
                    ->label(__('filament/resources.payment_methods.fields.name')),
                TextColumn::make('code')
                    ->label(__('filament/resources.payment_methods.fields.code')),
                IconColumn::make('collects_on_delivery')
                    ->label(__('filament/resources.payment_methods.fields.collects_on_delivery_short'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('filament/resources.payment_methods.fields.is_active'))
                    ->boolean(),
                TextColumn::make('sort')
                    ->label(__('filament/resources.payment_methods.fields.sort')),
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
            'index' => ManagePaymentMethods::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.payment_methods.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.payment_methods.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.payment_methods.plural_model_label');
    }
}
