<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InternalAuditResource;
use App\Models\AuditFinding;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OpenAuditFindingsWidget extends BaseWidget
{
    protected static ?string $heading = 'Open Nonconformities (Audit Findings)';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 11;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditFinding::query()
                    ->whereIn('finding_type', ['minor_nonconformity', 'major_nonconformity'])
                    ->whereNotIn('status', ['closed', 'verified'])
                    ->orderBy('finding_type') // major first (alphabetically earlier)
                    ->orderBy('target_date')
            )
            ->columns([
                Tables\Columns\BadgeColumn::make('finding_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => AuditFinding::FINDING_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'major_nonconformity',
                        'warning' => 'minor_nonconformity',
                    ]),

                Tables\Columns\TextColumn::make('internalAudit.audit_reference')
                    ->label('Audit Ref.')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('clause_reference')
                    ->label('Clause')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(55)
                    ->searchable(),

                Tables\Columns\TextColumn::make('responsiblePerson.name')
                    ->label('Responsible')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target Date')
                    ->date('d M Y')
                    ->color(fn (AuditFinding $record): string =>
                        ($record->target_date && $record->target_date->isPast())
                            ? 'danger'
                            : 'gray'
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => AuditFinding::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => 'action_planned',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open_audit')
                    ->label('Open Audit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (AuditFinding $record) => InternalAuditResource::getUrl('edit', [
                        'record' => $record->internal_audit_id,
                    ])),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
