<?php

namespace App\Filament\Resources\CapaResource\Pages;

use App\Filament\Resources\CapaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCapaActions extends ListRecords
{
    protected static string $resource = CapaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
