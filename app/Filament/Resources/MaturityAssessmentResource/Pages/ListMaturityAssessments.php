<?php

namespace App\Filament\Resources\MaturityAssessmentResource\Pages;

use App\Filament\Resources\MaturityAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaturityAssessments extends ListRecords
{
    protected static string $resource = MaturityAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
