<?php

namespace App\Filament\Resources\Entitlements;

use App\Filament\Resources\Entitlements\Pages\ManageEntitlements;
use App\Models\Entitlement;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EntitlementResource extends Resource
{
    protected static ?string $model = Entitlement::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Key;

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.sales');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.entitlements.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name')
                            ->label(__('filament/resources.entitlements.fields.product')),
                        TextEntry::make('order.number')
                            ->label(__('filament/resources.entitlements.fields.order')),
                        TextEntry::make('user.name')
                            ->label(__('filament/resources.entitlements.fields.user'))
                            ->placeholder('—'),
                        TextEntry::make('user.email')
                            ->label(__('filament/resources.entitlements.fields.email'))
                            ->placeholder('—'),
                        TextEntry::make('granted_at')
                            ->label(__('filament/resources.entitlements.fields.granted_at'))
                            ->dateTime(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('filament/resources.entitlements.fields.product'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('order.number')
                    ->label(__('filament/resources.entitlements.fields.order'))
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label(__('filament/resources.entitlements.fields.email'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('granted_at')
                    ->label(__('filament/resources.entitlements.fields.granted_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['product', 'order', 'user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEntitlements::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.entitlements.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.entitlements.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.entitlements.plural_model_label');
    }
}
