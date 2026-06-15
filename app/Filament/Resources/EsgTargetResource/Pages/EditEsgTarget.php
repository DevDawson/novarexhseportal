<?php

namespace App\Filament\Resources\EsgTargetResource\Pages;

use App\Filament\Resources\EsgTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsgTarget extends EditRecord
{
    protected static string $resource = EsgTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
