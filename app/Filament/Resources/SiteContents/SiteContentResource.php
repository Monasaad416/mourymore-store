<?php

namespace App\Filament\Resources\SiteContents;

use App\Filament\Resources\SiteContents\Pages\ManageSiteContents;
use App\Models\SiteContent;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteContentResource extends Resource
{
    protected static ?string $model = SiteContent::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?string $slug = 'contents';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.site_contents.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('group')
                            ->label(__('filament/resources.site_contents.fields.group'))
                            ->required()
                            ->maxLength(64)
                            ->datalist(fn (): array => SiteContent::query()
                                ->distinct()
                                ->orderBy('group')
                                ->pluck('group')
                                ->all()),
                        TextInput::make('key')
                            ->label(__('filament/resources.site_contents.fields.key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->dehydrated()
                            ->helperText(__('filament/resources.site_contents.fields.key_help')),
                        Select::make('input')
                            ->label(__('filament/resources.site_contents.fields.input'))
                            ->options([
                                'text' => __('filament/resources.site_contents.fields.input_text'),
                                'textarea' => __('filament/resources.site_contents.fields.input_textarea'),
                            ])
                            ->default('text')
                            ->required()
                            ->live(),
                        TextInput::make('sort')
                            ->label(__('filament/resources.site_contents.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Textarea::make('value.en')
                            ->label(__('filament/resources.site_contents.fields.value_en'))
                            ->rows(fn ($get): int => $get('input') === 'textarea' ? 5 : 2)
                            ->required()
                            ->helperText(__('filament/resources.site_contents.fields.placeholders_help'))
                            ->columnSpanFull(),
                        Textarea::make('value.ar')
                            ->label(__('filament/resources.site_contents.fields.value_ar'))
                            ->rows(fn ($get): int => $get('input') === 'textarea' ? 5 : 2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('group')
            ->columns([
                TextColumn::make('group')
                    ->label(__('filament/resources.site_contents.fields.group'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('key')
                    ->label(__('filament/resources.site_contents.fields.key'))
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('filament/resources.site_contents.fields.value'))
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('input')
                    ->label(__('filament/resources.site_contents.fields.input'))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label(__('filament/resources.site_contents.fields.group'))
                    ->options(fn (): array => SiteContent::query()
                        ->distinct()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->all()),
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
            'index' => ManageSiteContents::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.site_contents.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.site_contents.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.site_contents.plural_model_label');
    }
}
