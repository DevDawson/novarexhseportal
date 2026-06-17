<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalAuditResource\Pages;
use App\Filament\Resources\EnvironmentalAuditResource\RelationManagers;
use App\Models\EnvironmentalAudit;
use App\Models\User;
use App\Services\EnvironmentalAuditService;
use Filament\Forms;
use Filament\Forms\Form;
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
                            'good'      => 'Good (75–89%)',
                            'fair'      => 'Fair (50–74%)',
                            'poor'      => 'Poor (<50%)',
                        ])
                        ->native(false)
                        ->disabled(),

                    Forms\Components\Textarea::make('management_summary')
                        ->label('Management Summary')
                        ->rows(4)->columnSpanFull(),

                    Forms\Components\Textarea::make('closing_notes')
                        ->label('Closing Notes / Auditor Conclusion')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Select::make('approved_by')
                        ->label('Approved By')
                        ->relationship('approvedBy', 'name')
                        ->searchable()->preload(),

                    Forms\Components\DateTimePicker::make('approved_at')
                        ->label('Approval Date / Time')->native(false),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EnvironmentalAudit::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('audit_type')
                    ->label('Type')
                    ->options(EnvironmentalAudit::AUDIT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        'excellent' => 'Excellent',
                        'good'      => 'Good',
                        'fair'      => 'Fair',
                        'poor'      => 'Poor',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('audit_date', 'desc')
            ->actions([
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
