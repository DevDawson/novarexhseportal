<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalAuditResource\Pages;
use App\Filament\Resources\EnvironmentalAuditResource\RelationManagers;
use App\Models\EnvAuditApprovalLog;
use App\Models\EnvironmentalAudit;
use App\Models\User;
use App\Services\EnvironmentalAuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnvironmentalAuditResource extends Resource
{
    protected static ?string $model = EnvironmentalAudit::class;
    protected static ?string $navigationIcon  = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationGroup = 'Environmental Audit';
    protected static ?string $navigationLabel = 'Environmental Audits';
    protected static ?string $modelLabel      = 'Environmental Audit';
    protected static ?int    $navigationSort  = 1;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── 1. Audit Identity ───────────────────────────────────────────
            Forms\Components\Section::make('1. Audit Identification')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('audit_number')
                        ->label('Audit ID')
                        ->placeholder('Auto-generated')
                        ->maxLength(30)
                        ->disabled(fn ($operation) => $operation === 'edit'),

                    Forms\Components\TextInput::make('audit_reference')
                        ->label('Audit Reference No.')
                        ->maxLength(50),

                    Forms\Components\Select::make('audit_type')
                        ->label('Audit Type')
                        ->options(EnvironmentalAudit::AUDIT_TYPE_LABELS)
                        ->default('internal')->required()->native(false),

                    Forms\Components\TextInput::make('audit_title')
                        ->label('Audit Title')
                        ->required()->maxLength(255)->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EnvironmentalAudit::STATUS_LABELS)
                        ->default('planned')->required()->native(false),

                    Forms\Components\Select::make('audit_method')
                        ->label('Audit Method')
                        ->options(EnvironmentalAudit::AUDIT_METHOD_LABELS)
                        ->default('on_site')->required()->native(false),
                ]),

            // ── 2. Scope & Objectives ───────────────────────────────────────
            Forms\Components\Section::make('2. Scope & Objectives')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('scope')
                        ->label('Audit Scope (Processes / Areas / Activities Covered)')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('objectives')
                        ->label('Audit Objectives')
                        ->placeholder('e.g. Compliance, Performance evaluation, Certification readiness')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('criteria')
                        ->label('Audit Criteria')
                        ->placeholder('e.g. ISO 14001:2015 clauses, Legal requirements, Permits, Internal procedures')
                        ->rows(3)->columnSpanFull(),
                ]),

            // ── 3. Planning & Location ──────────────────────────────────────
            Forms\Components\Section::make('3. Audit Planning Information')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('site_location')
                        ->label('Audit Site / Location')
                        ->maxLength(255)->columnSpanFull(),

                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->nullable(),

                    Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->relationship('department', 'name')
                        ->searchable()->preload()->nullable(),

                    Forms\Components\DatePicker::make('audit_date')
                        ->label('Audit Date')->native(false),

                    Forms\Components\DatePicker::make('planned_start_date')
                        ->label('Planned Start Date')->native(false),

                    Forms\Components\DatePicker::make('planned_end_date')
                        ->label('Planned End Date')->native(false),

                    Forms\Components\TextInput::make('audit_duration_days')
                        ->label('Duration (Days)')
                        ->numeric()->default(1)->minValue(1)->suffix('day(s)'),
                ]),

            // ── 4. Audit Team ───────────────────────────────────────────────
            Forms\Components\Section::make('4. Audit Team')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('team_leader_id')
                        ->label('Audit Team Leader')
                        ->relationship('teamLeader', 'name')
                        ->searchable()->preload(),

                    Forms\Components\Select::make('lead_auditor_id')
                        ->label('Lead Auditor')
                        ->relationship('leadAuditor', 'name')
                        ->searchable()->preload(),

                    Forms\Components\Textarea::make('co_auditors')
                        ->label('Co-Auditors')
                        ->placeholder('List names and qualifications')
                        ->rows(2),

                    Forms\Components\Textarea::make('technical_experts')
                        ->label('Technical Experts (if applicable)')
                        ->rows(2),

                    Forms\Components\Textarea::make('auditee_representatives')
                        ->label('Auditee Representative(s)')
                        ->rows(2)->columnSpanFull(),
                ]),

            // ── 5. Summary & Scoring (read-mostly) ─────────────────────────
            Forms\Components\Section::make('5. Audit Summary & Outputs')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('compliance_score')
                        ->label('Compliance Score (%)')
                        ->numeric()->readOnly()
                        ->suffix('%')
                        ->helperText('Auto-computed from checklist assessments'),

                    Forms\Components\Select::make('rating')
                        ->label('Audit Rating')
                        ->options([
                            'excellent' => 'Excellent (90–100%)',
                            'good'      => 'Good (80–89%)',
                            'fair'      => 'Fair (70–79%)',
                            'poor'      => 'Poor (50–69%)',
                            'critical'  => 'Critical (<50%)',
                        ])
                        ->native(false)
                        ->disabled(),

                    Forms\Components\Textarea::make('management_summary')
                        ->label('Management Summary')
                        ->rows(4)->columnSpanFull(),

                    Forms\Components\Textarea::make('closing_notes')
                        ->label('Closing Notes / Auditor Conclusion')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\TextInput::make('total_operating_hours')
                        ->label('Total Operating Hours (for KPI 7)')
                        ->numeric()
                        ->helperText('Used to calculate Environmental Incident Rate (incidents per 100,000 hrs)')
                        ->suffix('hrs'),

                    Forms\Components\Select::make('approved_by')
                        ->label('Initial Approval By')
                        ->relationship('approvedBy', 'name')
                        ->searchable()->preload(),

                    Forms\Components\DateTimePicker::make('approved_at')
                        ->label('Initial Approval Date / Time')->native(false),
                ]),

            // ── 6. Approval Workflow Status (Step 17, read-only display) ──
            Forms\Components\Section::make('6. Approval Workflow Status (Step 17)')
                ->collapsed()
                ->columns(4)
                ->schema([
                    Forms\Components\Placeholder::make('approval_status_display')
                        ->label('Current Approval Status')
                        ->content(fn ($record) => $record
                            ? (EnvironmentalAudit::APPROVAL_STATUS_LABELS[$record->approval_status ?? 'draft'] ?? 'Draft')
                            : 'Draft'
                        ),

                    Forms\Components\Placeholder::make('lead_auditor_signed_display')
                        ->label('Lead Auditor Signed')
                        ->content(fn ($record) => $record?->lead_auditor_signed_at
                            ? ($record->leadAuditorSigner?->name . ' — ' . $record->lead_auditor_signed_at->format('d M Y H:i'))
                            : '—'
                        ),

                    Forms\Components\Placeholder::make('pm_approved_display')
                        ->label('PM Approved')
                        ->content(fn ($record) => $record?->pm_approved_at
                            ? ($record->pmApprover?->name . ' — ' . $record->pm_approved_at->format('d M Y H:i'))
                            : '—'
                        ),

                    Forms\Components\Placeholder::make('client_approved_display')
                        ->label('Client Approved')
                        ->content(fn ($record) => $record?->client_approved_at
                            ? ($record->clientApprover?->name . ' — ' . $record->client_approved_at->format('d M Y H:i'))
                            : '—'
                        ),

                    Forms\Components\Placeholder::make('final_approved_display')
                        ->label('Final Approval')
                        ->content(fn ($record) => $record?->final_approved_at
                            ? ($record->finalApprover?->name . ' — ' . $record->final_approved_at->format('d M Y H:i'))
                            : '—'
                        ),

                    Forms\Components\Placeholder::make('rejection_reason_display')
                        ->label('Rejection Reason')
                        ->content(fn ($record) => $record?->rejection_reason ?? '—')
                        ->columnSpan(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('audit_number')
                    ->label('Audit ID')
                    ->badge()->color('gray')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('audit_title')
                    ->label('Title')->searchable()->limit(35),

                Tables\Columns\TextColumn::make('audit_type')
                    ->label('Type')->badge()->color('primary')
                    ->formatStateUsing(fn (?string $s) =>
                        EnvironmentalAudit::AUDIT_TYPE_LABELS[$s] ?? ($s ?? '—')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')->badge()
                    ->formatStateUsing(fn (?string $s) =>
                        EnvironmentalAudit::STATUS_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state) =>
                        EnvironmentalAudit::STATUS_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('compliance_score')
                    ->label('Score')
                    ->badge()
                    ->formatStateUsing(fn ($s) => $s . '%')
                    ->color(fn ($record) =>
                        EnvironmentalAudit::RATING_COLORS[$record->rating ?? 'poor'] ?? 'gray'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')->badge()
                    ->formatStateUsing(fn (?string $s) => $s ? ucfirst($s) : '—')
                    ->color(fn (?string $state) =>
                        EnvironmentalAudit::RATING_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('audit_date')
                    ->label('Date')->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('findings_count')
                    ->label('Findings')
                    ->counts('findings')
                    ->badge()->color('warning'),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Approval')
                    ->badge()
                    ->formatStateUsing(fn (?string $s) =>
                        EnvironmentalAudit::APPROVAL_STATUS_LABELS[$s ?? 'draft'] ?? 'Draft'
                    )
                    ->color(fn (?string $state) => match ($state) {
                        'final_approved'      => 'success',
                        'client_approved',
                        'pm_approved',
                        'lead_auditor_signed' => 'info',
                        'submitted'           => 'warning',
                        'rejected'            => 'danger',
                        default               => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EnvironmentalAudit::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('audit_type')
                    ->label('Type')
                    ->options(EnvironmentalAudit::AUDIT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        'excellent' => 'Excellent (90–100%)',
                        'good'      => 'Good (80–89%)',
                        'fair'      => 'Fair (70–79%)',
                        'poor'      => 'Poor (50–69%)',
                        'critical'  => 'Critical (<50%)',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('audit_date', 'desc')
            ->actions([
                // ── Step 17 Approval Workflow Actions ──
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->approval_status === 'draft'
                        && auth()->user()?->can('manage esia_audits'))
                    ->action(function ($record) {
                        self::recordApproval($record, 'submitted', 'approved');
                        $record->update(['approval_status' => 'submitted']);
                        Notification::make()->title('Audit submitted for review')->success()->send();
                    }),

                Tables\Actions\Action::make('lead_auditor_sign')
                    ->label('Lead Auditor Sign')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('comments')->label('Lead Auditor Comments')->rows(3),
                    ])
                    ->visible(fn ($record) => $record->approval_status === 'submitted'
                        && auth()->user()?->hasAnyRole(['md', 'hse_manager', 'lead_auditor']))
                    ->action(function ($record, array $data) {
                        self::recordApproval($record, 'lead_auditor_signed', 'approved', $data['comments'] ?? null);
                        $record->update([
                            'approval_status'        => 'lead_auditor_signed',
                            'lead_auditor_signed_by' => auth()->id(),
                            'lead_auditor_signed_at' => now(),
                            'lead_auditor_comments'  => $data['comments'] ?? null,
                        ]);
                        Notification::make()->title('Lead Auditor signature recorded')->success()->send();
                    }),

                Tables\Actions\Action::make('pm_approve')
                    ->label('PM Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('comments')->label('Project Manager Comments')->rows(3),
                    ])
                    ->visible(fn ($record) => $record->approval_status === 'lead_auditor_signed'
                        && auth()->user()?->hasAnyRole(['md', 'business_director']))
                    ->action(function ($record, array $data) {
                        self::recordApproval($record, 'pm_approved', 'approved', $data['comments'] ?? null);
                        $record->update([
                            'approval_status' => 'pm_approved',
                            'pm_approved_by'  => auth()->id(),
                            'pm_approved_at'  => now(),
                            'pm_comments'     => $data['comments'] ?? null,
                        ]);
                        Notification::make()->title('Project Manager approval recorded')->success()->send();
                    }),

                Tables\Actions\Action::make('client_approve')
                    ->label('Client Approve')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('comments')->label('Client Comments')->rows(3),
                    ])
                    ->visible(fn ($record) => $record->approval_status === 'pm_approved'
                        && auth()->user()?->hasAnyRole(['md', 'business_director']))
                    ->action(function ($record, array $data) {
                        self::recordApproval($record, 'client_approved', 'approved', $data['comments'] ?? null);
                        $record->update([
                            'approval_status'    => 'client_approved',
                            'client_approved_by' => auth()->id(),
                            'client_approved_at' => now(),
                            'client_comments'    => $data['comments'] ?? null,
                        ]);
                        Notification::make()->title('Client approval recorded')->success()->send();
                    }),

                Tables\Actions\Action::make('final_approve')
                    ->label('Final Approve')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('comments')->label('Final Approval Comments')->rows(3),
                    ])
                    ->visible(fn ($record) => $record->approval_status === 'client_approved'
                        && auth()->user()?->hasAnyRole(['md']))
                    ->action(function ($record, array $data) {
                        self::recordApproval($record, 'final_approved', 'approved', $data['comments'] ?? null);
                        $record->update([
                            'approval_status'  => 'final_approved',
                            'final_approved_by' => auth()->id(),
                            'final_approved_at' => now(),
                            'final_comments'    => $data['comments'] ?? null,
                            'status'            => 'closed',
                        ]);
                        Notification::make()->title('Audit finally approved and closed')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()->rows(3),
                    ])
                    ->visible(fn ($record) => in_array($record->approval_status, ['submitted', 'lead_auditor_signed', 'pm_approved', 'client_approved'])
                        && auth()->user()?->hasAnyRole(['md', 'hse_manager', 'lead_auditor', 'business_director']))
                    ->action(function ($record, array $data) {
                        self::recordApproval($record, 'rejected', 'rejected', $data['reason']);
                        $record->update([
                            'approval_status'  => 'rejected',
                            'rejection_reason' => $data['reason'],
                        ]);
                        Notification::make()->title('Audit rejected — returned to auditor')->danger()->send();
                    }),

                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.env.audit', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    protected static function recordApproval(
        EnvironmentalAudit $record,
        string $stage,
        string $action,
        ?string $comments = null
    ): void {
        EnvAuditApprovalLog::create([
            'audit_id'       => $record->id,
            'user_id'        => auth()->id(),
            'stage'          => $stage,
            'action'         => $action,
            'comments'       => $comments,
            'signature_text' => auth()->user()->name,
            'signed_at'      => now(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChecklistItemsRelationManager::class,
            RelationManagers\FindingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnvironmentalAudits::route('/'),
            'create' => Pages\CreateEnvironmentalAudit::route('/create'),
            'edit'   => Pages\EditEnvironmentalAudit::route('/{record}/edit'),
        ];
    }
}
