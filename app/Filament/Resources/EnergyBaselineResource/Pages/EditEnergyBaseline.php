<?php

namespace App\Filament\Resources\EnergyBaselineResource\Pages;

use App\Filament\Resources\EnergyBaselineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyBaseline extends EditRecord
{
    protected static string $resource = EnergyBaselineResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
