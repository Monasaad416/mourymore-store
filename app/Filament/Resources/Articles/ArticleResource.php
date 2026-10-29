<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use App\Filament\Resources\Articles\Schemas\ArticleInfolist;
use App\Models\Article;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentText;

    protected static ?string $navigationLabel = 'Articles';

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'articles';

    protected static ?string $slug = 'articles';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.articles.sections.overview'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('title.en')
                            ->label(__('filament/resources.articles.fields.title_en'))
                            ->required(),
                        TextInput::make('title.ar')
                            ->label(__('filament/resources.articles.fields.title_ar'))
                            ->required(),
                        TextInput::make('slug')
                            ->label(__('filament/resources.articles.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('published_at')
                            ->label(__('filament/resources.articles.fields.published_at'))
                            ->default(now()),
                        FileUpload::make('image')
                            ->label(__('filament/resources.articles.fields.image'))
                            ->image()
                            ->disk('public')
                            ->directory('articles'),
                        Toggle::make('is_published')
                            ->label(__('filament/resources.articles.fields.is_published'))
                            ->default(false),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.articles.sections.content'))
                    ->columns(2)
                    ->schema([
                        Textarea::make('excerpt.en')
                            ->label(__('filament/resources.articles.fields.excerpt_en'))
                            ->rows(3)
                            ->required(),
                        Textarea::make('excerpt.ar')
                            ->label(__('filament/resources.articles.fields.excerpt_ar'))
                            ->rows(3)
                            ->required(),
                        Textarea::make('body.en')
                            ->label(__('filament/resources.articles.fields.body_en'))
                            ->rows(10)
                            ->required(),
                        Textarea::make('body.ar')
                            ->label(__('filament/resources.articles.fields.body_ar'))
                            ->rows(10)
                            ->required(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.articles.sections.seo'))
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title.en')
                            ->label(__('filament/resources.articles.fields.meta_title_en'))
                            ->helperText(__('filament/resources.articles.fields.meta_title_help')),
                        TextInput::make('meta_title.ar')
                            ->label(__('filament/resources.articles.fields.meta_title_ar')),
                        Textarea::make('meta_description.en')
                            ->label(__('filament/resources.articles.fields.meta_description_en'))
                            ->helperText(__('filament/resources.articles.fields.meta_description_help'))
                            ->rows(2),
                        Textarea::make('meta_description.ar')
                            ->label(__('filament/resources.articles.fields.meta_description_ar'))
                            ->rows(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArticleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament/resources.articles.fields.title'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('filament/resources.articles.fields.slug'))
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label(__('filament/resources.articles.fields.is_published'))
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label(__('filament/resources.articles.fields.published_at'))
                    ->dateTime()
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
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'view' => ViewArticle::route('/{record}'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.articles.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.articles.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.articles.plural_model_label');
    }
}
