<?php

namespace App\Filament\Resources\WasteTrackingResource\Pages;

use App\Filament\Resources\WasteTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWasteTrackingRecord extends EditRecord
{
    protected static string $resource = WasteTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
