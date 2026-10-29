<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Enums\ProductKind;
use App\Enums\ProductType;
use App\Filament\Resources\Courses\CourseResource;
use App\Models\Category;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kind'] = ProductKind::Course->value;
        $data['type'] = ProductType::Digital->value;
        $data['category_id'] = Category::query()->where('slug', 'courses')->value('id');

        return $data;
    }
}
