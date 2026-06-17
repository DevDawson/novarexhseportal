<?php

namespace App\Filament\Resources\InternalAuditResource\RelationManagers;

use App\Models\AuditCapaAction;
use App\Models\AuditNonConformity;
use App\Models\User;
use App\Services\AuditManagementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AmsCapaActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'amsCapaActions';

    protected static ?string $title = 'CAPA Actions';

    protected static ?string $recordTitleAttribute = 'action_number';

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

            Forms\Components\Section::make('CAPA Identification')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('action_number')
                        ->label('CAPA Number')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-assigned on create'),

                    Forms\Components\Select::make('action_type')
                        ->label('Action Type')
                        ->options(AuditCapaAction::ACTION_TYPE_LABELS)
                        ->default('corrective')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('nc_id')
                        ->label('Linked Non-Conformity')
                        ->options(fn () => AuditNonConformity::where('internal_audit_id', $this->getOwnerRecord()->id)
                            ->get()
                            ->mapWithKeys(fn ($nc) => [$nc->id => "[{$nc->nc_number}] {$nc->nc_type} — " . \Illuminate\Support\Str::limit($nc->description, 55)])
                        )
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Action Description')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('root_cause_addressed')
                        ->label('Root Cause Being Addressed')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Assignment & Timeline')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('responsible_person_id')
                        ->label('Responsible Person')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    Forms\Components\TextInput::make('department')
                        ->label('Department')
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('target_date')
                        ->label('Target Completion Date')
                        ->native(false)
                        ->required(),

                    Forms\Components\DatePicker::make('actual_completion_date')
                        ->label('Actual Completion Date')
                        ->native(false),
                ]),

            Forms\Components\Section::make('Status & Verification')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Action Status')
                        ->options(AuditCapaAction::STATUS_LABELS)
                        ->default('open')
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('verification_status')
                        ->label('Verification Status')
                        ->options(AuditCapaAction::VERIFICATION_LABELS)
                        ->default('not_due')
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('evidence_notes')
                        ->label('Evidence of Completion')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('evidence_file')
                        ->label('Supporting Evidence File')
                        ->directory('audits/capa-evidence')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->visible(fn (Forms\Get $g) => in_array($g('status'), ['completed', 'verified'])),

                    Forms\Components\Toggle::make('effectiveness_check')
                        ->label('Effectiveness Confirmed')
                        ->visible(fn (Forms\Get $g) => $g('status') === 'verified'),

                    Forms\Components\Textarea::make('effectiveness_notes')
                        ->label('Effectiveness Review Notes')
                        ->rows(2)
                        ->columnSpanFull()
                        ->visible(fn (Forms\Get $g) => $g('status') === 'verified'),
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
                Tables\Columns\TextColumn::make('action_number')
                    ->label('CAPA #')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('action_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => AuditCapaAction::ACTION_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'corrective',
                        'info'    => 'preventive',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->limit(55)
                    ->wrap(),

                Tables\Columns\TextColumn::make('responsiblePerson.name')
                    ->label('Responsible')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target Date')
                    ->date('d M Y')
                    ->color(fn (AuditCapaAction $record): string =>
                        $record->is_overdue ? 'danger' : 'gray'
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => AuditCapaAction::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => 'in_progress',
                        'primary' => 'completed',
                        'success' => 'verified',
                    ]),

                Tables\Columns\BadgeColumn::make('verification_status')
                    ->label('Verification')
                    ->formatStateUsing(fn (string $state): string => AuditCapaAction::VERIFICATION_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'passed',
                        'danger'  => 'failed',
                        'warning' => 'pending',
                        'gray'    => 'not_due',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action_type')
                    ->options(AuditCapaAction::ACTION_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(AuditCapaAction::STATUS_LABELS),

                Tables\Filters\Filter::make('open_only')
                    ->label('Open Actions Only')
                    ->query(fn ($q) => $q->whereIn('status', ['open', 'in_progress'])),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Actions')
                    ->query(fn ($q) => $q
                        ->where('target_date', '<', now())
                        ->whereNotIn('status', ['completed', 'verified'])
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add CAPA Action')
                    ->mutateFormDataUsing(function (array $data) {
                        $auditId = $this->getOwnerRecord()->id;
                        $data['internal_audit_id'] = $auditId;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('action_number');
    }
}
