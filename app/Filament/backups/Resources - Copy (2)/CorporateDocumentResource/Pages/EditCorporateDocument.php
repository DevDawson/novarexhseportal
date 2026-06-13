<?php

namespace App\Filament\Resources\CorporateDocumentResource\Pages;

use App\Filament\Resources\CorporateDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCorporateDocument extends EditRecord
{
    protected static string $resource = CorporateDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
