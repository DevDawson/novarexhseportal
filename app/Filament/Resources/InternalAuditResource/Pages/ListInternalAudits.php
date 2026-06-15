<?php

namespace App\Filament\Resources\InternalAuditResource\Pages;

use App\Filament\Resources\InternalAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternalAudits extends ListRecords
{
    protected static string $resource = InternalAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
