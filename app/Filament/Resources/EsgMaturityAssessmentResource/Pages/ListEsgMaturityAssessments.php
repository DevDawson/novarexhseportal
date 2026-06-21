<?php

namespace App\Filament\Resources\EsgMaturityAssessmentResource\Pages;

use App\Filament\Resources\EsgMaturityAssessmentResource;
use App\Services\EsgMaturityService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEsgMaturityAssessments extends ListRecords
{
    protected static string $resource = EsgMaturityAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('new_auto')
                ->label('New Auto-Filled Assessment')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->action(function () {
                    $auto    = EsgMaturityService::autoFill();
                    $scores  = [];
                    $sources = [];
                    foreach ($auto as $key => $data) {
                        $scores["{$key}_score"] = $data['score'];
                        $sources[$key]          = $data['source'];
                    }
                    $record = \App\Models\EsgMaturityAssessment::create(array_merge($scores, [
                        'period'          => now()->year,
                        'period_type'     => 'annual',
                        'status'          => 'draft',
                        'assessed_by_id'  => auth()->id(),
                        'auto_sources'    => $sources,
                    ]));
                    EsgMaturityService::recalculate($record);
                    Notification::make()->title("Draft ESG-MI assessment created for " . now()->year . " — review and adjust scores before finalising")->success()->send();
                    $this->redirect(EsgMaturityAssessmentResource::getUrl('edit', ['record' => $record]));
                }),

            Actions\CreateAction::make()->label('New Manual Assessment'),
        ];
    }
}
