<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentResource\Pages;
use App\Models\Incident;
use App\Services\RiskScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Incident Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Investigation & Reporting';

    /**
     * HSE Staff manage incidents day-to-day. MD has oversight access.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    /**
     * Only MD or HSE Staff may delete an incident record (audit trail integrity).
     */
    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Incident Report')
                ->description('Log new safety, environmental, or security incidents as soon as possible.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / not project specific'),

                    Forms\Components\Hidden::make('reported_by')
                        ->default(fn () => auth()->id()),

                    Forms\Components\DateTimePicker::make('incident_date')
                        ->label('Date & Time of Incident')
                        ->native(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('location')
                        ->label('Location / Site')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\Select::make('incident_type')
                        ->options([
                            'near_miss' => 'Near Miss',
                            'unsafe_act' => 'Unsafe Act',
                            'unsafe_condition' => 'Unsafe Condition',
                            'first_aid' => 'First Aid Case',
                            'medical_treatment' => 'Medical Treatment Case',
                            'lost_time' => 'Lost Time Injury',
                            'fatality' => 'Fatality',
                            'environmental' => 'Environmental Incident',
                            'property_damage' => 'Property Damage',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('likelihood')
                            ->label('Likelihood (L)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateRisk($get, $set)),

                        Forms\Components\Select::make('impact')
                            ->label('Impact / Severity (I)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateRisk($get, $set)),

                        Forms\Components\Placeholder::make('risk_score_preview')
                            ->label('Risk Score (R = L x I)')
                            ->content(function (Forms\Get $get) {
                                $score = RiskScoringService::score((int) $get('likelihood'), (int) $get('impact'));
                                $level = RiskScoringService::level($score);

                                return "{$score} / 25 - ".ucfirst($level);
                            }),
                    ]),

                    Forms\Components\Hidden::make('severity')->default('low'),
                    Forms\Components\Hidden::make('risk_score')->default(0),

                    Forms\Components\Textarea::make('description')
                        ->label('What happened?')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Investigation Method')
                ->description('Select the investigation method. Suggested method is based on the Risk Score above.')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('investigation_method')
                            ->label('Investigation Method')
                            ->options([
                                'five_whys' => '5 Whys (Minor/Low Risk)',
                                'fishbone' => 'Fishbone / Ishikawa (Moderate)',
                                'taproot' => 'TapRooT Style (Major/High Risk)',
                                'barrier_analysis' => 'Barrier Analysis (Critical)',
                            ])
                            ->native(false)
                            ->live()
                            ->helperText(fn (Forms\Get $get) => 'Suggested: '.match (
                                RiskScoringService::level(
                                    RiskScoringService::score((int) $get('likelihood'), (int) $get('impact'))
                                )) {
                                    'critical' => 'TapRooT + Barrier Analysis (Critical risk)',
                                    'high' => 'Fishbone (High risk)',
                                    default => '5 Whys (Low / Medium risk)',
                                }),

                        Forms\Components\Select::make('investigation_status')
                            ->options([
                                'not_started' => 'Not Started',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                            ])
                            ->default('not_started')
                            ->native(false),

                        Forms\Components\TextInput::make('investigation_responsible_person')
                            ->label('Lead Investigator')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('investigation_target_date')
                            ->label('Target Completion Date')
                            ->native(false),
                    ]),
                ]),

            // -------------------------------------------------------
            // APPROACH 1: 5 WHYS
            // -------------------------------------------------------
            Forms\Components\Section::make('5 Whys Analysis')
                ->description('Ask "Why?" five times to reach the root cause. Start from the observable incident and work backwards.')
                ->collapsible()
                ->collapsed(fn (Forms\Get $get) => $get('investigation_method') !== 'five_whys')
                ->visible(fn (Forms\Get $get) => in_array($get('investigation_method'), ['five_whys', 'fishbone']))
                ->schema([
                    Forms\Components\Textarea::make('why_1')->label('Why 1 — Why did this happen?')->rows(2),
                    Forms\Components\Textarea::make('why_2')->label('Why 2 — Why did that happen?')->rows(2),
                    Forms\Components\Textarea::make('why_3')->label('Why 3 — Why did that happen?')->rows(2),
                    Forms\Components\Textarea::make('why_4')->label('Why 4 — Why did that happen?')->rows(2),
                    Forms\Components\Textarea::make('why_5')->label('Why 5 — Why did that happen? (Root Cause)')->rows(2),
                    Forms\Components\Textarea::make('root_cause')->label('Root Cause Summary')->rows(2),
                ]),

            // -------------------------------------------------------
            // APPROACH 2: FISHBONE (ISHIKAWA)
            // -------------------------------------------------------
            Forms\Components\Section::make('Fishbone / Ishikawa Analysis')
                ->description('Identify causes across 6 categories (People, Equipment, Method, Materials, Environment, Management).')
                ->collapsible()
                ->collapsed(fn (Forms\Get $get) => $get('investigation_method') !== 'fishbone')
                ->visible(fn (Forms\Get $get) => $get('investigation_method') === 'fishbone')
                ->schema([
                    Forms\Components\Repeater::make('fishbone_data')
                        ->label('Cause Categories')
                        ->schema([
                            Forms\Components\Select::make('category')
                                ->options([
                                    'people' => 'People',
                                    'equipment' => 'Equipment',
                                    'method' => 'Method / Procedure',
                                    'materials' => 'Materials',
                                    'environment' => 'Environment',
                                    'management' => 'Management',
                                ])
                                ->required()
                                ->native(false),

                            Forms\Components\Textarea::make('causes')
                                ->label('Causes Identified')
                                ->rows(2)
                                ->helperText('List causes found under this category (one per line).'),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add Category')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('root_cause')
                        ->label('Root Cause Summary')
                        ->rows(2),

                    Forms\Components\Textarea::make('corrective_actions')
                        ->label('Recommendations')
                        ->rows(2),
                ]),

            // -------------------------------------------------------
            // APPROACH 3: TapRooT STYLE
            // -------------------------------------------------------
            Forms\Components\Section::make('TapRooT Style Investigation')
                ->description('For serious incidents. Reconstruct the full event timeline, identify direct and contributing causes.')
                ->collapsible()
                ->collapsed(fn (Forms\Get $get) => $get('investigation_method') !== 'taproot')
                ->visible(fn (Forms\Get $get) => $get('investigation_method') === 'taproot')
                ->schema([
                    Forms\Components\Repeater::make('taproot_timeline')
                        ->label('Event Timeline')
                        ->schema([
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\DateTimePicker::make('time')
                                    ->label('Date & Time')
                                    ->native(false)
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('event')
                                    ->label('Event Description')
                                    ->rows(2)
                                    ->columnSpan(3),
                            ]),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add Timeline Event')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('taproot_witnesses')
                        ->label('Witness Statements (summarised)')
                        ->rows(3),

                    Forms\Components\FileUpload::make('evidence_files')
                        ->label('Evidence / Photos / Documents')
                        ->multiple()
                        ->directory('incidents/evidence')
                        ->openable(),

                    Forms\Components\Textarea::make('taproot_direct_causes')
                        ->label('Direct Causes')
                        ->rows(2),

                    Forms\Components\Textarea::make('taproot_contributing_factors')
                        ->label('Contributing Factors')
                        ->rows(2),

                    Forms\Components\Textarea::make('root_cause')
                        ->label('Root Cause(s) Determined')
                        ->rows(2),

                    Forms\Components\Textarea::make('taproot_verification_review')
                        ->label('Verification & Effectiveness Review')
                        ->rows(2),
                ]),

            // -------------------------------------------------------
            // APPROACH 4: BARRIER ANALYSIS
            // -------------------------------------------------------
            Forms\Components\Section::make('Barrier Analysis')
                ->description('Identify which controls (barriers) existed, why they failed, and what corrective actions are required.')
                ->collapsible()
                ->collapsed(fn (Forms\Get $get) => $get('investigation_method') !== 'barrier_analysis')
                ->visible(fn (Forms\Get $get) => in_array($get('investigation_method'), ['taproot', 'barrier_analysis']))
                ->schema([
                    Forms\Components\Repeater::make('barrier_analysis_data')
                        ->label('Barrier Analysis Table')
                        ->schema([
                            Forms\Components\TextInput::make('hazard')
                                ->label('Hazard')
                                ->required(),

                            Forms\Components\TextInput::make('existing_control')
                                ->label('Required Barrier / Control'),

                            Forms\Components\Select::make('control_status')
                                ->label('Barrier Status')
                                ->options([
                                    'present_effective' => 'Present & Effective',
                                    'present_inadequate' => 'Present but Inadequate',
                                    'missing' => 'Missing',
                                    'not_used' => 'Not Used / Not Worn',
                                    'not_implemented' => 'Not Implemented',
                                ])
                                ->native(false),

                            Forms\Components\Textarea::make('control_failure')
                                ->label('Reason for Control Failure')
                                ->rows(2),

                            Forms\Components\Textarea::make('corrective_action')
                                ->label('Corrective Action')
                                ->rows(2),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add Hazard / Barrier')
                        ->columnSpanFull(),
                ]),

            // -------------------------------------------------------
            // STRUCTURED CORRECTIVE ACTION PLAN (all methods)
            // -------------------------------------------------------
            Forms\Components\Section::make('Corrective Action Plan')
                ->description('Record all corrective and preventive actions, with owners, due dates, and budgets.')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Forms\Get $get) => filled($get('investigation_method')))
                ->schema([
                    Forms\Components\Repeater::make('corrective_actions_plan')
                        ->label('Actions')
                        ->schema([
                            Forms\Components\Textarea::make('description')
                                ->label('Action Description')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\TextInput::make('responsible')
                                    ->label('Responsible Person'),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->native(false),

                                Forms\Components\TextInput::make('budget')
                                    ->label('Budget (TZS)')
                                    ->numeric(),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                        'verified' => 'Verified & Closed',
                                    ])
                                    ->default('pending')
                                    ->native(false),
                            ]),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add Corrective Action')
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'investigating' => 'Investigating',
                                'closed' => 'Closed',
                            ])
                            ->default('open')
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\DatePicker::make('closed_date')
                            ->native(false)
                            ->visible(fn (Forms\Get $get) => $get('status') === 'closed'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('incident_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'gray' => ['near_miss', 'first_aid'],
                        'primary' => ['unsafe_act', 'unsafe_condition'],
                        'warning' => ['medical_treatment', 'property_damage', 'environmental'],
                        'danger' => ['lost_time', 'fatality'],
                    ]),

                Tables\Columns\TextColumn::make('risk_score')
                    ->label('Risk Score (LxI)')
                    ->badge()
                    ->formatStateUsing(fn (int $state, Incident $record): string => "{$state}/25 - ".ucfirst($record->risk_level))
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'investigating',
                        'success' => 'closed',
                    ]),

                Tables\Columns\BadgeColumn::make('investigation_method')
                    ->label('Method')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'five_whys' => '5 Whys',
                        'fishbone' => 'Fishbone',
                        'taproot' => 'TapRooT',
                        'barrier_analysis' => 'Barrier',
                        default => 'None',
                    })
                    ->colors([
                        'gray' => fn ($state) => $state === null,
                        'info' => 'five_whys',
                        'primary' => 'fishbone',
                        'warning' => 'taproot',
                        'danger' => 'barrier_analysis',
                    ])
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('investigation_status')
                    ->label('Investigation')
                    ->colors([
                        'gray' => 'not_started',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Reported By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'investigating' => 'Investigating',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('investigation_status')
                    ->label('Investigation Status')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('investigation_method')
                    ->label('Investigation Method')
                    ->options([
                        'five_whys' => '5 Whys',
                        'fishbone' => 'Fishbone',
                        'taproot' => 'TapRooT',
                        'barrier_analysis' => 'Barrier Analysis',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('incident_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.incident', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Recalculate the live Risk Score / Level preview and keep the
     * hidden severity + risk_score fields in sync (also recomputed
     * authoritatively in Incident::booted() on save).
     */
    protected static function recalculateRisk(Forms\Get $get, Forms\Set $set): void
    {
        $score = RiskScoringService::score((int) $get('likelihood'), (int) $get('impact'));

        $set('risk_score', $score);
        $set('severity', RiskScoringService::level($score));
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\IncidentResource\RelationManagers\InvestigationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),
            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
}
