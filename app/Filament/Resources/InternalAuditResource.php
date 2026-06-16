<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternalAuditResource\Pages;
use App\Filament\Resources\InternalAuditResource\RelationManagers\FindingsRelationManager;
use App\Models\AuditFinding;
use App\Models\Department;
use App\Models\InternalAudit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternalAuditResource extends Resource
{
    protected static ?string $model = InternalAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'HSE & Technical Operations';

    protected static ?string $navigationLabel = 'Internal Audits';

    protected static ?string $modelLabel = 'Internal Audit';

    protected static ?int $navigationSort = 5;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage audits') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage audits') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage audits') ?? false;
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

            // --------------------------------------------------------
            // SECTION 1: Audit Details
            // --------------------------------------------------------
            Forms\Components\Section::make('Audit Details')
                ->description('Core audit identification and scheduling information.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('audit_reference')
                        ->label('Audit Reference')
                        ->placeholder('Auto-generated on save (e.g. AUD-2026-06-0001)')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('audit_type')
                        ->label('Audit Type')
                        ->options(InternalAudit::AUDIT_TYPE_LABELS)
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('standard')
                        ->label('Standard / Framework')
                        ->options(InternalAudit::STANDARD_LABELS)
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\TextInput::make('standard_other')
                        ->label('Specify Standard / Framework')
                        ->placeholder('e.g. OSHA 18001, Client HSE Manual...')
                        ->visible(fn (Forms\Get $get) => $get('standard') === 'other')
                        ->required(fn (Forms\Get $get) => $get('standard') === 'other')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('scope')
                        ->label('Audit Scope')
                        ->helperText('What processes, areas, or clauses are being audited?')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Not project-specific'),

                    Forms\Components\Select::make('department_id')
                        ->label('Department (if applicable)')
                        ->options(Department::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Not department-specific')
                        ->nullable(),

                    Forms\Components\DatePicker::make('audit_date')
                        ->label('Audit Date')
                        ->native(false)
                        ->required(),

                    Forms\Components\Select::make('lead_auditor_id')
                        ->label('Lead Auditor')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(InternalAudit::STATUS_LABELS)
                        ->default('planned')
                        ->required()
                        ->native(false),
                ]),

            // --------------------------------------------------------
            // SECTION 1b: Planning Details (ISO 19011 §6.2)
            // --------------------------------------------------------
            Forms\Components\Section::make('Audit Planning')
                ->description('Define objectives, criteria, and planned audit dates before execution.')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('audit_objectives')
                        ->label('Audit Objectives')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('audit_criteria')
                        ->label('Audit Criteria')
                        ->helperText('Standards, procedures, or requirements the audit is measured against.')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('planned_start_date')
                        ->label('Planned Start Date')
                        ->native(false),

                    Forms\Components\DatePicker::make('planned_end_date')
                        ->label('Planned End Date')
                        ->native(false)
                        ->afterOrEqual('planned_start_date'),
                ]),

            // --------------------------------------------------------
            // SECTION 2: Audit Team
            // --------------------------------------------------------
            Forms\Components\Section::make('Audit Team')
                ->description('Select additional Internal Auditors on this team. The Lead Auditor above is NOT duplicated here.')
                ->schema([
                    Forms\Components\Select::make('teamMembers')
                        ->label('Team Members (Internal Auditors)')
                        ->relationship('teamMembers', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 3: Execution Notes
            // --------------------------------------------------------
            Forms\Components\Section::make('Audit Execution')
                ->description('Notes from opening and closing meetings recorded during audit execution.')
                ->schema([
                    Forms\Components\Textarea::make('opening_meeting_notes')
                        ->label('Opening Meeting Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('closing_meeting_notes')
                        ->label('Closing Meeting Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 4: Summary & Report
            // --------------------------------------------------------
            Forms\Components\Section::make('Summary & Report')
                ->description('Overall audit conclusion and supporting report file.')
                ->schema([
                    Forms\Components\Textarea::make('summary')
                        ->label('Audit Summary / Conclusion')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('report_file')
                        ->label('Audit Report (PDF / Word)')
                        ->directory('audits/reports')
                        ->acceptedFileTypes(['application/pdf', 'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->openable()
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 5: Closure Verification (ISO 19011 §6.6)
            // --------------------------------------------------------
            Forms\Components\Section::make('Closure Verification')
                ->description('Confirm all findings are resolved and the audit is formally closed.')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('closure_verification_notes')
                        ->label('Closure Verification Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('closure_verified_by_id')
                        ->label('Closure Verified By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    Forms\Components\DatePicker::make('closure_date')
                        ->label('Closure Date')
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
                Tables\Columns\TextColumn::make('audit_reference')
                    ->label('Reference')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('audit_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => InternalAudit::AUDIT_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'primary' => 'internal',
                        'info'    => 'surveillance',
                        'warning' => 'external',
                        'danger'  => 'certification',
                        'gray'    => 'supplier',
                    ]),

                Tables\Columns\TextColumn::make('standard')
                    ->label('Standard')
                    ->formatStateUsing(function (string $state, InternalAudit $record): string {
                        if ($state === 'other') {
                            return $record->standard_other ?? 'Other';
                        }

                        return InternalAudit::STANDARD_LABELS[$state] ?? $state;
                    })
                    ->limit(30),

                Tables\Columns\TextColumn::make('audit_date')
                    ->label('Audit Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('leadAuditor.name')
                    ->label('Lead Auditor'),

                Tables\Columns\TextColumn::make('findings_summary')
                    ->label('Findings')
                    ->getStateUsing(function (InternalAudit $record): string {
                        $total = $record->findings()->count();
                        $nc    = $record->non_conformity_count;

                        if ($total === 0) {
                            return 'No findings';
                        }

                        return $nc > 0
                            ? "{$total} findings ({$nc} NC)"
                            : "{$total} findings";
                    })
                    ->color(fn (InternalAudit $record): string =>
                        $record->non_conformity_count > 0 ? 'danger' : 'success'
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => InternalAudit::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'gray'    => 'planned',
                        'warning' => 'in_progress',
                        'primary' => 'completed',
                        'success' => 'closed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('audit_type')
                    ->label('Audit Type')
                    ->options(InternalAudit::AUDIT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('standard')
                    ->options(InternalAudit::STANDARD_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(InternalAudit::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('lead_auditor_id')
                    ->label('Lead Auditor')
                    ->relationship('leadAuditor', 'name')
                    ->searchable(),
            ])
            ->defaultSort('audit_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.audit', $record))
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
    // Relations & Pages
    // ----------------------------------------------------------------

    public static function getRelations(): array
    {
        return [
            FindingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInternalAudits::route('/'),
            'create' => Pages\CreateInternalAudit::route('/create'),
            'edit'   => Pages\EditInternalAudit::route('/{record}/edit'),
        ];
    }
}
