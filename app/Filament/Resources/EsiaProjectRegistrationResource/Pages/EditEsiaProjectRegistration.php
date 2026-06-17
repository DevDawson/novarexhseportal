<?php

namespace App\Filament\Resources\EsiaProjectRegistrationResource\Pages;

use App\Filament\Resources\EsiaProjectRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaProjectRegistration extends EditRecord
{
    protected static string $resource = EsiaProjectRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
