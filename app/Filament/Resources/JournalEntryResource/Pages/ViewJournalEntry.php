<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Entry Details')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('reference')
                            ->label('Reference')
                            ->weight('bold'),

                        TextEntry::make('entry_date')
                            ->label('Date')
                            ->date('d M Y'),

                        TextEntry::make('source_type')
                            ->label('Source')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => \App\Models\JournalEntry::SOURCE_LABELS[$state] ?? $state)
                            ->color(fn (string $state) => match ($state) {
                                'payroll_approval' => 'info',
                                'payroll_payment' => 'success',
                                'statutory_remittance' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('postedBy.name')
                            ->label('Posted By')
                            ->placeholder('System'),

                        TextEntry::make('description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Journal Lines (DR / CR)')
                    ->schema([
                        RepeatableEntry::make('lines')
                            ->label('')
                            ->schema([
                                TextEntry::make('account.display_name')
                                    ->label('Account')
                                    ->weight('medium'),

                                TextEntry::make('debit')
                                    ->label('Debit (TZS)')
                                    ->money('TZS')
                                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                                TextEntry::make('credit')
                                    ->label('Credit (TZS)')
                                    ->money('TZS')
                                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                                TextEntry::make('description')
                                    ->label('Notes')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),

                        TextEntry::make('total_debit')
                            ->label('Total Debit (TZS)')
                            ->state(fn ($record) => $record->total_debit)
                            ->money('TZS')
                            ->weight('bold'),

                        TextEntry::make('total_credit')
                            ->label('Total Credit (TZS)')
                            ->state(fn ($record) => $record->total_credit)
                            ->money('TZS')
                            ->weight('bold'),

                        TextEntry::make('is_balanced')
                            ->label('Status')
                            ->state(fn ($record) => $record->is_balanced ? '✓ Balanced' : '✗ NOT BALANCED')
                            ->badge()
                            ->color(fn ($record) => $record->is_balanced ? 'success' : 'danger'),
                    ])
                    ->columns(3),
            ]);
    }
}
