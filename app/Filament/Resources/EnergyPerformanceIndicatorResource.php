<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyPerformanceIndicatorResource\Pages;
use App\Models\EnergyPerformanceIndicator;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnergyPerformanceIndicatorResource extends Resource
{
    protected static ?string $model = EnergyPerformanceIndicator::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Energy Management (EnMS)';

    protected static ?string $navigationLabel = 'EnPI';

    protected static ?string $modelLabel = 'Energy Performance Indicator';

    protected static ?string $pluralModelLabel = 'Energy Performance Indicators (EnPI)';

    protected static ?int $navigationSort = 2;

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
            Forms\Components\Section::make('Indicator Definition')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('indicator_name')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
                    Forms\Components\Textarea::make('formula')->rows(2)->label('Calculation Formula')->columnSpanFull(),

                    Forms\Components\TextInput::make('unit_of_measure')->required()->maxLength(50),
                    Forms\Components\Select::make('energy_source')
                        ->options(['electricity' => 'Electricity', 'diesel' => 'Diesel', 'petrol' => 'Petrol', 'natural_gas' => 'Natural Gas', 'lpg' => 'LPG', 'solar' => 'Solar', 'all' => 'All Sources'])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('period')
                        ->options(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual'])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('responsible_id')
                        ->label('Responsible Person')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Performance Values')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('baseline_value')->numeric()->label('Baseline Value'),
                    Forms\Components\TextInput::make('target_value')->numeric()->label('Target Value'),
                    Forms\Components\TextInput::make('current_value')->numeric()->label('Current Value'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('indicator_name')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('unit_of_measure')->label('Unit'),
                Tables\Columns\BadgeColumn::make('energy_source')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['primary' => 'electricity', 'warning' => 'diesel', 'success' => 'solar']),
                Tables\Columns\TextColumn::make('baseline_value')->alignRight(),
                Tables\Columns\TextColumn::make('target_value')->alignRight(),
                Tables\Columns\TextColumn::make('current_value')->alignRight(),
                Tables\Columns\TextColumn::make('period')->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'gray' => 'inactive']),
            ])
            ->defaultSort('indicator_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnergyPerformanceIndicators::route('/'),
            'create' => Pages\CreateEnergyPerformanceIndicator::route('/create'),
            'edit' => Pages\EditEnergyPerformanceIndicator::route('/{record}/edit'),
        ];
    }
}
