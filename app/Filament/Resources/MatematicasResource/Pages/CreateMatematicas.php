<?php

namespace App\Filament\Resources\MatematicasResource\Pages;

use App\Filament\Resources\MatematicasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMatematicas extends CreateRecord
{
    protected static string $resource = MatematicasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category'] = 'matematicas';

        return $data;
    }
}
