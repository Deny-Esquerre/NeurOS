<?php

namespace App\Filament\Resources\CompresionLectoraResource\Pages;

use App\Filament\Resources\CompresionLectoraResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompresionLectora extends CreateRecord
{
    protected static string $resource = CompresionLectoraResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category'] = 'compresion_lectora';

        return $data;
    }
}
