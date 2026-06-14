<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Resources\Pages\EditRecord;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->visible(fn () => $this->record->source_type === 'manual'),
        ];
    }

    /**
     * Validate that the entry remains balanced before saving edits.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $lines = $data['lines'] ?? [];

        $totalDebit = collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            \Filament\Notifications\Notification::make()
                ->title('Entry not balanced')
                ->body('Total Debit (TZS '.number_format($totalDebit, 2).') must equal Total Credit (TZS '.number_format($totalCredit, 2).').')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
