<?php

namespace App\Filament\Resources\SocialIndicatorResource\Pages;

use App\Filament\Resources\SocialIndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialIndicator extends EditRecord
{
    protected static string $resource = SocialIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
