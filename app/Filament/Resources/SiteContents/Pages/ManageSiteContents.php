<?php

namespace App\Filament\Resources\SiteContents\Pages;

use App\Filament\Resources\SiteContents\SiteContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSiteContents extends ManageRecords
{
    protected static string $resource = SiteContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    if (blank($data['group'] ?? null) && filled($data['key'] ?? null)) {
                        $data['group'] = explode('.', (string) $data['key'], 2)[0];
                    }

                    return $data;
                }),
        ];
    }
}
