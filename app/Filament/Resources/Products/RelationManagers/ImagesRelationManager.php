<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Support\Media;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\File;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources.relations.images.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label(__('filament/resources.relations.images.fields.path'))
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/jpg',
                        'image/pjpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                        'application/octet-stream',
                    ])
                    ->rules([
                        File::default()
                            ->extensions(['jpg', 'jpeg', 'png', 'webp', 'gif'])
                            ->max(5 * 1024),
                    ])
                    ->required(),
                TextInput::make('sort')
                    ->label(__('filament/resources.relations.images.fields.sort'))
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('filament/resources.relations.images.model_label'))
            ->pluralModelLabel(__('filament/resources.relations.images.plural_model_label'))
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('filament/resources.relations.images.fields.path'))
                    ->disk('public')
                    ->visibility('public')
                    ->defaultImageUrl(fn (Model $record): string => Media::url($record->getAttribute('path')))
                    ->square()
                    ->imageSize(56)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                TextColumn::make('sort')
                    ->label(__('filament/resources.relations.images.fields.sort'))
                    ->sortable(),
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
