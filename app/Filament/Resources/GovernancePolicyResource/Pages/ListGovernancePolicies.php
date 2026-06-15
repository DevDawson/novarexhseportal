<?php

namespace App\Filament\Resources\GovernancePolicyResource\Pages;

use App\Filament\Resources\GovernancePolicyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGovernancePolicies extends ListRecords
{
    protected static string $resource = GovernancePolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
