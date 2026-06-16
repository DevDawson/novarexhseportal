<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyBaselineResource\Pages;
use App\Models\EnergyBaseline;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnergyBaselineResource extends Resource
{
    protected static ?string $model = EnergyBaseline::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Energy Management (EnMS)';

    protected static ?string $navigationLabel = 'Energy Baselines';

    protected static ?string $modelLabel = 'Energy Baseline';

    protected static ?int $navigationSort = 3;

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
            Forms\Components\Section::make('Baseline Definition')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('energy_source')
                        ->options([
                            'electricity' => 'Electricity', 'diesel' => 'Diesel', 'petrol' => 'Petrol',
                            'natural_gas' => 'Natural Gas', 'lpg' => 'LPG', 'solar' => 'Solar',
                            'biomass' => 'Biomass', 'total' => 'Total (All Sources)',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('total_consumption')->required()->numeric()->minValue(0),
                    Forms\Components\Select::make('unit')
                        ->options(['kWh' => 'kWh', 'MWh' => 'MWh', 'litres' => 'Litres', 'm3' => 'm³', 'GJ' => 'GJ', 'tonnes' => 'Tonnes'])
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('baseline_period_start')->required()->native(false),
                    Forms\Components\DatePicker::make('baseline_period_end')->required()->native(false)->afterOrEqual('baseline_period_start'),

                    Forms\Components\Textarea::make('methodology')->rows(3)->columnSpanFull()->label('Baseline Methodology'),
                    Forms\Components\Textarea::make('adjustment_factors')->rows(3)->columnSpanFull()->label('Adjustment / Normalization Factors'),

                    Forms\Components\Select::make('established_by_id')
                        ->label('Established By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->native(false),

                    Forms\Components\Select::make('approved_by_id')
                        ->label('Approved By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\DatePicker::make('approved_date')->native(false),
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
                    ->colors(['primary' => 'electricity', 'warning' => 'diesel', 'success' => 'solar', 'gray' => 'total']),
                Tables\Columns\TextColumn::make('baseline_period_start')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('baseline_period_end')->date('d M Y'),
                Tables\Columns\TextColumn::make('total_consumption')->suffix(fn ($record) => ' ' . $record->unit)->alignRight(),
                Tables\Columns\TextColumn::make('establishedBy.name')->label('Established By'),
                Tables\Columns\TextColumn::make('approvedBy.name')->label('Approved By')->toggleable(),
                Tables\Columns\TextColumn::make('approved_date')->date('d M Y')->toggleable(),
            ])
            ->defaultSort('baseline_period_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnergyBaselines::route('/'),
            'create' => Pages\CreateEnergyBaseline::route('/create'),
            'edit' => Pages\EditEnergyBaseline::route('/{record}/edit'),
        ];
    }
}
