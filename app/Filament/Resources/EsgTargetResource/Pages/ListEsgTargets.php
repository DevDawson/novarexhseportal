<?php

namespace App\Filament\Resources\EsgTargetResource\Pages;

use App\Filament\Resources\EsgTargetResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListEsgTargets extends ListRecords
{
    protected static string $resource = EsgTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_esg_pdf')
                ->label('Export ESG Summary PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('pdf.esg.summary'))
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }
}
