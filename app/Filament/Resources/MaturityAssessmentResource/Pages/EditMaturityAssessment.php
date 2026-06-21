<?php

namespace App\Filament\Resources\MaturityAssessmentResource\Pages;

use App\Filament\Resources\MaturityAssessmentResource;
use App\Models\MaturityScore;
use App\Services\MaturityScoringService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaturityAssessment extends EditRecord
{
    protected static string $resource = MaturityAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->saveScores();
    }

    private function saveScores(): void
    {
        $formData = $this->data;
        $scores   = $formData['scores'] ?? [];

        foreach ($scores as $indicatorId => $scoreData) {
            if (!is_array($scoreData) || empty($scoreData['score'])) {
                continue;
            }

            MaturityScore::updateOrCreate(
                [
                    'assessment_id' => $this->record->id,
                    'indicator_id'  => $indicatorId,
                ],
                [
                    'score'          => (int) $scoreData['score'],
                    'evidence'       => $scoreData['evidence'] ?? null,
                    'auto_calculated'=> false,
                ]
            );
        }

        MaturityScoringService::recalculate($this->record);
    }
}
