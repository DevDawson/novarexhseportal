<?php

namespace App\Filament\Resources\EnvironmentalMonitoringRecordResource\Pages;

use App\Filament\Resources\EnvironmentalMonitoringRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvironmentalMonitoringRecord extends EditRecord
{
    protected static string $resource = EnvironmentalMonitoringRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
