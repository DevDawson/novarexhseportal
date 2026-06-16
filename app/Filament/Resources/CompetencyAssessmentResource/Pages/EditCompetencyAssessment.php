<?php

namespace App\Filament\Resources\CompetencyAssessmentResource\Pages;

use App\Filament\Resources\CompetencyAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetencyAssessment extends EditRecord
{
    protected static string $resource = CompetencyAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
