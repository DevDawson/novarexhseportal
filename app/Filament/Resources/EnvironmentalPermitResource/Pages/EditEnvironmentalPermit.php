<?php

namespace App\Filament\Resources\EnvironmentalPermitResource\Pages;

use App\Filament\Resources\EnvironmentalPermitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvironmentalPermit extends EditRecord
{
    protected static string $resource = EnvironmentalPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
