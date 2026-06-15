<?php

namespace App\Filament\Resources\SocialIndicatorResource\Pages;

use App\Filament\Resources\SocialIndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocialIndicators extends ListRecords
{
    protected static string $resource = SocialIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
