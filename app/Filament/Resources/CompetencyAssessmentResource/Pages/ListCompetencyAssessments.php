<?php

namespace App\Filament\Resources\CompetencyAssessmentResource\Pages;

use App\Filament\Resources\CompetencyAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetencyAssessments extends ListRecords
{
    protected static string $resource = CompetencyAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
