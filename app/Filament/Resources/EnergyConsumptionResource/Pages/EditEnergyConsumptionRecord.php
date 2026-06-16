<?php

namespace App\Filament\Resources\EnergyConsumptionResource\Pages;

use App\Filament\Resources\EnergyConsumptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyConsumptionRecord extends EditRecord
{
    protected static string $resource = EnergyConsumptionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
