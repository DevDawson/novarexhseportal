<?php

namespace App\Filament\Resources\GrievanceResource\Pages;

use App\Filament\Resources\GrievanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrievance extends EditRecord
{
    protected static string $resource = GrievanceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
