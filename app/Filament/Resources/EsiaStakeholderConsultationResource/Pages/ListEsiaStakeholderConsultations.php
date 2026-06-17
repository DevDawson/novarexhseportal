<?php

namespace App\Filament\Resources\EsiaStakeholderConsultationResource\Pages;

use App\Filament\Resources\EsiaStakeholderConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaStakeholderConsultations extends ListRecords
{
    protected static string $resource = EsiaStakeholderConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
