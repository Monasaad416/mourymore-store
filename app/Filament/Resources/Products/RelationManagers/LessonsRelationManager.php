<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources.relations.lessons.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title.en')
                    ->label(__('filament/resources.relations.lessons.fields.title_en'))
                    ->required(),
                TextInput::make('title.ar')
                    ->label(__('filament/resources.relations.lessons.fields.title_ar'))
                    ->required(),
                TextInput::make('sort')
                    ->label(__('filament/resources.relations.lessons.fields.sort'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('duration_minutes')
                    ->numeric()
                    ->label(__('filament/resources.relations.lessons.fields.duration_minutes')),
                Toggle::make('is_preview')
                    ->label(__('filament/resources.relations.lessons.fields.is_preview')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('filament/resources.relations.lessons.model_label'))
            ->pluralModelLabel(__('filament/resources.relations.lessons.plural_model_label'))
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament/resources.relations.lessons.fields.title')),
                TextColumn::make('duration_minutes')
                    ->label(__('filament/resources.relations.lessons.fields.duration_minutes')),
                IconColumn::make('is_preview')
                    ->label(__('filament/resources.relations.lessons.fields.is_preview'))
                    ->boolean(),
                TextColumn::make('sort')
                    ->label(__('filament/resources.relations.lessons.fields.sort')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
