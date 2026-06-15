<?php

namespace App\Filament\Resources\PermitToWorkResource\Pages;

use App\Filament\Resources\PermitToWorkResource;
use App\Models\PermitToWork;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPermitToWork extends ViewRecord
{
    protected static string $resource = PermitToWorkResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Permit Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('permit_number')->label('Permit Number')->weight('bold'),

                        TextEntry::make('permit_type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => PermitToWork::PERMIT_TYPE_LABELS[$state] ?? $state),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => PermitToWork::STATUS_LABELS[$state] ?? $state)
                            ->color(fn (string $state) => match ($state) {
                                'draft' => 'gray',
                                'submitted' => 'info',
                                'approved' => 'primary',
                                'active' => 'success',
                                'suspended', 'expired' => 'warning',
                                'closed' => 'secondary',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('project.title')->label('Project')->placeholder('Company premises'),
                        TextEntry::make('location'),
                        TextEntry::make('description')->columnSpanFull(),

                        TextEntry::make('valid_from')->dateTime('d M Y H:i'),
                        TextEntry::make('valid_to')->dateTime('d M Y H:i')
                            ->color(fn (PermitToWork $record) => $record->is_overdue ? 'danger' : null),

                        TextEntry::make('is_overdue')
                            ->label('Closeout Status')
                            ->state(fn (PermitToWork $record) => $record->is_overdue ? 'OVERDUE FOR CLOSEOUT' : 'OK')
                            ->badge()
                            ->color(fn (PermitToWork $record) => $record->is_overdue ? 'danger' : 'success'),
                    ]),

                Section::make('People')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('requestedBy.name')->label('Permit Holder / Performer'),
                        TextEntry::make('issuedBy.name')->label('Issuer / Authorizer')->placeholder('-'),
                        TextEntry::make('areaAuthority.name')->label('Area Authority')->placeholder('-'),
                    ]),

                Section::make('Hazards & Controls')
                    ->schema([
                        TextEntry::make('hazards_identified')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('precautions_taken')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('ppe_required_labels')
                            ->label('PPE Required')
                            ->badge()
                            ->placeholder('None specified'),
                        TextEntry::make('emergency_procedures')->placeholder('-')->columnSpanFull(),
                    ]),

                Section::make('Isolation')
                    ->visible(fn (PermitToWork $record) => $record->isolation_required)
                    ->schema([
                        TextEntry::make('isolation_details')->label('Isolation / LOTO Details')->placeholder('-'),
                    ]),

                Section::make('Gas Testing')
                    ->visible(fn (PermitToWork $record) => $record->gas_test_required)
                    ->columns(4)
                    ->schema([
                        TextEntry::make('gas_test_results.o2')->label('O₂ %')->placeholder('-'),
                        TextEntry::make('gas_test_results.lel')->label('LEL %')->placeholder('-'),
                        TextEntry::make('gas_test_results.h2s')->label('H₂S (ppm)')->placeholder('-'),
                        TextEntry::make('gas_test_results.co')->label('CO (ppm)')->placeholder('-'),
                        TextEntry::make('gas_test_results.tested_by')->label('Tested By')->placeholder('-'),
                        TextEntry::make('gas_test_results.tested_at')->label('Test Time')->placeholder('-'),
                    ]),

                Section::make('Permit Checklist')
                    ->schema([
                        RepeatableEntry::make('checklistItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('item')->label('')->columnSpan(2),
                                IconEntry::make('is_checked')
                                    ->label('')
                                    ->boolean(),
                                TextEntry::make('remarks')->label('')->placeholder('-'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Extension History')
                    ->visible(fn (PermitToWork $record) => $record->extensions->isNotEmpty())
                    ->schema([
                        RepeatableEntry::make('extensions')
                            ->label('')
                            ->schema([
                                TextEntry::make('previous_valid_to')->label('Previous Valid To')->dateTime('d M Y H:i'),
                                TextEntry::make('extended_to')->label('Extended To')->dateTime('d M Y H:i'),
                                TextEntry::make('reason')->label('Reason'),
                                TextEntry::make('extendedBy.name')->label('Extended By'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Closeout')
                    ->visible(fn (PermitToWork $record) => $record->status === 'closed')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('closeoutBy.name')->label('Closed By'),
                        TextEntry::make('closeout_at')->label('Closeout Date/Time')->dateTime('d M Y H:i'),
                        TextEntry::make('closeout_notes')->label('Notes')->columnSpanFull()->placeholder('-'),
                    ]),
            ]);
    }
}
