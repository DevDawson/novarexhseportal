<?php

namespace App\Filament\Resources\PermitToWorkResource\Pages;

use App\Filament\Resources\PermitToWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermitToWorks extends ListRecords
{
    protected static string $resource = PermitToWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Request New Permit'),
        ];
    }
}
