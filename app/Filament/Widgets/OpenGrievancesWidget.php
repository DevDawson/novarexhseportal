<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\GrievanceResource;
use App\Models\Grievance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OpenGrievancesWidget extends BaseWidget
{
    protected static ?string $heading = 'Open Grievances';

    protected static ?int $sort = 17;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Grievance::query()
                    ->whereNotIn('status', ['resolved', 'closed'])
                    ->orderByRaw("FIELD(severity, 'high', 'medium', 'low')")
                    ->orderBy('received_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),

                Tables\Columns\TextColumn::make('received_date')
                    ->label('Received')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn ($state) => Grievance::CATEGORY_LABELS[$state] ?? $state)
                    ->colors(['primary' => fn () => true]),

                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'danger'  => 'high',
                        'warning' => 'medium',
                        'success' => 'low',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => Grievance::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => ['under_review', 'action_taken'],
                    ]),

                Tables\Columns\TextColumn::make('target_resolution_date')
                    ->label('Target')
                    ->date()
                    ->color(fn ($record) => $record?->is_overdue ? 'danger' : null),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('View')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn ($record) => GrievanceResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
