<?php

namespace App\Filament\Resources\EnergyActionPlanResource\Pages;

use App\Filament\Resources\EnergyActionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyActionPlans extends ListRecords
{
    protected static string $resource = EnergyActionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
