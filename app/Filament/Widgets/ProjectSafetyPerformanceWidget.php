<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Services\RiskScoringService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ProjectSafetyPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Project Safety Performance';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    /**
     * NOTE: The HSE specification asks for "Department Performance", but
     * Incidents/Risks are recorded against Projects (not directly against
     * Departments) in this schema. This widget reports per-Project safety
     * performance as the closest available breakdown. If department-level
     * reporting is required, projects would need a department_id column,
     * or incidents would need to record the involved staff member's
     * department directly.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Project::query()
                    ->withCount(['incidents', 'risks'])
                    ->withCount(['incidents as open_incidents_count' => function (Builder $query) {
                        $query->whereIn('status', ['open', 'investigating']);
                    }])
                    ->withAvg('incidents', 'risk_score')
                    ->withAvg('risks', 'risk_rating')
                    ->where(function (Builder $query) {
                        $query->whereHas('incidents')->orWhereHas('risks');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('project_code')
                    ->label('Project'),

                Tables\Columns\TextColumn::make('title')
                    ->limit(35),

                Tables\Columns\TextColumn::make('incidents_count')
                    ->label('Incidents')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('open_incidents_count')
                    ->label('Open')
                    ->alignCenter()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('risks_count')
                    ->label('Risks')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('incidents_avg_risk_score')
                    ->label('Avg Incident Risk')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 1).'/25' : '-')
                    ->badge()
                    ->color(fn ($state) => $state !== null ? RiskScoringService::colorForScore((int) round($state)) : 'gray'),

                Tables\Columns\TextColumn::make('risks_avg_risk_rating')
                    ->label('Avg Risk Register Score')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 1).'/25' : '-')
                    ->badge()
                    ->color(fn ($state) => $state !== null ? RiskScoringService::colorForScore((int) round($state)) : 'gray'),
            ])
            ->defaultSort('incidents_avg_risk_score', 'desc')
            ->paginated([5, 10, 25]);
    }
}
