<?php

namespace App\Filament\Resources\PettyCashTransactionResource\Pages;

use App\Filament\Resources\PettyCashTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePettyCashTransaction extends CreateRecord
{
    protected static string $resource = PettyCashTransactionResource::class;
}
