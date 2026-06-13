<?php

namespace App\Filament\Resources\PettyCashTransactionResource\Pages;

use App\Filament\Resources\PettyCashTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPettyCashTransactions extends ListRecords
{
    protected static string $resource = PettyCashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
