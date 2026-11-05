<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ManageSubscriptions;
use App\Models\Subscription;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Envelope;

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?string $recordTitleAttribute = 'email';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.subscriptions.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label(__('filament/resources.subscriptions.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label(__('filament/resources.subscriptions.fields.name')),
                        Select::make('locale')
                            ->label(__('filament/resources.subscriptions.fields.locale'))
                            ->options([
                                'ar' => __('filament/resources.locales.ar'),
                                'en' => __('filament/resources.locales.en'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label(__('filament/resources.subscriptions.fields.email'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('filament/resources.subscriptions.fields.name'))
                    ->searchable(),
                TextColumn::make('locale')
                    ->label(__('filament/resources.subscriptions.fields.locale')),
                TextColumn::make('created_at')
                    ->label(__('filament/resources.subscriptions.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->label(__('filament/resources.subscriptions.fields.locale'))
                    ->options([
                        'ar' => __('filament/resources.locales.ar'),
                        'en' => __('filament/resources.locales.en'),
                    ]),
                Filter::make('created_at')
                    ->label(__('filament/resources.subscriptions.fields.created_at'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('filament/resources.filters.from')),
                        DatePicker::make('until')
                            ->label(__('filament/resources.filters.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('created_at', '<=', $date),
                            );
                    }),
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
            'index' => ManageSubscriptions::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.subscriptions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.subscriptions.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.subscriptions.plural_model_label');
    }
}
