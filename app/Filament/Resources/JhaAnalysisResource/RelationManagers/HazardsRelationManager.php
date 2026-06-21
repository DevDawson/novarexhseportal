<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use App\Models\JhaTask;
use App\Services\HazopScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HazardsRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'Hazard Identification & Risk Ranking (Steps 6–8)';

    // We override to flatten hazards across all tasks
    public function isReadOnly(): bool { return false; }

    private static function ratingOptions(array $labels): array
    {
        return collect($labels)->mapWithKeys(fn ($label, $key) => [$key => "{$key} — {$label}"])->toArray();
    }

    private static function likelihoodOptions(): array
    {
        return [
            1 => '1 — Rare',
            2 => '2 — Unlikely',
            3 => '3 — Possible',
            4 => '4 — Likely',
            5 => '5 — Almost Certain',
        ];
    }

    private static function severityOptions(): array
    {
        return [
            1 => '1 — Insignificant',
            2 => '2 — Minor Injury',
            3 => '3 — Moderate Injury',
            4 => '4 — Major Injury',
            5 => '5 — Fatality',
        ];
    }

    private static function exposureOptions(): array
    {
        return [
            1 => '1 — Rare Exposure',
            2 => '2 — Monthly',
            3 => '3 — Weekly',
            4 => '4 — Daily',
            5 => '5 — Continuous',
        ];
    }

    public function form(Form $form): Form
    {
        $jhaId = $this->ownerRecord->id;

        return $form->schema([
            Forms\Components\Select::make('jha_task_id')
                ->label('Job Step / Task')
                ->options(
                    JhaTask::where('jha_analysis_id', $jhaId)
                        ->orderBy('step_number')
                        ->get()
                        ->mapWithKeys(fn ($t) => [$t->id => "Step {$t->step_number}: {$t->task_description}"])
                )
                ->required()
                ->native(false),

            Forms\Components\Select::make('hazard_type')
                ->label('Hazard Type')
                ->options(\App\Models\JhaHazard::$hazardTypes)
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('hazard_description')
                ->label('Hazard Description')
                ->required()
                ->columnSpanFull(),

            // ── Step 6: Initial Risk ─────────────────────────────────
            Forms\Components\Section::make('Initial Risk Assessment (L × S × E)')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('initial_likelihood')
                        ->label('Likelihood (L)')
                        ->options(self::likelihoodOptions())
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('initial_risk_score',
                                HazopScoringService::initialScore(
                                    (int)$get('initial_likelihood'),
                                    (int)$get('initial_severity'),
                                    (int)$get('initial_exposure'),
                                )
                            )
                        ),

                    Forms\Components\Select::make('initial_severity')
                        ->label('Severity (S)')
                        ->options(self::severityOptions())
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('initial_risk_score',
                                HazopScoringService::initialScore(
                                    (int)$get('initial_likelihood'),
                                    (int)$get('initial_severity'),
                                    (int)$get('initial_exposure'),
                                )
                            )
                        ),

                    Forms\Components\Select::make('initial_exposure')
                        ->label('Exposure (E)')
                        ->options(self::exposureOptions())
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('initial_risk_score',
                                HazopScoringService::initialScore(
                                    (int)$get('initial_likelihood'),
                                    (int)$get('initial_severity'),
                                    (int)$get('initial_exposure'),
                                )
                            )
                        ),

                    Forms\Components\TextInput::make('initial_risk_score')
                        ->label('Risk Score (L×S×E)')
                        ->disabled()->dehydrated()
                        ->default(1)
                        ->suffix('/ 125'),
                ]),

            // ── Step 8: Residual Risk ────────────────────────────────
            Forms\Components\Section::make('Residual Risk (After Controls — Step 8)')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('residual_likelihood')
                        ->label('Residual Likelihood (L)')
                        ->options(self::likelihoodOptions())
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('residual_risk_score',
                                HazopScoringService::initialScore(
                                    (int)$get('residual_likelihood'),
                                    (int)$get('residual_severity'),
                                    (int)$get('residual_exposure'),
                                )
                            )
                        ),

                    Forms\Components\Select::make('residual_severity')
                        ->label('Residual Severity (S)')
                        ->options(self::severityOptions())
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('residual_risk_score',
                                HazopScoringService::initialScore(
                                    (int)$get('residual_likelihood'),
                                    (int)$get('residual_severity'),
                                    (int)$get('residual_exposure'),
                                )
                            )
                        ),

                    Forms\Components\Select::make('residual_exposure')
                        ->label('Residual Exposure (E)')
                        ->options(self::exposureOptions())
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('residual_risk_score',
                                HazopScoringService::initialScore(
                                    (int)$get('residual_likelihood'),
                                    (int)$get('residual_severity'),
                                    (int)$get('residual_exposure'),
                                )
                            )
                        ),

                    Forms\Components\TextInput::make('residual_risk_score')
                        ->label('Residual Risk Score')
                        ->disabled()->dehydrated()
                        ->default(1)
                        ->suffix('/ 125')
                        ->helperText('Must be < Initial Risk Score for JHA acceptance'),
                ]),

            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        // Show hazards from ALL tasks belonging to this JHA
        return $table
            ->query(
                \App\Models\JhaHazard::query()
                    ->whereHas('task', fn ($q) => $q->where('jha_analysis_id', $this->ownerRecord->id))
            )
            ->columns([
                Tables\Columns\TextColumn::make('task.step_number')
                    ->label('Step')
                    ->width(50),

                Tables\Columns\TextColumn::make('task.task_description')
                    ->label('Task')
                    ->limit(25),

                Tables\Columns\TextColumn::make('hazard_type')
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('hazard_description')
                    ->label('Hazard')
                    ->limit(35),

                Tables\Columns\TextColumn::make('initial_risk_score')
                    ->label('Initial Risk')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 81 => 'danger',
                        $state >= 51 => 'warning',
                        $state >= 21 => 'info',
                        default      => 'success',
                    }),

                Tables\Columns\TextColumn::make('initial_risk_level')
                    ->label('Level')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'critical' => 'danger',
                        'high'     => 'warning',
                        'medium'   => 'info',
                        default    => 'success',
                    }),

                Tables\Columns\TextColumn::make('residual_risk_score')
                    ->label('Residual Risk')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 81 => 'danger',
                        $state >= 51 => 'warning',
                        $state >= 21 => 'info',
                        default      => 'success',
                    }),

                Tables\Columns\IconColumn::make('residual_accepted')
                    ->label('Accepted?')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
