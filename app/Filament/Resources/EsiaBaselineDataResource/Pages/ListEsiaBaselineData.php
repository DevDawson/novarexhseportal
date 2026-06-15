<?php

namespace App\Filament\Resources\EsiaBaselineDataResource\Pages;

use App\Filament\Resources\EsiaBaselineDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaBaselineData extends ListRecords
{
    protected static string $resource = EsiaBaselineDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
