<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalAspectResource\Pages;
use App\Models\EnvironmentalAspect;
use App\Models\User;
use App\Services\RiskScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnvironmentalAspectResource extends Resource
{
    protected static ?string $model = EnvironmentalAspect::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Environmental Management';

    protected static ?string $navigationLabel = 'Aspects & Impacts';

    protected static ?string $modelLabel = 'Environmental Aspect';

    protected static ?int $navigationSort = 1;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    // ----------------------------------------------------------------
    // Form
    // ----------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Activity & Aspect')
                ->description('Describe the process or activity and the environmental aspect it generates.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / office operations')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('activity_process')
                        ->label('Activity / Process')
                        ->placeholder('e.g. Diesel generator operation')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\Select::make('impact_category')
                        ->label('Impact Category')
                        ->options(EnvironmentalAspect::IMPACT_CATEGORY_LABELS)
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('environmental_aspect')
                        ->label('Environmental Aspect')
                        ->placeholder('e.g. Air emissions (CO2, NOx)')
                        ->maxLength(255)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('environmental_impact')
                        ->label('Environmental Impact')
                        ->placeholder('e.g. Contribution to air pollution and climate change')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Significance Assessment')
                ->description('Rate Likelihood × Severity to determine significance. Score ≥ 10 = Significant (same matrix as HIRA / Incidents).')
                ->columns(2)
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('likelihood')
                            ->label('Likelihood (L)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                $set('significance_score', RiskScoringService::score(
                                    (int) $get('likelihood'), (int) $get('severity')
                                ))
                            ),

                        Forms\Components\Select::make('severity')
                            ->label('Severity (S)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                $set('significance_score', RiskScoringService::score(
                                    (int) $get('likelihood'), (int) $get('severity')
                                ))
                            ),

                        Forms\Components\Placeholder::make('significance_preview')
                            ->label('Significance Score (L × S)')
                            ->content(function (Forms\Get $get) {
                                $score = RiskScoringService::score(
                                    (int) $get('likelihood'),
                                    (int) $get('severity')
                                );
                                $level = RiskScoringService::level($score);
                                $label = $score >= 10 ? 'Significant' : 'Not Significant';

                                return "{$score} / 25 — {$label} (" . ucfirst($level) . ")";
                            }),
                    ])->columnSpanFull(),

                    Forms\Components\Hidden::make('significance_score')->default(0),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EnvironmentalAspect::STATUS_LABELS)
                        ->default('not_significant')
                        ->required()
                        ->native(false)
                        ->helperText('Auto-set from score on save. Set to "Controlled" to override once controls are in place.'),
                ]),

            Forms\Components\Section::make('Controls & References')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('existing_controls')
                        ->label('Existing Controls / Mitigation Measures')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('legal_requirement_ref')
                        ->label('Legal Requirement Reference')
                        ->placeholder('e.g. NEMC Permit No. EIA/2024/001 or Cap 191 s.57')
                        ->maxLength(255),

                    Forms\Components\Select::make('responsible_person_id')
                        ->label('Responsible Person')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Next Review Date')
                        ->native(false),
                ]),
        ]);
    }

    // ----------------------------------------------------------------
    // Table
    // ----------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity_process')
                    ->label('Activity / Process')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('environmental_aspect')
                    ->label('Aspect')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\BadgeColumn::make('impact_category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string =>
                        EnvironmentalAspect::IMPACT_CATEGORY_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'info'    => ['air', 'noise'],
                        'primary' => ['water'],
                        'warning' => ['waste', 'soil'],
                        'success' => ['biodiversity', 'energy'],
                        'gray'    => ['other'],
                    ]),

                Tables\Columns\TextColumn::make('significance_score')
                    ->label('Significance')
                    ->badge()
                    ->formatStateUsing(function (int $state): string {
                        $level = RiskScoringService::level($state);
                        $label = $state >= 10 ? 'Significant' : 'Not Significant';

                        return "{$state}/25 — {$label}";
                    })
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string =>
                        EnvironmentalAspect::STATUS_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'danger'  => 'significant',
                        'success' => 'not_significant',
                        'primary' => 'controlled',
                    ]),

                Tables\Columns\TextColumn::make('review_date')
                    ->label('Review Date')
                    ->date('d M Y')
                    ->color(fn (EnvironmentalAspect $record): string =>
                        ($record->review_date && $record->review_date->isPast() && $record->status !== 'controlled')
                            ? 'danger'
                            : 'gray'
                    )
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('impact_category')
                    ->label('Category')
                    ->options(EnvironmentalAspect::IMPACT_CATEGORY_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(EnvironmentalAspect::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('significant_only')
                    ->label('Significant Aspects Only')
                    ->query(fn (Builder $query) => $query->where('status', 'significant'))
                    ->toggle(),

                Tables\Filters\Filter::make('overdue_review')
                    ->label('Overdue Review')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('review_date')
                        ->where('review_date', '<', now())
                        ->where('status', '!=', 'controlled')
                    )
                    ->toggle(),
            ])
            ->defaultSort('significance_score', 'desc')
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.ems.aspect', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnvironmentalAspects::route('/'),
            'create' => Pages\CreateEnvironmentalAspect::route('/create'),
            'edit'   => Pages\EditEnvironmentalAspect::route('/{record}/edit'),
        ];
    }
}
