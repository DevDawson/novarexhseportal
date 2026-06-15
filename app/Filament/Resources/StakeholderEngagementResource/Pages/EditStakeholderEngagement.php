<?php

namespace App\Filament\Resources\StakeholderEngagementResource\Pages;

use App\Filament\Resources\StakeholderEngagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStakeholderEngagement extends EditRecord
{
    protected static string $resource = StakeholderEngagementResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
