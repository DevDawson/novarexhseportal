<?php

namespace App\Filament\Resources\EsiaMitigationResource\Pages;

use App\Filament\Resources\EsiaMitigationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaMitigation extends EditRecord
{
    protected static string $resource = EsiaMitigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
