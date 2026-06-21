<?php

namespace App\Filament\Resources\EnvironmentalAuditResource\Pages;

use App\Filament\Resources\EnvironmentalAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvironmentalAudit extends EditRecord
{
    protected static string $resource = EnvironmentalAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => route('pdf.env.audit', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('export_docx')
                ->label('Export DOCX')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => route('docx.env.audit', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
