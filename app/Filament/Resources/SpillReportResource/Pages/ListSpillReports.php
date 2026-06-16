<?php

namespace App\Filament\Resources\SpillReportResource\Pages;

use App\Filament\Resources\SpillReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpillReports extends ListRecords
{
    protected static string $resource = SpillReportResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
