<?php

namespace App\Filament\Resources\EthicsIncidentResource\Pages;

use App\Filament\Resources\EthicsIncidentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEthicsIncidents extends ListRecords
{
    protected static string $resource = EthicsIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
