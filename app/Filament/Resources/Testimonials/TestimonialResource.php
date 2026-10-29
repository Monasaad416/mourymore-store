<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Testimonials';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.testimonials.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament/resources.testimonials.fields.name'))
                            ->required(),
                        Select::make('rating')
                            ->label(__('filament/resources.testimonials.fields.rating'))
                            ->options([
                                '5.0' => '5',
                                '4.0' => '4',
                                '3.0' => '3',
                                '2.0' => '2',
                                '1.0' => '1',
                            ]),
                        TextInput::make('role.en')
                            ->label(__('filament/resources.testimonials.fields.role_en')),
                        TextInput::make('role.ar')
                            ->label(__('filament/resources.testimonials.fields.role_ar')),
                        Textarea::make('quote.en')
                            ->label(__('filament/resources.testimonials.fields.quote_en'))
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('quote.ar')
                            ->label(__('filament/resources.testimonials.fields.quote_ar'))
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('sort')
                            ->label(__('filament/resources.testimonials.fields.sort'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_published')
                            ->label(__('filament/resources.testimonials.fields.is_published'))
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
                    ->label(__('filament/resources.testimonials.fields.name'))
                    ->searchable(),
                TextColumn::make('quote')
                    ->label(__('filament/resources.testimonials.fields.quote'))
                    ->limit(60),
                TextColumn::make('rating')
                    ->label(__('filament/resources.testimonials.fields.rating')),
                TextColumn::make('sort')
                    ->label(__('filament/resources.testimonials.fields.sort'))
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label(__('filament/resources.testimonials.fields.is_published'))
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
            'index' => ManageTestimonials::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources.testimonials.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources.testimonials.plural_model_label');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/resources.testimonials.plural_model_label');
    }
}
