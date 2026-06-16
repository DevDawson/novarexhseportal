<?php

namespace App\Filament\Resources\EnergyPerformanceIndicatorResource\Pages;

use App\Filament\Resources\EnergyPerformanceIndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyPerformanceIndicators extends ListRecords
{
    protected static string $resource = EnergyPerformanceIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
