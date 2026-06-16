<?php

namespace App\Filament\Resources\EnergyActionPlanResource\Pages;

use App\Filament\Resources\EnergyActionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyActionPlan extends EditRecord
{
    protected static string $resource = EnergyActionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
