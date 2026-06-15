<?php

namespace App\Filament\Resources\EsiaScreeningResource\Pages;

use App\Filament\Resources\EsiaScreeningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaScreening extends EditRecord
{
    protected static string $resource = EsiaScreeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
