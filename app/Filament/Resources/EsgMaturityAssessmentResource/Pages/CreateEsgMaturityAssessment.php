<?php

namespace App\Filament\Resources\EsgMaturityAssessmentResource\Pages;

use App\Filament\Resources\EsgMaturityAssessmentResource;
use App\Services\EsgMaturityService;
use Filament\Resources\Pages\CreateRecord;

class CreateEsgMaturityAssessment extends CreateRecord
{
    protected static string $resource = EsgMaturityAssessmentResource::class;

    protected function afterCreate(): void
    {
        EsgMaturityService::recalculate($this->record);
    }
}
