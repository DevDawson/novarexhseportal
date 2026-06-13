<?php

namespace App\Filament\Resources\FieldExpenseResource\Pages;

use App\Filament\Resources\FieldExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFieldExpense extends EditRecord
{
    protected static string $resource = FieldExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
