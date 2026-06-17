<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaImpactAssessmentResource\Pages;
use App\Models\EsiaImpactAssessment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaImpactAssessmentResource extends Resource
{
    protected static ?string $model = EsiaImpactAssessment::class;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Steps 5 & 6: Impact Matrix';
    protected static ?string $modelLabel = 'Impact Assessment';
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project & Activity')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('activity')
                        ->label('Project Activity')
                        ->placeholder('e.g. Site clearing, Excavation, Vehicle movement')
                        ->required()->maxLength(255),

                    Forms\Components\TextInput::make('receptor')
                        ->label('Environmental / Social Receptor')
                        ->placeholder('e.g. Riparian vegetation, Local residents, Surface water')
                        ->required()->maxLength(255),

                    Forms\Components\Select::make('impact_category')
                        ->label('Impact Category')
                        ->options(EsiaImpactAssessment::IMPACT_CATEGORY_LABELS)
                        ->required()->native(false),

                    Forms\Components\Select::make('phase')
                        ->label('Project Phase')
                        ->options(EsiaImpactAssessment::PHASE_LABELS)
                        ->default('construction')->required()->native(false),

                    Forms\Components\Select::make('nature')
                        ->label('Impact Nature')
                        ->options(['positive' => 'Positive', 'negative' => 'Negative', 'neutral' => 'Neutral'])
                        ->default('negative')->required()->native(false),

                    Forms\Components\Toggle::make('is_direct')
                        ->label('Direct Impact')
                        ->default(true)
                        ->helperText('Caused directly by the project activity.'),

                    Forms\Components\Toggle::make('is_cumulative')
                        ->label('Cumulative Impact')
                        ->default(false)
                        ->helperText('Adds to impacts from other sources.'),

                    Forms\Components\Toggle::make('is_reversible')
                        ->label('Reversible')
                        ->default(true)
                        ->helperText('Environment can recover after impact.'),

                    Forms\Components\Textarea::make('impact_description')
                        ->label('Impact Description')
                        ->rows(3)->required()->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Significance Rating (Severity × Likelihood × Duration × Sensitivity)')
                ->description('Each factor rated 1–5. Max score = 625. Impact Level: High ≥81 (Red), Medium 21–80 (Yellow), Low 1–20 (Green). Detailed: Critical ≥300, Major ≥100, Moderate ≥40, Minor ≥10, Negligible <10.')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('severity')
                        ->label('Severity (1–5)')
                        ->options(EsiaImpactAssessment::RATING_OPTIONS)
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            self::recomputeScore($g, $s)
                        ),

                    Forms\Components\Select::make('likelihood')
                        ->label('Likelihood (1–5)')
                        ->options(EsiaImpactAssessment::RATING_OPTIONS)
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            self::recomputeScore($g, $s)
                        ),

                    Forms\Components\Select::make('duration')
                        ->label('Duration (1–5)')
                        ->options([
                            1 => '1 — Short-term (<1 yr)',
                            2 => '2 — Short-medium (1–3 yr)',
                            3 => '3 — Medium-term (3–10 yr)',
                            4 => '4 — Long-term (>10 yr)',
                            5 => '5 — Permanent',
                        ])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            self::recomputeScore($g, $s)
                        ),

                    Forms\Components\Select::make('sensitivity')
                        ->label('Area Sensitivity (1–5)')
                        ->options([
                            1 => '1 — Low',
                            2 => '2 — Low-Medium',
                            3 => '3 — Medium',
                            4 => '4 — High',
                            5 => '5 — Very High / Protected',
                        ])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            self::recomputeScore($g, $s)
                        ),

                    Forms\Components\Placeholder::make('significance_preview')
                        ->label('Impact Score & Classification')
                        ->content(function (Forms\Get $get): string {
                            $s   = (int)$get('severity')    ?: 1;
                            $l   = (int)$get('likelihood')  ?: 1;
                            $d   = (int)$get('duration')    ?: 1;
                            $sen = (int)$get('sensitivity') ?: 1;
                            $score = $s * $l * $d * $sen;
                            $detail = ucfirst(EsiaImpactAssessment::scoreToLevel($score));
                            $level  = strtoupper(EsiaImpactAssessment::scoreToImpactLevel($score));
                            return "Score: {$score} / 625 — {$level} IMPACT ({$detail})";
                        })
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('significance_score')->default(1),
                    Forms\Components\Hidden::make('impact_level')->default('low'),
                ]),

            Forms\Components\Section::make('Proposed Mitigation & Residual Risk')
                ->collapsed()
                ->columns(4)
                ->schema([
                    Forms\Components\Textarea::make('proposed_mitigation')
                        ->label('Proposed Mitigation Measures')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('residual_severity')
                        ->label('Residual Severity')
                        ->options(EsiaImpactAssessment::RATING_OPTIONS)
                        ->native(false)->nullable(),

                    Forms\Components\Select::make('residual_likelihood')
                        ->label('Residual Likelihood')
                        ->options(EsiaImpactAssessment::RATING_OPTIONS)
                        ->native(false)->nullable(),

                    Forms\Components\Select::make('residual_duration')
                        ->label('Residual Duration')
                        ->options(EsiaImpactAssessment::RATING_OPTIONS)
                        ->native(false)->nullable(),

                    Forms\Components\Select::make('residual_sensitivity')
                        ->label('Residual Sensitivity')
                        ->options(EsiaImpactAssessment::RATING_OPTIONS)
                        ->native(false)->nullable(),
                ]),

            Forms\Components\Section::make('Assessor')
                ->schema([
                    Forms\Components\Select::make('assessed_by')
                        ->label('Assessed By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                ]),
        ]);
    }

    protected static function recomputeScore(Forms\Get $g, Forms\Set $s): void
    {
        $score = ((int)$g('severity') ?: 1)
            * ((int)$g('likelihood') ?: 1)
            * ((int)$g('duration') ?: 1)
            * ((int)$g('sensitivity') ?: 1);
        $s('significance_score', $score);
        $s('impact_level', EsiaImpactAssessment::scoreToImpactLevel($score));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(25),

                Tables\Columns\TextColumn::make('activity')
                    ->label('Activity')->limit(25),

                Tables\Columns\TextColumn::make('receptor')
                    ->label('Receptor')->limit(25),

                Tables\Columns\BadgeColumn::make('nature')
                    ->colors([
                        'danger'  => 'negative',
                        'success' => 'positive',
                        'gray'    => 'neutral',
                    ]),

                Tables\Columns\TextColumn::make('impact_level')
                    ->label('Impact Level')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match($state) {
                        'high'   => 'HIGH',
                        'medium' => 'MEDIUM',
                        'low'    => 'LOW',
                        default  => '—',
                    })
                    ->color(fn (?string $state): string => EsiaImpactAssessment::IMPACT_LEVEL_COLORS[$state] ?? 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('significance_score')
                    ->label('Score')
                    ->badge()
                    ->formatStateUsing(fn ($s) => $s . ' / 625')
                    ->color(fn (int $state): string =>
                        EsiaImpactAssessment::levelColor(EsiaImpactAssessment::scoreToLevel($state))
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('significance_level')
                    ->label('Detail')
                    ->badge()
                    ->formatStateUsing(fn ($s) => ucfirst($s))
                    ->color(fn (string $state): string => EsiaImpactAssessment::levelColor($state)),

                Tables\Columns\TextColumn::make('phase')
                    ->badge()
                    ->formatStateUsing(fn ($s) => EsiaImpactAssessment::PHASE_LABELS[$s] ?? $s)
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('significance_level')
                    ->options(EsiaImpactAssessment::SIGNIFICANCE_LEVEL_LABELS),

                Tables\Filters\SelectFilter::make('nature')
                    ->options(['positive' => 'Positive', 'negative' => 'Negative', 'neutral' => 'Neutral']),

                Tables\Filters\SelectFilter::make('phase')
                    ->options(EsiaImpactAssessment::PHASE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('significance_score', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaImpactAssessments::route('/'),
            'create' => Pages\CreateEsiaImpactAssessment::route('/create'),
            'edit'   => Pages\EditEsiaImpactAssessment::route('/{record}/edit'),
        ];
    }
}
