<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use App\Services\HseKpiService;
use App\Services\RiskScoringService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OpenCorrectiveActionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Open Corrective Actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hr_director']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Incident::query()
                    ->whereIn('status', ['open', 'investigating'])
                    ->orderBy('incident_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->date('d M Y')
                    ->label('Reported'),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide')
                    ->limit(30),

                Tables\Columns\TextColumn::make('description')
                    ->limit(40),

                Tables\Columns\TextColumn::make('risk_score')
                    ->label('Risk')
                    ->badge()
                    ->formatStateUsing(fn (int $state, Incident $record): string => "{$state}/25 - ".ucfirst($record->risk_level))
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state)),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'investigating',
                    ]),

                Tables\Columns\TextColumn::make('days_open')
                    ->label('Days Open')
                    ->state(fn (Incident $record) => $record->incident_date->diffInDays(now()))
                    ->badge()
                    ->color(fn ($state) => $state > HseKpiService::OVERDUE_THRESHOLD_DAYS ? 'danger' : 'gray'),
            ])
            ->paginated([5, 10, 25]);
    }
}
