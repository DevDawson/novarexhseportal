<?php

namespace App\Filament\Resources\InternalAuditResource\RelationManagers;

use App\Models\AuditFinding;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'findings';

    protected static ?string $recordTitleAttribute = 'description';

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

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('clause_reference')
                    ->label('Clause Reference')
                    ->placeholder('e.g. ISO 45001 Clause 8.1.1')
                    ->maxLength(100),

                Forms\Components\Select::make('finding_type')
                    ->label('Finding Type')
                    ->options(AuditFinding::FINDING_TYPE_LABELS)
                    ->required()
                    ->native(false)
                    ->live(),
            ]),

            Forms\Components\Textarea::make('description')
                ->label('Finding Description')
                ->rows(3)
                ->required()
                ->columnSpanFull(),

            Forms\Components\Textarea::make('evidence')
                ->label('Evidence / Objective Evidence')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('corrective_action')
                ->label('Corrective / Preventive Action Required')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('responsible_person_id')
                    ->label('Responsible Person')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\DatePicker::make('target_date')
                    ->label('Target Closure Date')
                    ->native(false),
            ]),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(AuditFinding::STATUS_LABELS)
                ->default('open')
                ->required()
                ->native(false)
                ->live(),

            // Verification fields — visible when status is closed or verified
            Forms\Components\Section::make('Verification')
                ->description('Complete this section when verifying the corrective action has been implemented effectively.')
                ->collapsed()
                ->visible(fn (Forms\Get $get) => in_array($get('status'), ['closed', 'verified']))
                ->schema([
                    Forms\Components\Textarea::make('verification_notes')
                        ->label('Verification Notes')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('verification_date')
                        ->label('Date Verified')
                        ->native(false),
                ]),
        ]);
    }

    // ----------------------------------------------------------------
    // Table
    // ----------------------------------------------------------------

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\BadgeColumn::make('finding_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => AuditFinding::FINDING_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'major_nonconformity',
                        'warning' => 'minor_nonconformity',
                        'success' => 'conformity',
                        'info'    => 'opportunity_for_improvement',
                        'gray'    => 'observation',
                    ]),

                Tables\Columns\TextColumn::make('clause_reference')
                    ->label('Clause')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(55)
                    ->searchable(),

                Tables\Columns\TextColumn::make('responsiblePerson.name')
                    ->label('Responsible')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target Date')
                    ->date('d M Y')
                    ->color(fn (AuditFinding $record): string =>
                        ($record->target_date
                            && $record->target_date->isPast()
                            && ! in_array($record->status, ['closed', 'verified']))
                            ? 'danger'
                            : 'gray'
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => AuditFinding::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => 'action_planned',
                        'primary' => 'closed',
                        'success' => 'verified',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('finding_type');
    }
}
