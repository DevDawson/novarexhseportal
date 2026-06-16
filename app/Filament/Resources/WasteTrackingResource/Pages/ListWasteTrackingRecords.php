<?php

namespace App\Filament\Resources\WasteTrackingResource\Pages;

use App\Filament\Resources\WasteTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWasteTrackingRecords extends ListRecords
{
    protected static string $resource = WasteTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
