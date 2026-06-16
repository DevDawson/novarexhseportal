<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyConsumptionResource\Pages;
use App\Models\EnergyConsumptionRecord;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnergyConsumptionResource extends Resource
{
    protected static ?string $model = EnergyConsumptionRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Energy Management (EnMS)';

    protected static ?string $navigationLabel = 'Consumption Records';

    protected static ?string $modelLabel = 'Energy Consumption Record';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage energy') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage energy') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage energy') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Energy Source & Period')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('energy_source')
                        ->options([
                            'electricity' => 'Electricity',
                            'diesel' => 'Diesel',
                            'petrol' => 'Petrol',
                            'natural_gas' => 'Natural Gas',
                            'lpg' => 'LPG',
                            'solar' => 'Solar',
                            'biomass' => 'Biomass',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('facility')
                        ->label('Facility / Site')
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('period_start')
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('period_end')
                        ->required()
                        ->native(false)
                        ->afterOrEqual('period_start'),

                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Consumption & Cost')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\Select::make('unit')
                        ->options(['kWh' => 'kWh', 'MWh' => 'MWh', 'litres' => 'Litres', 'm3' => 'm³', 'GJ' => 'GJ', 'tonnes' => 'Tonnes'])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('meter_reading_start')->label('Meter Reading (Start)'),
                    Forms\Components\TextInput::make('meter_reading_end')->label('Meter Reading (End)'),

                    Forms\Components\TextInput::make('cost')->numeric()->minValue(0)->prefix('TZS'),
                    Forms\Components\TextInput::make('currency')->default('TZS')->maxLength(10),

                    Forms\Components\Select::make('recorded_by_id')
                        ->label('Recorded By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->native(false),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('energy_source')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['primary' => 'electricity', 'warning' => ['diesel', 'petrol'], 'success' => 'solar', 'gray' => 'other']),
                Tables\Columns\TextColumn::make('period_start')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('period_end')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('quantity')->suffix(fn ($record) => ' ' . $record->unit)->alignRight(),
                Tables\Columns\TextColumn::make('cost')->money('TZS')->alignRight()->toggleable(),
                Tables\Columns\TextColumn::make('facility')->toggleable(),
                Tables\Columns\TextColumn::make('project.name')->toggleable(),
                Tables\Columns\TextColumn::make('recordedBy.name')->label('Recorded By')->toggleable(),
            ])
            ->defaultSort('period_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnergyConsumptionRecords::route('/'),
            'create' => Pages\CreateEnergyConsumptionRecord::route('/create'),
            'edit' => Pages\EditEnergyConsumptionRecord::route('/{record}/edit'),
        ];
    }
}
