<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\ProductType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BookDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'bookDetail';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources.relations.book_detail.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author.en')
                    ->label(__('filament/resources.relations.book_detail.fields.author_en'))
                    ->required(),
                TextInput::make('author.ar')
                    ->label(__('filament/resources.relations.book_detail.fields.author_ar')),
                TextInput::make('pages')
                    ->label(__('filament/resources.relations.book_detail.fields.pages'))
                    ->numeric(),
                FileUpload::make('file_path')
                    ->label(__('filament/resources.relations.book_detail.fields.file_path'))
                    ->disk('public')
                    ->directory('books')
                    ->acceptedFileTypes(['application/pdf'])
                    ->helperText(__('filament/resources.relations.book_detail.fields.file_path_help'))
                    ->visible(fn (): bool => $this->getOwnerRecord()?->type === ProductType::Digital),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('filament/resources.relations.book_detail.model_label'))
            ->pluralModelLabel(__('filament/resources.relations.book_detail.plural_model_label'))
            ->columns([
                TextColumn::make('author')
                    ->label(__('filament/resources.relations.book_detail.fields.author')),
                TextColumn::make('pages')
                    ->label(__('filament/resources.relations.book_detail.fields.pages')),
                TextColumn::make('file_path')
                    ->label(__('filament/resources.relations.book_detail.fields.file'))
                    ->placeholder(__('filament/resources.relations.book_detail.fields.printed')),
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
