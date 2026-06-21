<?php

namespace App\Filament\Resources\JhaAnalysisResource\Pages;

use App\Filament\Resources\JhaAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJhaAnalyses extends ListRecords
{
    protected static string $resource = JhaAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
