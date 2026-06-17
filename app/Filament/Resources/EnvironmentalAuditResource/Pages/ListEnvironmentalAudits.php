<?php

namespace App\Filament\Resources\EnvironmentalAuditResource\Pages;

use App\Filament\Resources\EnvironmentalAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalAudits extends ListRecords
{
    protected static string $resource = EnvironmentalAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
