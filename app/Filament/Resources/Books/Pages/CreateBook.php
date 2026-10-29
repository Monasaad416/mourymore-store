<?php

namespace App\Filament\Resources\Books\Pages;

use App\Enums\ProductKind;
use App\Filament\Resources\Books\BookResource;
use App\Models\Category;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kind'] = ProductKind::Book->value;
        $data['category_id'] = Category::query()->where('slug', 'books')->value('id');

        return $data;
    }
}
