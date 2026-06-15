<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HazardRegisterResource;
use App\Models\HazardRegister;
use App\Services\RiskScoringService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HighRiskHazardsWidget extends BaseWidget
{
    protected static ?string $heading = 'High / Critical Residual Hazards';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HazardRegister::query()
                    ->where('residual_risk_score', '>=', 10)
                    ->where('status', '!=', 'closed')
                    ->orderByDesc('residual_risk_score')
            )
            ->columns([
                Tables\Columns\TextColumn::make('activity_task')
                    ->label('Activity / Task')
                    ->limit(45)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide'),

                Tables\Columns\BadgeColumn::make('hazard_category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => HazardRegister::HAZARD_CATEGORY_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => ['physical', 'mechanical', 'electrical'],
                        'warning' => ['chemical', 'biological'],
                        'info'    => ['ergonomic', 'psychosocial'],
                        'success' => ['environmental'],
                    ]),

                Tables\Columns\TextColumn::make('residual_risk_score')
                    ->label('Residual Risk')
                    ->badge()
                    ->formatStateUsing(function (int $state): string {
                        $level = RiskScoringService::level($state);

                        return "{$state}/25 — " . ucfirst($level);
                    })
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('responsiblePerson.name')
                    ->label('Responsible')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('review_date')
                    ->label('Review Date')
                    ->date('d M Y')
                    ->color(fn (HazardRegister $record): string =>
                        ($record->review_date && $record->review_date->isPast())
                            ? 'danger'
                            : 'gray'
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => HazardRegister::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => 'controls_in_progress',
                        'primary' => 'controlled',
                        'success' => 'closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (HazardRegister $record) => HazardRegisterResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
