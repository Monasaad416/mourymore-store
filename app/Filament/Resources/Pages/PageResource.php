<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Pages\ViewPage;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentDuplicate;

    protected static ?string $slug = 'pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.pages.sections.overview'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('title.en')
                            ->label(__('filament/resources.pages.fields.title_en'))
                            ->required(),
                        TextInput::make('title.ar')
                            ->label(__('filament/resources.pages.fields.title_ar'))
                            ->required(),
                        TextInput::make('slug')
                            ->label(__('filament/resources.pages.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText(__('filament/resources.pages.fields.slug_help')),
                        TextInput::make('sort')
                            ->label(__('filament/resources.pages.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label(__('filament/resources.pages.fields.published_at'))
                            ->default(now()),
                        Toggle::make('is_published')
                            ->label(__('filament/resources.pages.fields.is_published'))
                            ->default(true),
                        Toggle::make('show_in_footer')
                            ->label(__('filament/resources.pages.fields.show_in_footer'))
                            ->default(true),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.pages.sections.content'))
                    ->columns(2)
                    ->schema([
                        Textarea::make('body.en')
                            ->label(__('filament/resources.pages.fields.body_en'))
                            ->rows(12)
                            ->required(),
                        Textarea::make('body.ar')
                            ->label(__('filament/resources.pages.fields.body_ar'))
                            ->rows(12)
                            ->required(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.pages.sections.seo'))
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title.en')
                            ->label(__('filament/resources.pages.fields.meta_title_en')),
                        TextInput::make('meta_title.ar')
                            ->label(__('filament/resources.pages.fields.meta_title_ar')),
                        Textarea::make('meta_description.en')
                            ->label(__('filament/resources.pages.fields.meta_description_en'))
                            ->rows(2),
                        Textarea::make('meta_description.ar')
                            ->label(__('filament/resources.pages.fields.meta_description_ar'))
                            ->rows(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.pages.sections.overview'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('filament/resources.pages.fields.title'))
                            ->columnSpan(2),
                        TextEntry::make('slug')
                            ->label(__('filament/resources.pages.fields.slug'))
                            ->copyable(),
                        IconEntry::make('is_published')
                            ->label(__('filament/resources.pages.fields.is_published'))
                            ->boolean(),
                        IconEntry::make('show_in_footer')
                            ->label(__('filament/resources.pages.fields.show_in_footer'))
                            ->boolean(),
                        TextEntry::make('published_at')
                            ->label(__('filament/resources.pages.fields.published_at'))
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.pages.sections.content'))
                    ->schema([
                        TextEntry::make('body')
                            ->label(__('filament/resources.pages.fields.body'))
                            ->prose()
                            ->markdown()
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
                TextColumn::make('title')
                    ->label(__('filament/resources.pages.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('filament/resources.pages.fields.slug'))
                    ->searchable()
                    ->copyable(),
                IconColumn::make('is_published')
                    ->label(__('filament/resources.pages.fields.is_published'))
                    ->boolean(),
                IconColumn::make('show_in_footer')
                    ->label(__('filament/resources.pages.fields.show_in_footer'))
                    ->boolean(),
                TextColumn::make('sort')
                    ->label(__('filament/resources.pages.fields.sort'))
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'view' => ViewPage::route('/{record}'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.pages.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.pages.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.pages.plural_model_label');
    }
}
