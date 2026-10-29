<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductKind;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kind'] = ProductKind::Product->value;

        return $data;
    }
}
