<?php

namespace App\Filament\Resources\LessonsLearnedResource\Pages;

use App\Filament\Resources\LessonsLearnedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLessonsLearned extends EditRecord
{
    protected static string $resource = LessonsLearnedResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
