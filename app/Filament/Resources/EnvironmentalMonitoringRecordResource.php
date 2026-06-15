<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalMonitoringRecordResource\Pages;
use App\Models\EnvironmentalMonitoringRecord;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnvironmentalMonitoringRecordResource extends Resource
{
    protected static ?string $model = EnvironmentalMonitoringRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Environmental Management';

    protected static ?string $navigationLabel = 'Monitoring Records';

    protected static ?string $modelLabel = 'Monitoring Record';

    protected static ?int $navigationSort = 3;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage environmental_monitoring') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage environmental_monitoring') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage environmental_monitoring') ?? false;
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

            Forms\Components\Section::make('Record Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / office'),

                    Forms\Components\DatePicker::make('record_date')
                        ->label('Record Date')
                        ->native(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('metric_type')
                        ->label('Metric Type')
                        ->options(EnvironmentalMonitoringRecord::METRIC_TYPE_LABELS)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $type = $get('metric_type');
                            $unit = EnvironmentalMonitoringRecord::METRIC_TYPE_UNITS[$type] ?? '';
                            $set('unit', $unit);
                        }),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Unit')
                            ->maxLength(30)
                            ->placeholder('e.g. m³, kWh, litres, kg')
                            ->required(),
                    ]),

                    Forms\Components\Select::make('recorded_by')
                        ->label('Recorded By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->default(fn () => auth()->id())
                        ->searchable()
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes / Source')
                        ->rows(2)
                        ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('record_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('metric_type')
                    ->label('Metric')
                    ->formatStateUsing(fn (string $state): string =>
                        EnvironmentalMonitoringRecord::METRIC_TYPE_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'primary' => ['water_consumption'],
                        'warning' => ['energy_consumption', 'fuel_consumption', 'ghg_emissions'],
                        'gray'    => ['waste_generated_hazardous', 'waste_generated_nonhazardous'],
                        'success' => ['waste_recycled'],
                        'danger'  => ['spills_incidents'],
                    ]),

                Tables\Columns\TextColumn::make('value_with_unit')
                    ->label('Value')
                    ->getStateUsing(fn (EnvironmentalMonitoringRecord $record): string =>
                        number_format((float) $record->value, 2) . ' ' . $record->unit
                    ),

                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metric_type')
                    ->label('Metric')
                    ->options(EnvironmentalMonitoringRecord::METRIC_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->where('record_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->where('record_date', '<=', $data['until']));
                    }),
            ])
            ->defaultSort('record_date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnvironmentalMonitoringRecords::route('/'),
            'create' => Pages\CreateEnvironmentalMonitoringRecord::route('/create'),
            'edit'   => Pages\EditEnvironmentalMonitoringRecord::route('/{record}/edit'),
        ];
    }
}
