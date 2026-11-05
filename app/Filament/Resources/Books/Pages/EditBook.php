<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Books\Concerns\SyncsBookFormats;
use App\Filament\Resources\Products\RelationManagers\BookDetailRelationManager;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Livewire;

class EditBook extends EditRecord
{
    use SyncsBookFormats;

    protected static string $resource = BookResource::class;

    public int $bookFormatsSyncToken = 0;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        if ($record instanceof Product) {
            $record->loadMissing('bookDetail');

            return $this->fillBookFormats($data, $record);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->extractBookFormats($data);
        $data['category_id'] = Category::query()->where('slug', 'books')->value('id');

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record instanceof Product) {
            $this->syncBookDetailFormats($record);
            // $record->unsetRelation('bookDetail');
            $record->load('bookDetail');
            $this->bookFormatsSyncToken++;
            $this->dispatch('book-formats-synced')->to(BookDetailRelationManager::class);
        }
    }

    public function getRelationManagersContentComponent(): Component
    {
        $component = parent::getRelationManagersContentComponent();

        if ($component instanceof Livewire) {
            $component->key(BookDetailRelationManager::class.'-'.$this->bookFormatsSyncToken);
        }

        return $component;
    }
}
