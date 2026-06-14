<?php

namespace App\Filament\Resources\IncidentResource\RelationManagers;

use App\Models\IncidentInvestigation;
use App\Services\RiskScoringService;
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
                                        'people' => '👤 People',
                                        'equipment' => '🔧 Equipment',
                                        'method' => '📋 Method',
                                        'materials' => '📦 Materials',
                                        'environment' => '🌿 Environment',
                                        'management' => '🏢 Management',
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
            // METHOD C: TapRooT STYLE INVESTIGATION
            // ============================================================
            Forms\Components\Section::make('TapRooT — Event Timeline')
                ->description('Reconstruct a chronological timeline of events leading to the incident.')
                ->visible(fn (Forms\Get $get) => in_array($get('method'), ['taprout', 'barrier']))
                ->schema([
                    Forms\Components\Repeater::make('timeline_events')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('time')
                                    ->label('Time / Date')
                                    ->placeholder('e.g. 14:30, Day 1')
                                    ->required(),

                                Forms\Components\TextInput::make('event')
                                    ->label('Event Title')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('description')
                                    ->label('Detail')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                        ])
                        ->addActionLabel('Add Event')
                        ->reorderable(true)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('TapRooT — Investigation Details')
                ->visible(fn (Forms\Get $get) => in_array($get('method'), ['taprout', 'barrier']))
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('witness_statements')
                        ->label('Witness Statements')
                        ->rows(3),

                    Forms\Components\Textarea::make('direct_causes')
                        ->label('Identify Direct Causes')
                        ->rows(2),

                    Forms\Components\Textarea::make('contributing_factors')
                        ->label('Contributing Factors')
                        ->rows(2),

                    Forms\Components\Textarea::make('action_plan')
                        ->label('Action Plan')
                        ->rows(3),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification / Effectiveness Review')
                            ->rows(2),

                        Forms\Components\DatePicker::make('verification_date')
                            ->label('Verification Date')
                            ->native(false),
                    ]),
                ]),

            // ============================================================
            // METHOD D: BARRIER ANALYSIS
            // ============================================================
            Forms\Components\Section::make('Barrier Analysis — Control Failures')
                ->description('Identify each hazard, its required control (barrier), and why the control failed.')
                ->visible(fn (Forms\Get $get) => $get('method') === 'barrier')
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
                                    ->label('Required Control / Barrier'),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('barrier_status')
                                    ->label('Barrier Status')
                                    ->options([
                                        'in_place' => '✓ In Place',
                                        'missing' => '✗ Missing',
                                        'failed' => '⚠ Failed',
                                        'not_worn' => '✗ Not Worn (PPE)',
                                        'not_implemented' => '✗ Not Implemented',
                                    ])
                                    ->default('missing')
                                    ->required()
                                    ->native(false),

                                Forms\Components\Textarea::make('control_failure')
                                    ->label('Why Did Control Fail?')
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

            // ============================================================
            // ROOT CAUSE + ACTIONS (all methods)
            // ============================================================
            Forms\Components\Section::make('Root Cause & Corrective Actions')
                ->columns(1)
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
            // ACTION TRACKING
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
                    ->label('Start Investigation'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
