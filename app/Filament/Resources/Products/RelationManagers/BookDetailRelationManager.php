<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\BookDetail;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use RuntimeException;

class BookDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'bookDetail';

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources.relations.book_detail.title');
    }

    #[On('book-formats-synced')]
    public function refreshSyncedFormats(): void
    {
        $this->ownerRecord->unsetRelation('bookDetail');
        $this->resetTable();
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('filament/resources.relations.book_detail.model_label'))
            ->pluralModelLabel(__('filament/resources.relations.book_detail.plural_model_label'))
            ->description(__('filament/resources.relations.book_detail.formats_synced_help'))
            ->columns([
                TextColumn::make('author')
                    ->label(__('filament/resources.relations.book_detail.fields.author')),
                TextColumn::make('pages')
                    ->label(__('filament/resources.relations.book_detail.fields.pages')),
                IconColumn::make('offers_print')
                    ->label(__('filament/resources.relations.book_detail.fields.offers_print'))
                    ->boolean(),
                IconColumn::make('offers_digital')
                    ->label(__('filament/resources.relations.book_detail.fields.offers_digital'))
                    ->boolean(),
                TextColumn::make('digital_price')
                    ->label(__('filament/resources.relations.book_detail.fields.digital_price'))
                    ->placeholder('—'),
                TextColumn::make('file_path')
                    ->label(__('filament/resources.relations.book_detail.fields.file'))
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->visible(function (): bool {
                        $owner = $this->getOwnerRecord();

                        return $owner instanceof Product && ! $owner->bookDetail()->exists();
                    })
                    ->using(function (array $data): BookDetail {
                        $owner = $this->getOwnerRecord();

                        if (! $owner instanceof Product) {
                            throw new RuntimeException('Book details can only be saved on a product.');
                        }

                        $detail = $owner->bookDetail()->first();

                        if ($detail instanceof BookDetail) {
                            $detail->fill($data)->save();

                            return $detail;
                        }

                        return $owner->bookDetail()->create($data);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
