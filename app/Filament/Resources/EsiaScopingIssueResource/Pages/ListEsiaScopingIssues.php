<?php

namespace App\Filament\Resources\EsiaScopingIssueResource\Pages;

use App\Filament\Resources\EsiaScopingIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEsiaScopingIssues extends ListRecords
{
    protected static string $resource = EsiaScopingIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
