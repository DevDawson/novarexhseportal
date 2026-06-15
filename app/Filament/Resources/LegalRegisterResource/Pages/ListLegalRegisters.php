<?php

namespace App\Filament\Resources\LegalRegisterResource\Pages;

use App\Filament\Resources\LegalRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegalRegisters extends ListRecords
{
    protected static string $resource = LegalRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
