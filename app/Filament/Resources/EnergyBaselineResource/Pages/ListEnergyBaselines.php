<?php

namespace App\Filament\Resources\EnergyBaselineResource\Pages;

use App\Filament\Resources\EnergyBaselineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyBaselines extends ListRecords
{
    protected static string $resource = EnergyBaselineResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
