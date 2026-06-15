<?php

namespace App\Filament\Resources\EsiaReportResource\Pages;

use App\Filament\Resources\EsiaReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaReports extends ListRecords
{
    protected static string $resource = EsiaReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
