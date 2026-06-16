<?php

namespace App\Filament\Resources\LessonsLearnedResource\Pages;

use App\Filament\Resources\LessonsLearnedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessonsLearned extends ListRecords
{
    protected static string $resource = LessonsLearnedResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
