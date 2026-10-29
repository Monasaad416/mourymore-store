<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Enums\ProductType;
use App\Filament\Resources\Courses\CourseResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

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
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = ProductType::Digital->value;
        $data['category_id'] = Category::query()->where('slug', 'courses')->value('id');

        return $data;
    }
}
