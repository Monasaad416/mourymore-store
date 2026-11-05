<?php

namespace App\Filament\Resources\Books\Pages;

use App\Enums\ProductKind;
use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Books\Concerns\SyncsBookFormats;
use App\Models\Category;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    use SyncsBookFormats;

    protected static string $resource = BookResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->extractBookFormats($data);
        $data['kind'] = ProductKind::Book->value;
        $data['category_id'] = Category::query()->where('slug', 'books')->value('id');

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if ($record instanceof Product) {
            $this->syncBookDetailFormats($record);
        }
    }
}
