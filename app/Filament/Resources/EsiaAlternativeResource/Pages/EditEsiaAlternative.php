<?php

namespace App\Filament\Resources\EsiaAlternativeResource\Pages;

use App\Filament\Resources\EsiaAlternativeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaAlternative extends EditRecord
{
    protected static string $resource = EsiaAlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
