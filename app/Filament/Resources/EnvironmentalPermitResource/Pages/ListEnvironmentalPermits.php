<?php

namespace App\Filament\Resources\EnvironmentalPermitResource\Pages;

use App\Filament\Resources\EnvironmentalPermitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalPermits extends ListRecords
{
    protected static string $resource = EnvironmentalPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_ems_pdf')
                ->label('Full EMS Report (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => route('pdf.ems.full'))
                ->openUrlInNewTab(),
            Actions\Action::make('export_ems_docx')
                ->label('Full EMS Report (DOCX)')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => route('docx.ems.full'))
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }
}
