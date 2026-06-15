<?php

namespace App\Filament\Resources\EsiaScopingIssueResource\Pages;

use App\Filament\Resources\EsiaScopingIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEsiaScopingIssue extends EditRecord
{
    protected static string $resource = EsiaScopingIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
