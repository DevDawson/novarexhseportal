<?php

namespace App\Filament\Resources\PermitToWorkResource\Pages;

use App\Filament\Resources\PermitToWorkResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPermitToWork extends EditRecord
{
    protected static string $resource = PermitToWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('extendPermit')
                ->label('Extend Permit')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->visible(fn () => in_array($this->record->status, ['approved', 'active']))
                ->form([
                    Forms\Components\DateTimePicker::make('extended_to')
                        ->label('New Valid To')
                        ->native(false)
                        ->seconds(false)
                        ->default(fn () => $this->record->valid_to?->addHours(4))
                        ->after(fn () => $this->record->valid_to)
                        ->required(),

                    Forms\Components\Textarea::make('reason')
                        ->label('Reason for Extension')
                        ->rows(2)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->extensions()->create([
                        'previous_valid_to' => $this->record->valid_to,
                        'extended_to' => $data['extended_to'],
                        'reason' => $data['reason'],
                        'extended_by' => auth()->id(),
                    ]);

                    $this->record->update(['valid_to' => $data['extended_to']]);

                    Notification::make()
                        ->title('Permit extended')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
