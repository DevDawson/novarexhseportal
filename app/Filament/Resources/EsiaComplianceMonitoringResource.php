<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaComplianceMonitoringResource\Pages;
use App\Models\EsiaComplianceMonitoring;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EsiaComplianceMonitoringResource extends Resource
{
    protected static ?string $model = EsiaComplianceMonitoring::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 12: Compliance Monitoring';
    protected static ?string $modelLabel = 'Compliance Monitoring Record';
    protected static ?int $navigationSort = 12;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project & ESMP Link')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('mitigation_id')
                        ->label('Linked ESMP Mitigation Action (optional)')
                        ->relationship('mitigation', 'activity_description')
                        ->searchable()
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('monitoring_type')
                        ->label('Monitoring Type')
                        ->options(EsiaComplianceMonitoring::MONITORING_TYPE_LABELS)
                        ->default('self_monitoring')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('monitoring_frequency')
                        ->label('Monitoring Frequency')
                        ->options(EsiaComplianceMonitoring::FREQUENCY_LABELS)
                        ->default('monthly')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Monitoring Event')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('parameter_monitored')
                        ->label('Parameter / Indicator Monitored')
                        ->placeholder('e.g. Ambient Noise Level, Effluent pH, Dust Fallout, Revegetation cover')
                        ->required()->maxLength(255)->columnSpanFull(),

                    Forms\Components\DatePicker::make('monitoring_date')
                        ->label('Date of Monitoring')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('monitored_by')
                        ->label('Monitored By (Person / Firm)')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('result_value')
                        ->label('Measured Value')
                        ->numeric(),

                    Forms\Components\TextInput::make('result_unit')
                        ->label('Unit')
                        ->placeholder('e.g. dB(A), mg/L, %')
                        ->maxLength(50),

                    Forms\Components\Textarea::make('result_description')
                        ->label('Result Description / Observations')
                        ->rows(3)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Compliance Assessment')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('compliance_status')
                        ->label('Compliance Status')
                        ->options(EsiaComplianceMonitoring::COMPLIANCE_STATUS_LABELS)
                        ->default('not_assessed')
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\DatePicker::make('corrective_action_due')
                        ->label('Corrective Action Due Date')
                        ->native(false)
                        ->visible(fn (Forms\Get $get) => in_array($get('compliance_status'), ['non_compliant', 'partial'])),

                    Forms\Components\Textarea::make('corrective_action')
                        ->label('Required Corrective Action')
                        ->rows(3)->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => in_array($get('compliance_status'), ['non_compliant', 'partial'])),

                    Forms\Components\DatePicker::make('corrective_action_completed')
                        ->label('Corrective Action Completed Date')
                        ->native(false)
                        ->visible(fn (Forms\Get $get) => in_array($get('compliance_status'), ['non_compliant', 'partial'])),
                ]),

            Forms\Components\Section::make('Verification & Evidence')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('verified_by')
                        ->label('Verified By')
                        ->relationship('verifiedBy', 'name')
                        ->searchable()->preload(),

                    Forms\Components\DatePicker::make('verified_at')
                        ->label('Verification Date')->native(false),

                    Forms\Components\FileUpload::make('evidence_file')
                        ->label('Evidence Document / Photo Report')
                        ->directory('esia/monitoring')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes / Recommendations')
                        ->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(25),

                Tables\Columns\TextColumn::make('parameter_monitored')
                    ->label('Parameter')->searchable()->limit(30),

                Tables\Columns\TextColumn::make('monitoring_type')
                    ->label('Type')
                    ->badge()->color('info')
                    ->formatStateUsing(fn (?string $s): string =>
                        EsiaComplianceMonitoring::MONITORING_TYPE_LABELS[$s] ?? ($s ?? '—')
                    ),

                Tables\Columns\TextColumn::make('compliance_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EsiaComplianceMonitoring::COMPLIANCE_STATUS_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EsiaComplianceMonitoring::COMPLIANCE_STATUS_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('result_value')
                    ->label('Result')
                    ->formatStateUsing(fn ($state, $record) =>
                        $state !== null ? "{$state} {$record->result_unit}" : '—'
                    ),

                Tables\Columns\TextColumn::make('monitoring_date')
                    ->label('Date')->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('corrective_action_due')
                    ->label('CA Due')
                    ->date('d M Y')
                    ->color(fn ($record) =>
                        $record->is_overdue ? 'danger' : 'gray'
                    )
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('compliance_status')
                    ->label('Compliance Status')
                    ->options(EsiaComplianceMonitoring::COMPLIANCE_STATUS_LABELS),

                Tables\Filters\SelectFilter::make('monitoring_type')
                    ->options(EsiaComplianceMonitoring::MONITORING_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('monitoring_frequency')
                    ->options(EsiaComplianceMonitoring::FREQUENCY_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('non_compliant')
                    ->label('Non-Compliant Records')
                    ->query(fn ($q) => $q->where('compliance_status', 'non_compliant'))
                    ->toggle(),

                Tables\Filters\Filter::make('overdue_corrections')
                    ->label('Overdue Corrective Actions')
                    ->query(fn (Builder $q) => $q
                        ->where('compliance_status', 'non_compliant')
                        ->whereNotNull('corrective_action_due')
                        ->where('corrective_action_due', '<', now())
                        ->whereNull('corrective_action_completed')
                    )
                    ->toggle(),
            ])
            ->defaultSort('monitoring_date', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaComplianceMonitorings::route('/'),
            'create' => Pages\CreateEsiaComplianceMonitoring::route('/create'),
            'edit'   => Pages\EditEsiaComplianceMonitoring::route('/{record}/edit'),
        ];
    }
}
