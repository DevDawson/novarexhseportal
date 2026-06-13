<?php

namespace App\Filament\Resources\FieldExpenseResource\Pages;

use App\Filament\Resources\FieldExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFieldExpenses extends ListRecords
{
    protected static string $resource = FieldExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
