<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaMitigationResource\Pages;
use App\Models\EsiaMitigationAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EsiaMitigationResource extends Resource
{
    protected static ?string $model = EsiaMitigationAction::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 8: Mitigation (ESMP)';
    protected static ?string $modelLabel = 'Mitigation Action';
    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project & Linked Impact')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required(),

                    Forms\Components\Select::make('impact_id')
                        ->label('Linked Impact (optional)')
                        ->relationship('impact', 'activity')
                        ->getOptionLabelFromRecordUsing(fn ($record) =>
                            "{$record->activity} → {$record->receptor}"
                        )
                        ->searchable()
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Mitigation Action')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('mitigation_type')
                        ->label('Mitigation Type')
                        ->options(EsiaMitigationAction::TYPE_LABELS)
                        ->default('minimize')->required()->native(false),

                    Forms\Components\Select::make('phase')
                        ->label('Project Phase')
                        ->options(EsiaMitigationAction::PHASE_LABELS)
                        ->default('construction')->required()->native(false),

                    Forms\Components\Textarea::make('activity_description')
                        ->label('Action Description')
                        ->rows(3)->required()->columnSpanFull(),

                    Forms\Components\TextInput::make('responsible_party')
                        ->label('Responsible Party')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('monitoring_frequency')
                        ->label('Monitoring Frequency')
                        ->placeholder('e.g. Monthly, Quarterly, After each rain event')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('kpi')
                        ->label('KPI / Success Measure')
                        ->placeholder('e.g. Zero spills, Noise <65dB at boundary')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Timeline & Cost')
                ->columns(3)
                ->schema([
                    Forms\Components\DatePicker::make('timeline_start')
                        ->label('Start Date')->native(false),

                    Forms\Components\DatePicker::make('timeline_end')
                        ->label('End Date')->native(false),

                    Forms\Components\TextInput::make('estimated_cost')
                        ->label('Estimated Cost')
                        ->numeric()->prefix('TZS'),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EsiaMitigationAction::STATUS_LABELS)
                        ->default('planned')->required()->native(false),

                    Forms\Components\DatePicker::make('actual_completion_date')
                        ->label('Actual Completion Date')->native(false),

                    Forms\Components\Textarea::make('completion_notes')
                        ->label('Completion Notes')
                        ->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(25),

                Tables\Columns\BadgeColumn::make('mitigation_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($s) => EsiaMitigationAction::TYPE_LABELS[$s] ?? $s)
                    ->colors([
                        'danger'  => 'avoid',
                        'warning' => 'minimize',
                        'primary' => 'restore',
                        'info'    => 'offset',
                        'success' => 'enhance',
                    ]),

                Tables\Columns\TextColumn::make('activity_description')
                    ->label('Action')->limit(35)->searchable(),

                Tables\Columns\TextColumn::make('responsible_party')
                    ->label('Responsible')->limit(20),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($s) => EsiaMitigationAction::STATUS_LABELS[$s] ?? $s)
                    ->colors([
                        'gray'    => 'planned',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger'  => 'overdue',
                        'warning' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('timeline_end')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->color(fn ($record) =>
                        ($record->is_overdue) ? 'danger' : 'gray'
                    )
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EsiaMitigationAction::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('mitigation_type')
                    ->options(EsiaMitigationAction::TYPE_LABELS),

                Tables\Filters\SelectFilter::make('phase')
                    ->options(EsiaMitigationAction::PHASE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Actions')
                    ->query(fn (Builder $q) => $q
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->where('timeline_end', '<', now())
                    )
                    ->toggle(),
            ])
            ->defaultSort('timeline_end')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaMitigations::route('/'),
            'create' => Pages\CreateEsiaMitigation::route('/create'),
            'edit'   => Pages\EditEsiaMitigation::route('/{record}/edit'),
        ];
    }
}
