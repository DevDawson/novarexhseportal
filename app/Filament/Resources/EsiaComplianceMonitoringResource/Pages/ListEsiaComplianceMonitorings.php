<?php

namespace App\Filament\Resources\EsiaComplianceMonitoringResource\Pages;

use App\Filament\Resources\EsiaComplianceMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaComplianceMonitorings extends ListRecords
{
    protected static string $resource = EsiaComplianceMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
