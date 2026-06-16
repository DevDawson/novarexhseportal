<?php

namespace App\Filament\Resources\SpillReportResource\Pages;

use App\Filament\Resources\SpillReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpillReport extends EditRecord
{
    protected static string $resource = SpillReportResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
