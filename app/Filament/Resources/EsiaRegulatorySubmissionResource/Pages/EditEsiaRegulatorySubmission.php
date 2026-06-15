<?php

namespace App\Filament\Resources\EsiaRegulatorySubmissionResource\Pages;

use App\Filament\Resources\EsiaRegulatorySubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaRegulatorySubmission extends EditRecord
{
    protected static string $resource = EsiaRegulatorySubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
