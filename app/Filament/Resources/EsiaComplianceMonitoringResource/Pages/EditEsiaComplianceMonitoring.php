<?php

namespace App\Filament\Resources\EsiaComplianceMonitoringResource\Pages;

use App\Filament\Resources\EsiaComplianceMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaComplianceMonitoring extends EditRecord
{
    protected static string $resource = EsiaComplianceMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
