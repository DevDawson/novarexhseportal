<?php

namespace App\Filament\Resources\EsiaRegulatorySubmissionResource\Pages;

use App\Filament\Resources\EsiaRegulatorySubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaRegulatorySubmissions extends ListRecords
{
    protected static string $resource = EsiaRegulatorySubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
