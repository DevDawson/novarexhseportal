<?php

namespace App\Filament\Resources\InternalAuditResource\RelationManagers;

use App\Models\AuditNonConformity;
use App\Models\AuditChecklistItem;
use App\Models\User;
use App\Services\AuditManagementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NonConformitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'nonConformities';

    protected static ?string $title = 'Non-Conformities & RCA';

    protected static ?string $recordTitleAttribute = 'nc_number';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage audits') ?? false;
    }

    // ----------------------------------------------------------------
    // Form
    // ----------------------------------------------------------------

    public function form(Form $form): Form
    {
        return $form->schema([

            // --------------------------------------------------------
            // NC Identification
            // --------------------------------------------------------
            Forms\Components\Section::make('Non-Conformity Identification')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nc_number')
                        ->label('NC Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-assigned on create'),

                    Forms\Components\Select::make('nc_type')
                        ->label('NC Type')
                        ->options(AuditNonConformity::NC_TYPE_LABELS)
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('clause_reference')
                        ->label('Clause Reference')
                        ->placeholder('e.g. ISO 45001:6.1.2')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('department_responsible')
                        ->label('Department Responsible')
                        ->maxLength(100),

                    Forms\Components\Select::make('checklist_item_id')
                        ->label('Linked Checklist Item')
                        ->options(fn () => AuditChecklistItem::where('internal_audit_id', $this->getOwnerRecord()->id)
                            ->get()
                            ->mapWithKeys(fn ($i) => [$i->id => "[{$i->clause_reference}] " . \Illuminate\Support\Str::limit($i->question, 60)])
                        )
                        ->searchable()
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('NC Description')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('objective_evidence')
                        ->label('Objective Evidence')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // Risk Evaluation
            // --------------------------------------------------------
            Forms\Components\Section::make('Risk Evaluation (L × S)')
                ->description('Risk Score = Likelihood × Severity. Low: 1–5 | Medium: 6–12 | High: 13–25')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('likelihood')
                        ->label('Likelihood (1–5)')
                        ->options([
                            1 => '1 — Rare',
                            2 => '2 — Unlikely',
                            3 => '3 — Possible',
                            4 => '4 — Likely',
                            5 => '5 — Almost Certain',
                        ])
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('severity')
                        ->label('Severity (1–5)')
                        ->options([
                            1 => '1 — Negligible',
                            2 => '2 — Minor',
                            3 => '3 — Moderate',
                            4 => '4 — Major',
                            5 => '5 — Critical',
                        ])
                        ->native(false)
                        ->live(),

                    Forms\Components\Placeholder::make('risk_score_preview')
                        ->label('Risk Score')
                        ->content(fn (Forms\Get $g): string =>
                            ($g('likelihood') && $g('severity'))
                                ? 'Score: ' . ((int)$g('likelihood') * (int)$g('severity')) .
                                  ' — ' . strtoupper(AuditManagementService::riskLevel((int)$g('likelihood') * (int)$g('severity')))
                                : 'Enter L & S above'
                        ),
                ]),

            // --------------------------------------------------------
            // Root Cause Analysis
            // --------------------------------------------------------
            Forms\Components\Section::make('Root Cause Analysis (RCA)')
                ->collapsed()
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('rca_method')
                        ->label('RCA Method')
                        ->options(AuditNonConformity::RCA_METHOD_LABELS)
                        ->default('none')
                        ->required()
                        ->native(false)
                        ->live(),

                    // 5 Whys
                    Forms\Components\Section::make('5 Whys Analysis')
                        ->visible(fn (Forms\Get $g) => in_array($g('rca_method'), ['five_whys', 'both']))
                        ->columns(1)
                        ->schema([
                            Forms\Components\Textarea::make('why_1')->label('Why 1 — Why did the problem occur?')->rows(2),
                            Forms\Components\Textarea::make('why_2')->label('Why 2 — Why did that happen?')->rows(2),
                            Forms\Components\Textarea::make('why_3')->label('Why 3 — Why?')->rows(2),
                            Forms\Components\Textarea::make('why_4')->label('Why 4 — Why?')->rows(2),
                            Forms\Components\Textarea::make('why_5')->label('Why 5 — Root cause identified:')->rows(2),
                        ]),

                    // Fishbone
                    Forms\Components\Section::make('Fishbone (Ishikawa) Diagram')
                        ->visible(fn (Forms\Get $g) => in_array($g('rca_method'), ['fishbone', 'both']))
                        ->columns(2)
                        ->schema([
                            Forms\Components\Textarea::make('fishbone_people')->label('People')->rows(2),
                            Forms\Components\Textarea::make('fishbone_process')->label('Process')->rows(2),
                            Forms\Components\Textarea::make('fishbone_equipment')->label('Equipment')->rows(2),
                            Forms\Components\Textarea::make('fishbone_material')->label('Material')->rows(2),
                            Forms\Components\Textarea::make('fishbone_environment')->label('Environment')->rows(2),
                            Forms\Components\Textarea::make('fishbone_management')->label('Management')->rows(2),
                        ]),

                    Forms\Components\Textarea::make('root_cause_summary')
                        ->label('Root Cause Summary')
                        ->rows(3),
                ]),

            // --------------------------------------------------------
            // Action & Status
            // --------------------------------------------------------
            Forms\Components\Section::make('Corrective / Preventive Action & Status')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('corrective_action_proposed')
                        ->label('Corrective Action Proposed')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('preventive_action_proposed')
                        ->label('Preventive Action Proposed')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('NC Status')
                        ->options(AuditNonConformity::STATUS_LABELS)
                        ->default('open')
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('assigned_to_id')
                        ->label('Assigned To')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->native(false),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->visible(fn (Forms\Get $g) => $g('status') === 'closed'),

                    Forms\Components\Toggle::make('effectiveness_verified')
                        ->label('Effectiveness Verified')
                        ->visible(fn (Forms\Get $g) => $g('status') === 'closed'),

                    Forms\Components\Textarea::make('effectiveness_notes')
                        ->label('Effectiveness Notes')
                        ->rows(2)
                        ->columnSpanFull()
                        ->visible(fn (Forms\Get $g) => $g('status') === 'closed'),
                ]),
        ]);
    }

    // ----------------------------------------------------------------
    // Table
    // ----------------------------------------------------------------

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nc_number')
                    ->label('NC #')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('nc_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => AuditNonConformity::NC_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'critical',
                        'warning' => 'major',
                        'info'    => 'minor',
                    ]),

                Tables\Columns\TextColumn::make('clause_reference')
                    ->label('Clause')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(55)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('risk_level')
                    ->label('Risk')
                    ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '—')
                    ->colors([
                        'danger'  => 'high',
                        'warning' => 'medium',
                        'success' => 'low',
                    ]),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->color(fn (AuditNonConformity $record): string =>
                        $record->is_overdue ? 'danger' : 'gray'
                    )
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => AuditNonConformity::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => 'in_progress',
                        'success' => 'closed',
                        'gray'    => 'rejected',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nc_type')
                    ->options(AuditNonConformity::NC_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(AuditNonConformity::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('risk_level')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']),

                Tables\Filters\Filter::make('open_only')
                    ->label('Open NCs Only')
                    ->query(fn ($q) => $q->whereIn('status', ['open', 'in_progress'])),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue NCs')
                    ->query(fn ($q) => $q
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->whereNotIn('status', ['closed', 'rejected'])
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Raise Non-Conformity')
                    ->mutateFormDataUsing(function (array $data) {
                        $auditId = $this->getOwnerRecord()->id;
                        $data['internal_audit_id'] = $auditId;
                        $data['nc_number'] = AuditManagementService::nextNcNumber($auditId);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('nc_number');
    }
}
