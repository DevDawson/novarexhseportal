<?php

namespace App\Filament\Resources\EnvironmentalMonitoringRecordResource\Pages;

use App\Filament\Resources\EnvironmentalMonitoringRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalMonitoringRecords extends ListRecords
{
    protected static string $resource = EnvironmentalMonitoringRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
