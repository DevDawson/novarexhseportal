<?php

namespace App\Filament\Resources\HazopStudyResource\RelationManagers;

use App\Models\Department;
use App\Models\HazopNode;
use App\Models\User;
use App\Services\HazopScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NodesRelationManager extends RelationManager
{
    protected static string $relationship = 'nodes';

    protected static ?string $recordTitleAttribute = 'deviation';

    protected static ?string $title = 'HAZOP Worksheet — Nodes & Deviations';

    // ----------------------------------------------------------------
    // Form — full quantitative HAZOP worksheet
    // ----------------------------------------------------------------

    public function form(Form $form): Form
    {
        return $form->schema([

            // ---- Section 1: Node Identification -------------------------
            Forms\Components\Section::make('1. Node Identification')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('node_number')
                        ->label('Node No.')
                        ->numeric()
                        ->minValue(1)
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(HazopNode::STATUS_LABELS)
                        ->default('open')
                        ->required()
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('node_name')
                        ->label('Node / Process Area')
                        ->placeholder('e.g. High-pressure gas separator inlet')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('parameter')
                        ->label('Parameter')
                        ->options(HazopScoringService::parameterOptions())
                        ->searchable()
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('guide_word')
                        ->label('Guide Word')
                        ->options(HazopScoringService::guideWordOptions())
                        ->searchable()
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('deviation')
                        ->label('Deviation')
                        ->helperText('What happens when the guide word is applied to the parameter? (e.g. "MORE OF Flow → high flow rate")')
                        ->rows(2)
                        ->required()
                        ->columnSpanFull(),
                ]),

            // ---- Section 2: Cause & Consequence -------------------------
            Forms\Components\Section::make('2. Cause & Consequence Analysis')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('cause')
                        ->label('Cause')
                        ->helperText('What could cause this deviation?')
                        ->rows(3)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('consequence')
                        ->label('Consequence')
                        ->helperText('What could be the impact on safety, environment, or assets?')
                        ->rows(3)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('existing_safeguards')
                        ->label('Existing Safeguards')
                        ->helperText('What protective systems, procedures, or controls are already in place?')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // ---- Section 3: Initial Risk Assessment (L×S×E) ------------
            Forms\Components\Section::make('3. Initial Risk Assessment — L × S × E')
                ->description('Rate the risk BEFORE additional controls. Score = Likelihood × Severity × Exposure (range 1–125).')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Select::make('likelihood')
                            ->label('Likelihood (L)')
                            ->options(HazopScoringService::likelihoodOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                self::recalcInitial($get, $set)
                            ),

                        Forms\Components\Select::make('severity')
                            ->label('Severity (S)')
                            ->options(HazopScoringService::severityOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                self::recalcInitial($get, $set)
                            ),

                        Forms\Components\Select::make('exposure')
                            ->label('Exposure (E)')
                            ->options(HazopScoringService::exposureOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                self::recalcInitial($get, $set)
                            ),

                        Forms\Components\Placeholder::make('initial_risk_preview')
                            ->label('Initial Risk Score (L×S×E)')
                            ->content(function (Forms\Get $get): string {
                                $score = HazopScoringService::initialScore(
                                    (int) $get('likelihood'),
                                    (int) $get('severity'),
                                    (int) $get('exposure'),
                                );
                                $level = HazopScoringService::riskLevel($score);
                                $approval = HazopScoringService::approvalRequirement($level);
                                return "{$score} / 125 — " . ucfirst($level) . "\n↳ {$approval}";
                            }),
                    ]),

                    Forms\Components\Hidden::make('initial_risk_score')->default(0),
                    Forms\Components\Hidden::make('risk_classification')->default('low'),
                ]),

            // ---- Section 4: Risk Priority Number (S×O×D) ---------------
            Forms\Components\Section::make('4. Risk Priority Number — S × O × D')
                ->description('HAZOP/FMEA detectability analysis. RPN = Severity × Occurrence × Detectability (range 1–125).')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Select::make('rpn_severity')
                            ->label('Severity (S)')
                            ->options(HazopScoringService::severityOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                self::recalcRpn($get, $set)
                            ),

                        Forms\Components\Select::make('rpn_occurrence')
                            ->label('Occurrence (O)')
                            ->options(HazopScoringService::likelihoodOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                self::recalcRpn($get, $set)
                            ),

                        Forms\Components\Select::make('rpn_detectability')
                            ->label('Detectability (D)')
                            ->options(HazopScoringService::detectabilityOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                                self::recalcRpn($get, $set)
                            ),

                        Forms\Components\Placeholder::make('rpn_preview')
                            ->label('RPN Score (S×O×D)')
                            ->content(function (Forms\Get $get): string {
                                $rpn = HazopScoringService::rpn(
                                    (int) $get('rpn_severity'),
                                    (int) $get('rpn_occurrence'),
                                    (int) $get('rpn_detectability'),
                                );
                                $level = HazopScoringService::riskLevel($rpn);
                                return "{$rpn} / 125 — " . ucfirst($level);
                            }),
                    ]),

                    Forms\Components\Hidden::make('rpn_score')->default(0),
                ]),

            // ---- Section 5: Controls & Residual Risk --------------------
            Forms\Components\Section::make('5. Recommended Actions & Residual Risk')
                ->description('Document actions to reduce risk. Residual Risk = Initial Risk × (1 − Control Effectiveness%).')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('recommended_actions')
                        ->label('Recommended Actions')
                        ->helperText('What specific actions are required to reduce or eliminate this risk?')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('control_effectiveness')
                        ->label('Control Effectiveness (%)')
                        ->helperText('How effective are the controls? 0% = no reduction, 100% = fully eliminated.')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->suffix('%')
                        ->live()
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                            self::recalcResidual($get, $set)
                        )
                        ->columnSpan(1),

                    Forms\Components\Placeholder::make('residual_risk_preview')
                        ->label('Residual Risk Score & RRF')
                        ->content(function (Forms\Get $get): string {
                            $ir = HazopScoringService::initialScore(
                                (int) $get('likelihood'),
                                (int) $get('severity'),
                                (int) $get('exposure'),
                            );
                            $ce = (float) ($get('control_effectiveness') ?? 0);
                            $rr = HazopScoringService::residualRisk($ir, $ce);
                            $rrf = HazopScoringService::rrf($ir, $rr);
                            $level = HazopScoringService::riskLevel($rr);
                            return "Residual Risk: {$rr} / 125 — " . ucfirst($level)
                                . "\nRisk Reduction Factor (RRF): ×{$rrf}";
                        })
                        ->columnSpan(1),

                    Forms\Components\Hidden::make('residual_risk_score')->default(0),
                    Forms\Components\Hidden::make('risk_reduction_factor')->default(0),
                    Forms\Components\Hidden::make('residual_risk_classification')->default('low'),
                ]),

            // ---- Section 6: Action Assignment ---------------------------
            Forms\Components\Section::make('6. Action Assignment')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('risk_owner_id')
                        ->label('Risk Owner')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\Select::make('department_id')
                        ->label('Responsible Department')
                        ->options(Department::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('approval_status')
                        ->label('Approval Status')
                        ->options([
                            'pending'  => 'Pending Review',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected — Rework Required',
                        ])
                        ->default('pending')
                        ->required()
                        ->native(false)
                        ->live()
                        ->columnSpan(1),

                    Forms\Components\Select::make('approved_by_id')
                        ->label('Approved By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->visible(fn (Forms\Get $get): bool => $get('approval_status') === 'approved')
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('approval_date')
                        ->label('Approval Date')
                        ->native(false)
                        ->visible(fn (Forms\Get $get): bool => $get('approval_status') === 'approved')
                        ->columnSpan(1),
                ]),

            // ---- Section 7: Closure Verification -------------------------
            Forms\Components\Section::make('7. Closure Verification')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('closure_verification')
                        ->label('Closure Verification Notes')
                        ->helperText('Describe the evidence or method used to verify that recommended actions were completed effectively.')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('closure_verified_by_id')
                        ->label('Verified By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('closure_date')
                        ->label('Closure Date')
                        ->native(false)
                        ->columnSpan(1),
                ]),
        ]);
    }

    // ----------------------------------------------------------------
    // Table — HAZOP worksheet view
    // ----------------------------------------------------------------

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('deviation')
            ->columns([
                Tables\Columns\TextColumn::make('node_number')
                    ->label('No.')
                    ->sortable()
                    ->width('50px'),

                Tables\Columns\TextColumn::make('node_name')
                    ->label('Node / Process Area')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('guide_word')
                    ->label('Guide Word')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('parameter')
                    ->label('Parameter')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('deviation')
                    ->label('Deviation')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('initial_risk_score')
                    ->label('Initial Risk')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $level = HazopScoringService::riskLevel((int) $state);
                        return "{$state} / 125 — " . ucfirst($level);
                    })
                    ->color(fn ($state) => HazopScoringService::colorForScore((int) $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('control_effectiveness')
                    ->label('CE%')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0) . '%')
                    ->badge()
                    ->color(fn ($state): string => (float) $state >= 70 ? 'success' : ((float) $state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('residual_risk_score')
                    ->label('Residual Risk')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $level = HazopScoringService::riskLevel((float) $state);
                        return "{$state} / 125 — " . ucfirst($level);
                    })
                    ->color(fn ($state) => HazopScoringService::colorForScore((float) $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('risk_reduction_factor')
                    ->label('RRF')
                    ->formatStateUsing(fn ($state) => '×' . number_format((float) $state, 2))
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rpn_score')
                    ->label('RPN')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $level = HazopScoringService::riskLevel((int) $state);
                        return "{$state} — " . ucfirst($level);
                    })
                    ->color(fn ($state) => HazopScoringService::colorForScore((int) $state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('riskOwner.name')
                    ->label('Risk Owner')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->color(fn ($record): string =>
                        ($record->due_date && $record->due_date->isPast() && $record->status !== 'closed')
                            ? 'danger' : 'gray'
                    )
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Approval')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default    => 'Pending',
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ])
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => HazopNode::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'primary' => 'action_assigned',
                        'warning' => ['in_progress', 'verification_pending'],
                        'success' => 'closed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(HazopNode::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('risk_classification')
                    ->label('Initial Risk Level')
                    ->options([
                        'low'      => 'Low (1–20)',
                        'medium'   => 'Medium (21–50)',
                        'high'     => 'High (51–80)',
                        'critical' => 'Critical (81–125)',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Actions')
                    ->query(fn ($query) => $query
                        ->where('status', '!=', 'closed')
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                    )
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('node_number', 'asc');
    }

    // ----------------------------------------------------------------
    // Live calculation helpers
    // ----------------------------------------------------------------

    protected static function recalcInitial(Forms\Get $get, Forms\Set $set): void
    {
        $ir = HazopScoringService::initialScore(
            (int) $get('likelihood'),
            (int) $get('severity'),
            (int) $get('exposure'),
        );
        $set('initial_risk_score', $ir);
        $set('risk_classification', HazopScoringService::riskLevel($ir));

        // Cascade into residual recalc
        $ce = (float) ($get('control_effectiveness') ?? 0);
        $rr = HazopScoringService::residualRisk($ir, $ce);
        $set('residual_risk_score', $rr);
        $set('risk_reduction_factor', HazopScoringService::rrf($ir, $rr));
        $set('residual_risk_classification', HazopScoringService::riskLevel($rr));
    }

    protected static function recalcRpn(Forms\Get $get, Forms\Set $set): void
    {
        $set('rpn_score', HazopScoringService::rpn(
            (int) $get('rpn_severity'),
            (int) $get('rpn_occurrence'),
            (int) $get('rpn_detectability'),
        ));
    }

    protected static function recalcResidual(Forms\Get $get, Forms\Set $set): void
    {
        $ir = (int) ($get('initial_risk_score') ?: HazopScoringService::initialScore(
            (int) $get('likelihood'),
            (int) $get('severity'),
            (int) $get('exposure'),
        ));
        $ce = (float) ($get('control_effectiveness') ?? 0);
        $rr = HazopScoringService::residualRisk($ir, $ce);
        $set('residual_risk_score', $rr);
        $set('risk_reduction_factor', HazopScoringService::rrf($ir, $rr));
        $set('residual_risk_classification', HazopScoringService::riskLevel($rr));
    }
}
