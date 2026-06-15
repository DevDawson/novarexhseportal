<?php

namespace App\Filament\Resources\InternalAuditResource\Pages;

use App\Filament\Resources\InternalAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalAudit extends EditRecord
{
    protected static string $resource = InternalAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
