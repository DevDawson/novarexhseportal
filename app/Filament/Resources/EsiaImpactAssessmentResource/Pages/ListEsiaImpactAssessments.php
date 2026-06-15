<?php

namespace App\Filament\Resources\EsiaImpactAssessmentResource\Pages;

use App\Filament\Resources\EsiaImpactAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaImpactAssessments extends ListRecords
{
    protected static string $resource = EsiaImpactAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
