<?php

namespace App\Filament\Resources\HazopStudyResource\Pages;

use App\Filament\Resources\HazopStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazopStudy extends EditRecord
{
    protected static string $resource = HazopStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
