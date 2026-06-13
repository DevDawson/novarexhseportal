<?php

namespace App\Filament\Resources\CorporateDocumentResource\Pages;

use App\Filament\Resources\CorporateDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCorporateDocuments extends ListRecords
{
    protected static string $resource = CorporateDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
