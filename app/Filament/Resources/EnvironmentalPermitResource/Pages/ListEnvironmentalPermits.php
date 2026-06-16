<?php

namespace App\Filament\Resources\EnvironmentalPermitResource\Pages;

use App\Filament\Resources\EnvironmentalPermitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalPermits extends ListRecords
{
    protected static string $resource = EnvironmentalPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
