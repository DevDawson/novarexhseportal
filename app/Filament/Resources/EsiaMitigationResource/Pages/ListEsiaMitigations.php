<?php

namespace App\Filament\Resources\EsiaMitigationResource\Pages;

use App\Filament\Resources\EsiaMitigationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaMitigations extends ListRecords
{
    protected static string $resource = EsiaMitigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
