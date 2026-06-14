<?php

namespace App\Filament\Resources\IncidentResource\RelationManagers;

use App\Models\IncidentInvestigation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvestigationsRelationManager extends RelationManager
{
    protected static string $relationship = 'investigations';

    protected static ?string $title = 'Investigations';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public function form(Form $form): Form
    {
        $incident = $this->getOwnerRecord();
        $recommendedMethod = IncidentInvestigation::recommendedMethod((int) $incident->risk_score);
        $riskLevel = ucfirst($incident->risk_level ?? 'low');

        return $form->schema([

            Forms\Components\Section::make('Investigation Setup')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('method')
                        ->label('Investigation Method')
                        ->options(IncidentInvestigation::METHOD_LABELS)
                        ->default($recommendedMethod)
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText("Recommended for Risk Level '{$riskLevel}' (Score: {$incident->risk_score}/25): "
                            .IncidentInvestigation::METHOD_LABELS[$recommendedMethod]),

                    Forms\Components\Select::make('conducted_by')
                        ->label('Conducted By')
                        ->relationship('conductedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),
                ]),

            // ============================================================
            // METHOD A: 5 WHYS
            // ============================================================
            Forms\Components\Section::make('5 Whys Analysis')
                ->description('Repeatedly ask "Why?" to trace the root cause. Each answer becomes the next question.')
                ->visible(fn (Forms\Get $get) => in_array($get('method'), ['five_whys', 'fishbone']))
                ->schema([
                    Forms\Components\Textarea::make('why_1')
                        ->label('Why 1 — Why did the incident happen?')
                        ->rows(2)->required(),

                    Forms\Components\Textarea::make('why_2')
                        ->label('Why 2 — Why did that happen?')
                        ->rows(2),

                    Forms\Components\Textarea::make('why_3')
                        ->label('Why 3')
                        ->rows(2),

                    Forms\Components\Textarea::make('why_4')
                        ->label('Why 4')
                        ->rows(2),

                    Forms\Components\Textarea::make('why_5')
                        ->label('Why 5 — Root Cause reached?')
                        ->rows(2),
                ]),

            // ============================================================
            // METHOD B: FISHBONE (Ishikawa) - uses sub-table via Repeater
            // ============================================================
            Forms\Components\Section::make('Fishbone (Ishikawa) — Causes by Category')
                ->description('Identify contributing causes under each category (People, Equipment, Method, Materials, Environment, Management).')
                ->visible(fn (Forms\Get $get) => $get('method') === 'fishbone')
                ->schema([
                    Forms\Components\Repeater::make('fishboneCauses')
                        ->relationship('fishboneCauses')
                        ->label('')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('category')
                                    ->options([
                                        'people' => 'People',
                                        'equipment' => 'Equipment',
                                        'method' => 'Method',
                                        'materials' => 'Materials',
                                        'environment' => 'Environment',
                                        'management' => 'Management',
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\Textarea::make('cause')
                                    ->label('Cause / Contributing Factor')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpan(2),
                            ]),
                        ])
                        ->addActionLabel('Add Cause')
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),

            // ============================================================
            // METHODS C & D: TapRooT / Barrier Analysis
            // Full 12-section TapRooT Style Investigation structure.
            // ============================================================
            Forms\Components\Wizard::make([

                // ---- Section 1: Incident Overview ------------------------
                Forms\Components\Wizard\Step::make('Incident Overview')
                    ->description('What, where, when, who')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Section::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\Placeholder::make('overview_description')
                                    ->label('What is the incident being investigated?')
                                    ->content($incident->description ?: '-')
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('overview_location')
                                    ->label('Where did it occur?')
                                    ->content($incident->location ?: '-'),

                                Forms\Components\Placeholder::make('overview_date')
                                    ->label('When did it occur?')
                                    ->content($incident->incident_date?->format('d M Y') ?: '-'),

                                Forms\Components\Textarea::make('people_involved')
                                    ->label('Who was involved? (names, roles)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // ---- Section 2: Event Timeline Reconstruction ----------------
                Forms\Components\Wizard\Step::make('Event Timeline')
                    ->description('Sequence of events before, during, after')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\Repeater::make('timeline_events')
                            ->label('Event Timeline Reconstruction')
                            ->schema([
                                Forms\Components\Grid::make(4)->schema([
                                    Forms\Components\Select::make('phase')
                                        ->label('Phase')
                                        ->options([
                                            'before' => 'Before Incident',
                                            'during' => 'During Incident',
                                            'after' => 'After Incident',
                                        ])
                                        ->default('before')
                                        ->required()
                                        ->native(false),

                                    Forms\Components\TextInput::make('time')
                                        ->label('Time / Date')
                                        ->placeholder('e.g. 14:30, Day 1')
                                        ->required(),

                                    Forms\Components\TextInput::make('event')
                                        ->label('Event Title')
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\Textarea::make('description')
                                        ->label('What happened? (step-by-step detail)')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->addActionLabel('Add Event')
                            ->reorderable(true)
                            ->helperText('Add one row per event - mark each as Before / During / After the incident to build the full sequence. "During" rows should capture the step-by-step actions taken.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('witness_statements')
                            ->label('Witness Statements')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // ---- Section 3: Task / Activity Description ----------------
                Forms\Components\Wizard\Step::make('Task / Activity')
                    ->description('What was being done, procedures, deviations')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\Textarea::make('task_description')
                            ->label('What task was being performed at the time of the incident?')
                            ->rows(2),

                        Forms\Components\Textarea::make('procedures_followed')
                            ->label('What procedures or instructions were supposed to be followed?')
                            ->rows(2),

                        Forms\Components\Textarea::make('deviations_from_practice')
                            ->label('Were there any deviations from normal work practice?')
                            ->rows(2),
                    ]),

                // ---- Section 4: Direct Causes ------------------------------
                Forms\Components\Wizard\Step::make('Direct Causes')
                    ->description('Unsafe acts, conditions, immediate failures')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\Textarea::make('direct_causes')
                            ->label('What directly caused the incident to occur?')
                            ->rows(2),

                        Forms\Components\Textarea::make('unsafe_acts_conditions')
                            ->label('What unsafe acts or unsafe conditions were present?')
                            ->rows(2),

                        Forms\Components\Textarea::make('immediate_failures')
                            ->label('What immediate failures led to the event?')
                            ->rows(2),
                    ]),

                // ---- Section 5: Contributing Causes -------------------------
                Forms\Components\Wizard\Step::make('Contributing Causes')
                    ->description('Equipment, environment, human, communication')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Textarea::make('contributing_factors')
                            ->label('What factors contributed to the direct causes?')
                            ->rows(2),

                        Forms\Components\Textarea::make('equipment_environmental_human_factors')
                            ->label('Were there equipment, environmental, or human factors involved?')
                            ->rows(2),

                        Forms\Components\Textarea::make('communication_supervision_gaps')
                            ->label('Were there communication or supervision gaps?')
                            ->rows(2),
                    ]),

                // ---- Section 6: Root Causes (System Failures) ------------------
                Forms\Components\Wizard\Step::make('Root Causes')
                    ->description('Management system failures')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Textarea::make('root_cause')
                            ->label('What management system failures allowed the incident to occur?')
                            ->rows(2)
                            ->required(),

                        Forms\Components\Textarea::make('training_supervision_failure')
                            ->label('Was there a failure in training, supervision, or procedures?')
                            ->rows(2),

                        Forms\Components\Textarea::make('risk_assessment_adequacy')
                            ->label('Was risk assessment adequate and effectively implemented?')
                            ->rows(2),

                        Forms\Components\Textarea::make('maintenance_inspection_effectiveness')
                            ->label('Was maintenance or inspection system effective?')
                            ->rows(2),
                    ]),

                // ---- Section 7: Safeguards / Barriers Analysis ------------------
                Forms\Components\Wizard\Step::make('Safeguards / Barriers')
                    ->description('Barriers that should have prevented this')
                    ->icon('heroicon-o-shield-exclamation')
                    ->schema([
                        Forms\Components\Repeater::make('barrierItems')
                            ->relationship('barrierItems')
                            ->label('')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('hazard')
                                        ->label('Hazard')
                                        ->required(),

                                    Forms\Components\TextInput::make('required_barrier')
                                        ->label('What barrier was supposed to prevent this?'),
                                ]),

                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('barrier_status')
                                        ->label('Which barrier failed or was missing?')
                                        ->options([
                                            'in_place' => 'In Place',
                                            'missing' => 'Missing',
                                            'failed' => 'Failed',
                                            'not_worn' => 'Not Worn (PPE)',
                                            'not_implemented' => 'Not Implemented',
                                        ])
                                        ->default('missing')
                                        ->required()
                                        ->native(false),

                                    Forms\Components\Textarea::make('control_failure')
                                        ->label('Why was the barrier ineffective?')
                                        ->rows(2),

                                    Forms\Components\Textarea::make('corrective_action')
                                        ->label('Corrective Action')
                                        ->rows(2),
                                ]),
                            ])
                            ->addActionLabel('Add Hazard / Barrier Row')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                // ---- Section 8: Human Performance Factors ------------------
                Forms\Components\Wizard\Step::make('Human Performance')
                    ->description('Understanding, fatigue, competency')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Textarea::make('task_understanding')
                            ->label('Was the task properly understood?')
                            ->rows(2),

                        Forms\Components\Textarea::make('distractions_fatigue_stress')
                            ->label('Were there distractions, fatigue, or stress factors?')
                            ->rows(2),

                        Forms\Components\Textarea::make('competency_assessment')
                            ->label('Was competency sufficient for the task?')
                            ->rows(2),
                    ]),

                // ---- Section 9: Corrective Actions ------------------------------
                Forms\Components\Wizard\Step::make('Corrective Actions')
                    ->description('Eliminate direct causes & system changes')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\Textarea::make('recommendations')
                            ->label('What actions are required to eliminate direct causes? What system changes are needed to address root causes?')
                            ->rows(4),
                    ]),

                // ---- Section 10: Preventive Actions ------------------------------
                Forms\Components\Wizard\Step::make('Preventive Actions')
                    ->description('Long-term improvements')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Textarea::make('preventive_actions')
                            ->label('What long-term improvements will prevent recurrence? What changes are needed in procedures, training, or maintenance systems?')
                            ->rows(4),
                    ]),

                // ---- Section 11: Effectiveness Verification ------------------------------
                Forms\Components\Wizard\Step::make('Effectiveness Verification')
                    ->description('Confirm corrective actions worked')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        Forms\Components\Textarea::make('effectiveness_indicators')
                            ->label('How will we confirm that corrective actions worked? What indicators will be used to measure effectiveness?')
                            ->rows(3),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Textarea::make('verification_notes')
                                ->label('Verification / Effectiveness Review Notes')
                                ->rows(2),

                            Forms\Components\DatePicker::make('verification_date')
                                ->label('Verification Date')
                                ->native(false),
                        ]),
                    ]),

                // ---- Section 12: Management Review ------------------------------
                Forms\Components\Wizard\Step::make('Management Review')
                    ->description('Lessons learned & org-level improvements')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->schema([
                        Forms\Components\Textarea::make('lessons_learned')
                            ->label('What lessons learned are identified?')
                            ->rows(3),

                        Forms\Components\Textarea::make('management_review_notes')
                            ->label('What system improvements are required at organizational level?')
                            ->rows(3),

                        Forms\Components\FileUpload::make('evidence_files')
                            ->label('Evidence (Photos, Videos, Documents)')
                            ->directory('investigations/evidence')
                            ->multiple()
                            ->openable()
                            ->columnSpanFull(),
                    ]),
            ])
                ->visible(fn (Forms\Get $get) => in_array($get('method'), ['taprout', 'barrier']))
                ->columnSpanFull()
                ->skippable(),

            // ============================================================
            // ROOT CAUSE & RECOMMENDATIONS (5 Whys / Fishbone only -
            // TapRooT/Barrier capture these inside the Wizard above)
            // ============================================================
            Forms\Components\Section::make('Root Cause & Corrective Actions')
                ->columns(1)
                ->visible(fn (Forms\Get $get) => in_array($get('method'), ['five_whys', 'fishbone']))
                ->schema([
                    Forms\Components\Textarea::make('root_cause')
                        ->label('Root Cause Summary')
                        ->rows(3)
                        ->required()
                        ->helperText('Summarise the underlying root cause identified through the investigation.'),

                    Forms\Components\Textarea::make('recommendations')
                        ->label('Corrective / Preventive Actions (Recommendations)')
                        ->rows(3),

                    Forms\Components\FileUpload::make('evidence_files')
                        ->label('Evidence (Photos, Videos, Documents)')
                        ->directory('investigations/evidence')
                        ->multiple()
                        ->openable()
                        ->columnSpanFull(),
                ]),

            // ============================================================
            // ACTION TRACKING (all methods)
            // ============================================================
            Forms\Components\Section::make('Action Tracking & Closure')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('responsible_person_id')
                        ->label('Responsible Person')
                        ->relationship('responsiblePerson', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('target_date')
                        ->label('Target Completion Date')
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'verified' => 'Verified & Closed',
                        ])
                        ->default('draft')
                        ->required()
                        ->native(false),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('method')
                    ->formatStateUsing(fn (string $state): string => IncidentInvestigation::METHOD_LABELS[$state] ?? $state)
                    ->colors([
                        'info' => 'five_whys',
                        'warning' => 'fishbone',
                        'danger' => ['taprout', 'barrier'],
                    ]),

                Tables\Columns\TextColumn::make('root_cause')
                    ->label('Root Cause')
                    ->limit(50)
                    ->placeholder('Not yet determined'),

                Tables\Columns\TextColumn::make('conductedBy.name')
                    ->label('Conducted By'),

                Tables\Columns\TextColumn::make('target_date')
                    ->date('d M Y')
                    ->label('Target Date'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'primary' => 'verified',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Start Investigation')
                    ->modalWidth('4xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('4xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
