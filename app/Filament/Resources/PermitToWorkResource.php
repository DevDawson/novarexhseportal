<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermitToWorkResource\Pages;
use App\Filament\Resources\PermitToWorkResource\RelationManagers;
use App\Models\HazardRegister;
use App\Models\HazopNode;
use App\Models\PermitToWork;
use App\Services\PermitToWorkService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermitToWorkResource extends Resource
{
    protected static ?string $model = PermitToWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Permit to Work (PTW)';

    protected static ?string $navigationLabel = 'Permit to Work';

    protected static ?string $modelLabel = 'Permit to Work';

    protected static ?int $navigationSort = 1;

    // =================================================================
    // Permissions
    // =================================================================

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->check();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if ($record->status === 'draft' && $record->requested_by === $user->id) return true;
        return $user->hasAnyRole(['md', 'hse_staff', 'hse_manager']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    // =================================================================
    // Form
    // =================================================================

    public static function form(Form $form): Form
    {
        return $form->schema([

            // --------------------------------------------------------
            // SECTION 1 — Permit Information
            // --------------------------------------------------------
            Forms\Components\Section::make('1. Permit Information')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('permit_number')
                        ->label('Permit Number')
                        ->default(fn () => PermitToWorkService::nextPermitNumber(now()))
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Forms\Components\Select::make('permit_type')
                        ->label('Permit Type')
                        ->options(PermitToWork::PERMIT_TYPE_LABELS)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (string $state, Set $set) {
                            $set('isolation_required',        PermitToWorkService::requiresIsolationByDefault($state));
                            $set('gas_test_required',         PermitToWorkService::requiresGasTestByDefault($state));
                            $set('fire_watch_required',       PermitToWorkService::requiresFireWatchByDefault($state));
                            $set('barricading_required',      PermitToWorkService::requiresBarricadingByDefault($state));
                            $set('emergency_standby_required',PermitToWorkService::requiresEmergencyStandbyByDefault($state));
                        }),

                    Forms\Components\Select::make('status')
                        ->options(PermitToWork::STATUS_LABELS)
                        ->default('draft')
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\TextInput::make('work_order_id')
                        ->label('Work Order / Job No.')
                        ->maxLength(100)
                        ->placeholder('WO-2024-0001'),

                    Forms\Components\Placeholder::make('risk_badge')
                        ->label('Risk Classification')
                        ->content(function (Get $get): string {
                            $l = (int) $get('likelihood');
                            $s = (int) $get('severity');
                            $score = ($l > 0 && $s > 0) ? $l * $s : 0;
                            $type = $get('permit_type') ?? 'general';
                            $class = $score > 0
                                ? PermitToWorkService::riskClassification($score, $type)
                                : '—';
                            return $score > 0
                                ? strtoupper($class) . " (L{$l} × S{$s} = {$score})"
                                : 'Set Likelihood & Severity below';
                        }),
                ]),

            // --------------------------------------------------------
            // SECTION 2 — Location & Timing
            // --------------------------------------------------------
            Forms\Components\Section::make('2. Location & Timing')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('location')
                        ->label('Work Location / Equipment Tag')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('site_area')
                        ->label('Site Area / Zone')
                        ->maxLength(150)
                        ->placeholder('e.g. Process Area 3, Tank Farm B'),

                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company premises / not project-specific'),

                    Forms\Components\Select::make('department_id')
                        ->label('Responsible Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('duration_estimate')
                        ->label('Estimated Duration (hours)')
                        ->numeric()
                        ->minValue(0.5)
                        ->placeholder('8'),

                    Forms\Components\DateTimePicker::make('valid_from')
                        ->label('Proposed Start')
                        ->native(false)
                        ->seconds(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\DateTimePicker::make('valid_to')
                        ->label('Expected Completion')
                        ->native(false)
                        ->seconds(false)
                        ->default(now()->addHours(8))
                        ->required()
                        ->after('valid_from'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description of Work')
                        ->required()
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 3 — Personnel
            // --------------------------------------------------------
            Forms\Components\Section::make('3. Personnel')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('requested_by')
                        ->label('Permit Holder / Performer')
                        ->relationship('requestedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id())
                        ->required(),

                    Forms\Components\Select::make('supervisor_id')
                        ->label('Work Supervisor')
                        ->relationship('supervisor', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('issued_by')
                        ->label('Issuer / Authorizer')
                        ->relationship('issuedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('area_authority_id')
                        ->label('Area Authority / Safety Officer')
                        ->relationship('areaAuthority', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('contractor_company')
                        ->label('Contractor Company')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('contractor_name')
                        ->label('Contractor Representative')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('number_of_workers')
                        ->label('Number of Workers')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                ]),

            // --------------------------------------------------------
            // SECTION 4 — Risk Assessment
            // --------------------------------------------------------
            Forms\Components\Section::make('4. Risk Assessment')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('linked_hazard_id')
                        ->label('Linked HAZID Register Entry')
                        ->options(
                            HazardRegister::query()
                                ->whereNotIn('status', ['closed', 'cancelled'])
                                ->get()
                                ->mapWithKeys(fn ($h) => [$h->id => "[{$h->hazard_id}] {$h->hazard_description}"])
                        )
                        ->searchable()
                        ->placeholder('Select from HAZID register (optional)'),

                    Forms\Components\Select::make('linked_hazop_node_id')
                        ->label('Linked HAZOP Node')
                        ->options(
                            HazopNode::query()
                                ->with('study')
                                ->whereNotIn('status', ['closed'])
                                ->get()
                                ->mapWithKeys(fn ($n) => [
                                    $n->id => "[{$n->study?->study_ref}] Node {$n->node_number}: {$n->deviation}",
                                ])
                        )
                        ->searchable()
                        ->placeholder('Link to a HAZOP deviation (optional)'),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('likelihood')
                            ->label('Likelihood (L)')
                            ->options([
                                1 => '1 — Rare',
                                2 => '2 — Unlikely',
                                3 => '3 — Possible',
                                4 => '4 — Likely',
                                5 => '5 — Almost Certain',
                            ])
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcRisk($get, $set)),

                        Forms\Components\Select::make('severity')
                            ->label('Severity (S)')
                            ->options([
                                1 => '1 — Negligible',
                                2 => '2 — Minor',
                                3 => '3 — Moderate',
                                4 => '4 — Major',
                                5 => '5 — Catastrophic',
                            ])
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcRisk($get, $set)),

                        Forms\Components\Placeholder::make('risk_score_preview')
                            ->label('Risk Score Preview')
                            ->content(function (Get $get): string {
                                $l = (int) $get('likelihood');
                                $s = (int) $get('severity');
                                if (! $l || ! $s) return '—';
                                $score = $l * $s;
                                $class = PermitToWorkService::riskClassification($score, $get('permit_type') ?? 'general');
                                $level = strtoupper($class);
                                return "Score: {$score} → {$level}";
                            }),
                    ])->columnSpanFull(),

                    Forms\Components\Hidden::make('risk_score'),
                    Forms\Components\Hidden::make('risk_classification'),
                ]),

            // --------------------------------------------------------
            // SECTION 5 — Hazards & Controls
            // --------------------------------------------------------
            Forms\Components\Section::make('5. Hazards & Controls')
                ->schema([
                    Forms\Components\Textarea::make('hazards_identified')
                        ->label('Hazards Identified')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('precautions_taken')
                        ->label('Precautions / Control Measures')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\CheckboxList::make('ppe_required')
                        ->label('PPE Required')
                        ->options(PermitToWorkService::ppeOptions())
                        ->columns(3)
                        ->bulkToggleable()
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 6 — Safety Controls
            // --------------------------------------------------------
            Forms\Components\Section::make('6. Safety Controls & Verification')
                ->columns(2)
                ->schema([
                    // LOTO / Isolation
                    Forms\Components\Toggle::make('isolation_required')
                        ->label('Isolation / LOTO Required')
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('isolation_details')
                        ->label('LOTO Details (Lock/Tag Numbers, Isolation Points)')
                        ->rows(2)
                        ->visible(fn (Get $get) => (bool) $get('isolation_required'))
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('loto_verified')
                        ->label('LOTO / Isolation Verified')
                        ->visible(fn (Get $get) => (bool) $get('isolation_required')),

                    Forms\Components\Placeholder::make('loto_note')
                        ->label('')
                        ->content('Use the LOTO Isolation Records tab below to log each individual isolation point.')
                        ->visible(fn (Get $get) => (bool) $get('isolation_required')),

                    // Gas Testing
                    Forms\Components\Toggle::make('gas_test_required')
                        ->label('Gas Test Required')
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(4)
                        ->visible(fn (Get $get) => (bool) $get('gas_test_required'))
                        ->schema([
                            Forms\Components\TextInput::make('gas_test_results.o2')
                                ->label('O₂ %')
                                ->placeholder('20.9')
                                ->helperText('Safe: 19.5–23.5%'),

                            Forms\Components\TextInput::make('gas_test_results.lel')
                                ->label('LEL %')
                                ->placeholder('0')
                                ->helperText('Safe: < 10%'),

                            Forms\Components\TextInput::make('gas_test_results.h2s')
                                ->label('H₂S (ppm)')
                                ->placeholder('0')
                                ->helperText('Safe: < 10 ppm'),

                            Forms\Components\TextInput::make('gas_test_results.co')
                                ->label('CO (ppm)')
                                ->placeholder('0')
                                ->helperText('Safe: < 35 ppm'),
                        ])->columnSpanFull(),

                    Forms\Components\Grid::make(2)
                        ->visible(fn (Get $get) => (bool) $get('gas_test_required'))
                        ->schema([
                            Forms\Components\TextInput::make('gas_test_results.tested_by')
                                ->label('Tested By'),

                            Forms\Components\DateTimePicker::make('gas_test_results.tested_at')
                                ->label('Test Date/Time')
                                ->native(false)
                                ->seconds(false),
                        ])->columnSpanFull(),

                    Forms\Components\Toggle::make('gas_testing_verified')
                        ->label('Gas Test Results Verified & Safe')
                        ->visible(fn (Get $get) => (bool) $get('gas_test_required')),

                    // Fire watch
                    Forms\Components\Toggle::make('fire_watch_required')
                        ->label('Fire Watch Required')
                        ->live(),

                    Forms\Components\Toggle::make('fire_watch_confirmed')
                        ->label('Fire Watch Confirmed in Place')
                        ->visible(fn (Get $get) => (bool) $get('fire_watch_required')),

                    // Barricading
                    Forms\Components\Toggle::make('barricading_required')
                        ->label('Barricading / Exclusion Zone Required')
                        ->live(),

                    Forms\Components\Toggle::make('barricading_confirmed')
                        ->label('Barricading Confirmed in Place')
                        ->visible(fn (Get $get) => (bool) $get('barricading_required')),

                    // Emergency standby
                    Forms\Components\Toggle::make('emergency_standby_required')
                        ->label('Emergency Standby Required')
                        ->live(),

                    Forms\Components\Toggle::make('emergency_standby_confirmed')
                        ->label('Emergency Standby Confirmed in Place')
                        ->visible(fn (Get $get) => (bool) $get('emergency_standby_required')),

                    // Emergency procedures
                    Forms\Components\Textarea::make('emergency_procedures')
                        ->label('Emergency Procedures / Rescue Plan')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 7 — Permit Checklist
            // --------------------------------------------------------
            Forms\Components\Section::make('7. Permit Checklist')
                ->description('Pre-condition checks to be verified before the permit is issued.')
                ->schema([
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('loadDefaultChecklist')
                            ->label('Load Default Checklist for this Permit Type')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->action(function (Get $get, Set $set) {
                                $type = $get('permit_type');
                                if (! $type) return;

                                $items = collect(PermitToWorkService::defaultChecklistItems($type))
                                    ->values()
                                    ->map(fn ($item, $index) => [
                                        'item'       => $item,
                                        'is_checked' => false,
                                        'remarks'    => null,
                                        'sort_order' => $index,
                                    ])
                                    ->all();

                                $set('checklistItems', $items);
                            }),
                    ]),

                    Forms\Components\Repeater::make('checklistItems')
                        ->relationship('checklistItems')
                        ->label('')
                        ->schema([
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\TextInput::make('item')
                                    ->label('Checklist Item')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Toggle::make('is_checked')
                                    ->label('Verified / OK')
                                    ->inline(false),

                                Forms\Components\TextInput::make('remarks')
                                    ->label('Remarks'),
                            ]),
                        ])
                        ->addActionLabel('Add Checklist Item')
                        ->reorderable(true)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 8 — Workflow & Approval
            // --------------------------------------------------------
            Forms\Components\Section::make('8. Workflow & Approval')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('current_approval_stage')
                        ->label('Current Approval Stage')
                        ->options(PermitToWork::APPROVAL_STAGE_LABELS)
                        ->native(false)
                        ->placeholder('Not yet in approval'),

                    Forms\Components\Select::make('final_approved_by_id')
                        ->label('Final Approved By')
                        ->relationship('finalApprovedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DateTimePicker::make('approved_at')
                        ->label('Final Approval Date/Time')
                        ->native(false)
                        ->seconds(false),
                ]),

            // --------------------------------------------------------
            // SECTION 9 — Closure
            // --------------------------------------------------------
            Forms\Components\Section::make('9. Closure & Completion')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('suspension_reason')
                        ->label('Suspension Reason')
                        ->rows(2)
                        ->visible(fn (Get $get) => $get('status') === 'suspended')
                        ->required(fn (Get $get) => $get('status') === 'suspended')
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('actual_start')
                        ->label('Actual Start Date/Time')
                        ->native(false)
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('actual_completion')
                        ->label('Actual Completion Date/Time')
                        ->native(false)
                        ->seconds(false),

                    Forms\Components\Textarea::make('final_inspection_notes')
                        ->label('Final Site Inspection Notes')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('closeout_notes')
                        ->label('Closeout Notes')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('closeout_by')
                        ->label('Closed Out By')
                        ->relationship('closeoutBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    Forms\Components\DateTimePicker::make('closeout_at')
                        ->label('Closeout Date/Time')
                        ->native(false)
                        ->seconds(false)
                        ->default(now()),

                    Forms\Components\Select::make('completion_confirmed_by_id')
                        ->label('Completion Confirmed By')
                        ->relationship('completionConfirmedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('completion_date')
                        ->label('Completion Date')
                        ->native(false),

                    Forms\Components\Select::make('linked_incident_id')
                        ->label('Linked Incident (if any)')
                        ->relationship('linkedIncident', 'incident_ref')
                        ->searchable()
                        ->placeholder('No incident linked'),
                ]),
        ]);
    }

    // =================================================================
    // Risk score recalculation helper
    // =================================================================

    protected static function recalcRisk(Get $get, Set $set): void
    {
        $l = (int) $get('likelihood');
        $s = (int) $get('severity');
        if ($l > 0 && $s > 0) {
            $score = $l * $s;
            $set('risk_score', $score);
            $set('risk_classification', PermitToWorkService::riskClassification($score, $get('permit_type') ?? 'general'));
        }
    }

    // =================================================================
    // Table
    // =================================================================

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permit_number')
                    ->label('Permit No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('permit_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PermitToWork::PERMIT_TYPE_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'hot_work', 'confined_space', 'electrical_isolation',
                        'pressure_system', 'chemical_handling', 'radiation_work' => 'danger',
                        'working_at_height', 'excavation', 'lifting_operations', 'commissioning' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('risk_classification')
                    ->label('Risk')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? '—'))
                    ->color(fn (?string $state): string => PermitToWork::RISK_CLASSIFICATION_COLORS[$state] ?? 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Valid To')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color(fn (PermitToWork $record): ?string => $record->is_overdue ? 'danger' : null)
                    ->weight(fn (PermitToWork $record): ?string => $record->is_overdue ? 'bold' : null),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('current_approval_stage')
                    ->label('Approval Stage')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PermitToWork::APPROVAL_STAGE_LABELS[$state] ?? ($state ?? '—'))
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PermitToWork::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => PermitToWork::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->defaultSort('valid_from', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('permit_type')
                    ->label('Permit Type')
                    ->options(PermitToWork::PERMIT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(PermitToWork::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('risk_classification')
                    ->label('Risk Level')
                    ->options(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low']),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Closeout')
                    ->query(fn (Builder $query) => $query
                        ->whereIn('status', ['approved', 'active', 'suspended'])
                        ->where('valid_to', '<', now())),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PTW Certificate')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (PermitToWork $record) => route('pdf.ptw.permit', $record))
                    ->openUrlInNewTab()
                    ->hidden(),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (PermitToWork $record) => route('pdf.ptw.permit', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('docx')
                    ->label('DOCX')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (PermitToWork $record) => route('docx.ptw.permit', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // =================================================================
    // Relation Managers
    // =================================================================

    public static function getRelations(): array
    {
        return [
            RelationManagers\ApprovalsRelationManager::class,
            RelationManagers\IsolationRecordsRelationManager::class,
            RelationManagers\ToolboxTalksRelationManager::class,
            RelationManagers\InspectionsRelationManager::class,
        ];
    }

    // =================================================================
    // Pages
    // =================================================================

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPermitToWorks::route('/'),
            'create' => Pages\CreatePermitToWork::route('/create'),
            'view'   => Pages\ViewPermitToWork::route('/{record}'),
            'edit'   => Pages\EditPermitToWork::route('/{record}/edit'),
        ];
    }
}
