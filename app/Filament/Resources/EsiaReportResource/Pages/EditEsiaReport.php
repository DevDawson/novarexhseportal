<?php

namespace App\Filament\Resources\EsiaReportResource\Pages;

use App\Filament\Resources\EsiaReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaReport extends EditRecord
{
    protected static string $resource = EsiaReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
