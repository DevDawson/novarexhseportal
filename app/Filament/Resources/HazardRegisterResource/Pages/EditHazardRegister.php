<?php

namespace App\Filament\Resources\HazardRegisterResource\Pages;

use App\Filament\Resources\HazardRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazardRegister extends EditRecord
{
    protected static string $resource = HazardRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
