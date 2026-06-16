<?php

namespace App\Filament\Resources\HazopStudyResource\Pages;

use App\Filament\Resources\HazopStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazopStudies extends ListRecords
{
    protected static string $resource = HazopStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
