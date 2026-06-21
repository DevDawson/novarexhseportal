<?php

namespace App\Filament\Resources\EsgMaturityAssessmentResource\Pages;

use App\Filament\Resources\EsgMaturityAssessmentResource;
use App\Services\EsgMaturityService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsgMaturityAssessment extends EditRecord
{
    protected static string $resource = EsgMaturityAssessmentResource::class;

    protected function afterSave(): void
    {
        EsgMaturityService::recalculate($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
