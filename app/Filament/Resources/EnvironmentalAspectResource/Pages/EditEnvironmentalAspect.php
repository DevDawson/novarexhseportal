<?php

namespace App\Filament\Resources\EnvironmentalAspectResource\Pages;

use App\Filament\Resources\EnvironmentalAspectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvironmentalAspect extends EditRecord
{
    protected static string $resource = EnvironmentalAspectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
