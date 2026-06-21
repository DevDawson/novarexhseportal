<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazardRegisterResource\Pages;
use App\Filament\Resources\HazardRegisterResource\RelationManagers;
use App\Models\Department;
use App\Models\HazardRegister;
use App\Models\User;
use App\Services\RiskScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HazardRegisterResource extends Resource
{
    protected static ?string $model = HazardRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'HIRA';

    protected static ?string $navigationLabel = 'HAZID / HIRA';

    protected static ?string $modelLabel = 'Hazard';

    protected static ?string $pluralModelLabel = 'Hazard Register (HAZID/HIRA)';

    protected static ?int $navigationSort = 3;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage hazards') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage hazards') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage hazards') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager']) ?? false;
    }

    // ----------------------------------------------------------------
    // Form
    // ----------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([

            // --------------------------------------------------------
            // SECTION 1: Project & Assessment Information
            // --------------------------------------------------------
            Forms\Components\Section::make('1. Project & Assessment Information')
                ->description('Identify the scope, location, and responsible parties for this HAZID assessment.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('hazard_id')
                        ->label('HAZID Reference No.')
                        ->placeholder('Auto-generated on save')
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Workflow Status')
                        ->options(HazardRegister::STATUS_LABELS)
                        ->default('draft')
                        ->required()
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('project_id')
                        ->label('Project / Work Package')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / not project specific')
                        ->columnSpan(1),

                    Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->options(Department::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('location')
                        ->label('Location / Site Area')
                        ->maxLength(255)
                        ->placeholder('e.g. Site B — Level 3 scaffold platform')
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('date_identified')
                        ->label('Date Identified')
                        ->native(false)
                        ->default(now())
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('identified_by_id')
                        ->label('Identified By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Next Review Date')
                        ->native(false)
                        ->columnSpan(1),
                ]),

            // --------------------------------------------------------
            // SECTION 2: Hazard Identification
            // --------------------------------------------------------
            Forms\Components\Section::make('2. Hazard Identification')
                ->description('Document the activity, hazard, its source, potential causes, consequences, and those at risk.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('activity_task')
                        ->label('Activity / Task')
                        ->placeholder('e.g. Welding on scaffold platform at height')
                        ->maxLength(255)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('hazard_category')
                        ->label('Hazard Category')
                        ->options(HazardRegister::HAZARD_CATEGORY_LABELS)
                        ->required()
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('hazard_source')
                        ->label('Hazard Source')
                        ->placeholder('e.g. Oxy-acetylene torch, scaffolding edge')
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('hazard_description')
                        ->label('Hazard Description')
                        ->helperText('What is the nature of the hazard? Describe it clearly.')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('potential_causes')
                        ->label('Potential Causes')
                        ->helperText('What could cause this hazard to materialise?')
                        ->rows(3)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('potential_consequences')
                        ->label('Potential Consequences')
                        ->helperText('What could be the impact on people, environment, or assets?')
                        ->rows(3)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('who_might_be_harmed')
                        ->label('Who Might Be Harmed?')
                        ->placeholder('e.g. Welders, nearby workers, site visitors, public')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 3: Initial Risk Assessment (before controls)
            // --------------------------------------------------------
            Forms\Components\Section::make('3. Initial Risk Assessment (Before Controls)')
                ->description('Rate the risk BEFORE any controls are applied using the L×S matrix (1–5 scale). Provide justification.')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('initial_likelihood')
                            ->label('Likelihood (L) — 1 to 5')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateInitialRisk($get, $set)),

                        Forms\Components\Select::make('initial_severity')
                            ->label('Severity (S) — 1 to 5')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateInitialRisk($get, $set)),

                        Forms\Components\Placeholder::make('initial_risk_preview')
                            ->label('Initial Risk Score (L × S)')
                            ->content(function (Forms\Get $get): string {
                                $score = RiskScoringService::score(
                                    (int) $get('initial_likelihood'),
                                    (int) $get('initial_severity')
                                );
                                $level = RiskScoringService::level($score);
                                return "{$score} / 25 — " . ucfirst($level);
                            }),
                    ]),

                    Forms\Components\Hidden::make('initial_risk_score')->default(0),

                    Forms\Components\Textarea::make('justification_of_risk_rating')
                        ->label('Justification of Risk Rating')
                        ->helperText('Explain why this likelihood and severity rating was selected.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 4: Controls
            // --------------------------------------------------------
            Forms\Components\Section::make('4. Risk Controls')
                ->description('Document all controls in place and planned. Follow the Hierarchy of Controls (Elimination → PPE).')
                ->schema([
                    Forms\Components\Textarea::make('existing_controls')
                        ->label('Existing Controls Currently In Place')
                        ->helperText('List all controls that are already active for this hazard.')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\CheckboxList::make('additional_controls')
                        ->label('Additional Controls to Apply (Hierarchy of Controls)')
                        ->options(HazardRegister::CONTROL_HIERARCHY_OPTIONS)
                        ->columns(1)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('additional_controls_description')
                        ->label('Additional Controls — Detailed Description')
                        ->helperText('Describe specifically what additional controls will be implemented and by whom.')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 5: Residual Risk & Action Management
            // --------------------------------------------------------
            Forms\Components\Section::make('5. Residual Risk Assessment & Action Management')
                ->description('Rate the risk AFTER all controls are applied. Assign responsibility and set priority.')
                ->columns(2)
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('residual_likelihood')
                            ->label('Likelihood (L) — after controls')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateResidualRisk($get, $set)),

                        Forms\Components\Select::make('residual_severity')
                            ->label('Severity (S) — after controls')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateResidualRisk($get, $set)),

                        Forms\Components\Placeholder::make('residual_risk_preview')
                            ->label('Residual Risk Score (L × S)')
                            ->content(function (Forms\Get $get): string {
                                $score = RiskScoringService::score(
                                    (int) $get('residual_likelihood'),
                                    (int) $get('residual_severity')
                                );
                                $level = RiskScoringService::level($score);
                                return "{$score} / 25 — " . ucfirst($level);
                            }),
                    ])->columnSpanFull(),

                    Forms\Components\Hidden::make('residual_risk_score')->default(0),

                    Forms\Components\Select::make('responsible_person_id')
                        ->label('Responsible Person')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\Select::make('priority_level')
                        ->label('Priority Level')
                        ->options([
                            'low'      => 'Low',
                            'medium'   => 'Medium',
                            'high'     => 'High',
                            'critical' => 'Critical',
                        ])
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('escalation_level')
                        ->label('Escalation Level')
                        ->options(HazardRegister::ESCALATION_LABELS)
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('target_completion_date')
                        ->label('Target Completion Date')
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('approved_by_id')
                        ->label('Approved By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('approval_date')
                        ->label('Approval Date')
                        ->native(false)
                        ->columnSpan(1),
                ]),

            // --------------------------------------------------------
            // SECTION 6: Verification & Closure
            // --------------------------------------------------------
            Forms\Components\Section::make('6. Verification & Closure')
                ->description('Confirm that controls have been implemented and the residual risk is acceptable.')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('verification_method')
                        ->label('Verification Method')
                        ->helperText('How will control effectiveness be verified? (e.g. inspection, test, observation)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('verification_evidence')
                        ->label('Verification Evidence')
                        ->helperText('Reference to documents, inspection records, or test results confirming control effectiveness.')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('verification_date')
                        ->label('Verification Date')
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('closure_comments')
                        ->label('Closure Comments')
                        ->helperText('Summary of actions taken and confirmation that the hazard is adequately controlled or closed.')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('closed_by_id')
                        ->label('Closed By')
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
    // Table
    // ----------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hazard_id')
                    ->label('HAZID Ref.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('activity_task')
                    ->label('Activity / Task')
                    ->searchable()
                    ->sortable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('hazard_category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => HazardRegister::HAZARD_CATEGORY_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => ['physical', 'mechanical', 'electrical', 'fire', 'radiation'],
                        'warning' => ['chemical', 'biological'],
                        'info'    => ['ergonomic', 'psychosocial'],
                        'success' => ['environmental'],
                    ]),

                Tables\Columns\TextColumn::make('initial_risk_score')
                    ->label('Initial Risk')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $level = RiskScoringService::level((int) $state);
                        return "{$state}/25 — " . ucfirst($level);
                    })
                    ->color(fn ($state) => RiskScoringService::colorForScore((int) $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('residual_risk_score')
                    ->label('Residual Risk')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $level = RiskScoringService::level((int) $state);
                        return "{$state}/25 — " . ucfirst($level);
                    })
                    ->color(fn ($state) => RiskScoringService::colorForScore((int) $state))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('priority_level')
                    ->label('Priority')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '—'))
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger'  => 'high',
                        'primary' => 'critical',
                    ])
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => HazardRegister::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'gray'    => ['draft', 'closed'],
                        'danger'  => ['open', 'action_required'],
                        'warning' => ['under_assessment', 'verification_pending'],
                        'primary' => 'controls_in_progress',
                        'success' => 'controlled',
                    ]),

                Tables\Columns\TextColumn::make('identifiedBy.name')
                    ->label('Identified By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('target_completion_date')
                    ->label('Target Completion')
                    ->date('d M Y')
                    ->color(fn (HazardRegister $record): string =>
                        ($record->target_completion_date && $record->target_completion_date->isPast()
                            && !in_array($record->status, ['controlled', 'closed']))
                            ? 'danger' : 'gray'
                    )
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hazard_category')
                    ->label('Hazard Category')
                    ->options(HazardRegister::HAZARD_CATEGORY_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(HazardRegister::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('priority_level')
                    ->label('Priority')
                    ->options([
                        'low'      => 'Low',
                        'medium'   => 'Medium',
                        'high'     => 'High',
                        'critical' => 'Critical',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('high_critical_residual')
                    ->label('High / Critical Residual Risk')
                    ->query(fn (Builder $query) => $query->where('residual_risk_score', '>=', 10))
                    ->toggle(),

                Tables\Filters\Filter::make('overdue_completion')
                    ->label('Overdue (Past Target Date)')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('target_completion_date')
                        ->where('target_completion_date', '<', now())
                        ->whereNotIn('status', ['controlled', 'closed'])
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('open_only')
                    ->label('Open Hazards Only')
                    ->query(fn (Builder $query) => $query->whereNotIn('status', ['controlled', 'closed']))
                    ->toggle(),
            ])
            ->defaultSort('residual_risk_score', 'desc')
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.hira', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('export_docx')
                    ->label('DOCX')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn ($record) => route('docx.hira', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ----------------------------------------------------------------
    // Live risk recalculation helpers
    // ----------------------------------------------------------------

    protected static function recalculateInitialRisk(Forms\Get $get, Forms\Set $set): void
    {
        $set('initial_risk_score', RiskScoringService::score(
            (int) $get('initial_likelihood'),
            (int) $get('initial_severity')
        ));
    }

    protected static function recalculateResidualRisk(Forms\Get $get, Forms\Set $set): void
    {
        $set('residual_risk_score', RiskScoringService::score(
            (int) $get('residual_likelihood'),
            (int) $get('residual_severity')
        ));
    }

    // ----------------------------------------------------------------
    // Relations & Pages
    // ----------------------------------------------------------------

    public static function getRelations(): array
    {
        return [
            RelationManagers\ActionsRelationManager::class,
            RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHazardRegisters::route('/'),
            'create' => Pages\CreateHazardRegister::route('/create'),
            'edit'   => Pages\EditHazardRegister::route('/{record}/edit'),
        ];
    }
}
