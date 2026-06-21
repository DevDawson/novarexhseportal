<?php

namespace App\Filament\Resources\EmsImprovementActionResource\Pages;

use App\Filament\Resources\EmsImprovementActionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmsImprovementActions extends ListRecords
{
    protected static string $resource = EmsImprovementActionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
