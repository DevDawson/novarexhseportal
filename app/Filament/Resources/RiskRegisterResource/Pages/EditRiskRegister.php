<?php

namespace App\Filament\Resources\RiskRegisterResource\Pages;

use App\Filament\Resources\RiskRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiskRegister extends EditRecord
{
    protected static string $resource = RiskRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
