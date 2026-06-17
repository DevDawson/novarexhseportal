<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaBaselineDataResource\Pages;
use App\Models\EsiaBaselineData;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaBaselineDataResource extends Resource
{
    protected static ?string $model = EsiaBaselineData::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 4: Baseline Data';
    protected static ?string $modelLabel = 'Baseline Data';
    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project & Parameter')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('parameter_type')
                        ->label('Parameter Type')
                        ->options(EsiaBaselineData::PARAMETER_TYPE_LABELS)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('parameter_subtype', null)),

                    Forms\Components\Select::make('parameter_subtype')
                        ->label('Parameter (Preset)')
                        ->options(fn (Forms\Get $get): array =>
                            EsiaBaselineData::PARAMETER_SUBTYPES[$get('parameter_type')] ?? []
                        )
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $set('parameter_name', $get('parameter_subtype') ?? $get('parameter_name')))
                        ->placeholder('Select from preset list…')
                        ->helperText('Choose from presets or leave blank and enter manually below'),

                    Forms\Components\TextInput::make('parameter_name')
                        ->label('Parameter Name (Custom / Override)')
                        ->placeholder('e.g. PM10, pH, Noise Leq dB(A)')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Measurement')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('sampling_location')
                        ->label('Sampling Location')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('gps_coordinates')
                        ->label('GPS Coordinates')
                        ->placeholder('e.g. -6.1523, 35.7395')
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('measurement_date')
                        ->label('Measurement Date')
                        ->native(false),

                    Forms\Components\TextInput::make('measurement_value')
                        ->label('Measured Value')
                        ->numeric(),

                    Forms\Components\TextInput::make('unit')
                        ->label('Unit')
                        ->placeholder('e.g. mg/m³, dB, mg/L')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('standard_limit')
                        ->label('Regulatory Limit')
                        ->placeholder('e.g. 150 µg/m³')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('standard_reference')
                        ->label('Standard Reference')
                        ->placeholder('e.g. TBS TZS 845, WHO 2021, NEMC 2007')
                        ->maxLength(100),

                    Forms\Components\Toggle::make('exceeds_limit')
                        ->label('Exceeds Regulatory Limit')
                        ->default(false),

                    Forms\Components\Select::make('trend')
                        ->label('Trend vs. Previous')
                        ->options([
                            'improving'  => 'Improving',
                            'stable'     => 'Stable',
                            'worsening'  => 'Worsening',
                            'unknown'    => 'Unknown / First Measurement',
                        ])
                        ->default('unknown')
                        ->native(false),
                ]),

            Forms\Components\Section::make('Source & Notes')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('data_source')
                        ->label('Data Source')
                        ->placeholder('e.g. Field measurement, NEMC report, IoT sensor')
                        ->maxLength(255),

                    Forms\Components\Select::make('recorded_by')
                        ->label('Recorded By')
                        ->relationship('recordedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(28),

                Tables\Columns\BadgeColumn::make('parameter_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($s) => EsiaBaselineData::PARAMETER_TYPE_LABELS[$s] ?? $s)
                    ->color('info'),

                Tables\Columns\TextColumn::make('parameter_name')
                    ->label('Parameter')->searchable()->limit(30),

                Tables\Columns\TextColumn::make('measurement_value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, $record) =>
                        $state !== null ? "{$state} {$record->unit}" : '—'
                    ),

                Tables\Columns\IconColumn::make('exceeds_limit')
                    ->label('Exceeds Limit')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('measurement_date')
                    ->label('Date')->date('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parameter_type')
                    ->options(EsiaBaselineData::PARAMETER_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('exceeds_limit')
                    ->label('Exceeds Limit Only')
                    ->query(fn ($q) => $q->where('exceeds_limit', true))
                    ->toggle(),
            ])
            ->defaultSort('measurement_date', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaBaselineData::route('/'),
            'create' => Pages\CreateEsiaBaselineData::route('/create'),
            'edit'   => Pages\EditEsiaBaselineData::route('/{record}/edit'),
        ];
    }
}
