<?php

namespace App\Filament\Resources\ConsultantInvoiceResource\Pages;

use App\Filament\Resources\ConsultantInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsultantInvoices extends ListRecords
{
    protected static string $resource = ConsultantInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
