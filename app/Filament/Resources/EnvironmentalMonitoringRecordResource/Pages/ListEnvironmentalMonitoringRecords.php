<?php

namespace App\Filament\Resources\EnvironmentalMonitoringRecordResource\Pages;

use App\Filament\Resources\EnvironmentalMonitoringRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalMonitoringRecords extends ListRecords
{
    protected static string $resource = EnvironmentalMonitoringRecordResource::class;

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
