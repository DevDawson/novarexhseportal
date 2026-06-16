<?php

namespace App\Filament\Resources\EnergyPerformanceIndicatorResource\Pages;

use App\Filament\Resources\EnergyPerformanceIndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyPerformanceIndicator extends EditRecord
{
    protected static string $resource = EnergyPerformanceIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
