<?php

namespace App\Filament\Resources\EsiaProjectRegistrationResource\Pages;

use App\Filament\Resources\EsiaProjectRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaProjectRegistrations extends ListRecords
{
    protected static string $resource = EsiaProjectRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
