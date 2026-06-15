<?php

namespace App\Filament\Resources\EsiaImpactAssessmentResource\Pages;

use App\Filament\Resources\EsiaImpactAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaImpactAssessment extends EditRecord
{
    protected static string $resource = EsiaImpactAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
