<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyActionPlanResource\Pages;
use App\Models\EnergyActionPlan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnergyActionPlanResource extends Resource
{
    protected static ?string $model = EnergyActionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Energy Management (EnMS)';

    protected static ?string $navigationLabel = 'Energy Action Plans';

    protected static ?string $modelLabel = 'Energy Action Plan';

    protected static ?int $navigationSort = 4;

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
            Forms\Components\Section::make('Action Plan Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->required()->rows(3)->columnSpanFull(),

                    Forms\Components\Select::make('opportunity_type')
                        ->options([
                            'efficiency_improvement' => 'Efficiency Improvement',
                            'renewable_energy' => 'Renewable Energy',
                            'behavioral_change' => 'Behavioral Change',
                            'technology_upgrade' => 'Technology Upgrade',
                            'process_optimization' => 'Process Optimization',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('energy_source_affected')
                        ->options(['electricity' => 'Electricity', 'diesel' => 'Diesel', 'petrol' => 'Petrol', 'natural_gas' => 'Natural Gas', 'lpg' => 'LPG', 'solar' => 'Solar', 'all' => 'All Sources'])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('expected_saving_quantity')->numeric()->label('Expected Saving (Quantity)'),
                    Forms\Components\TextInput::make('expected_saving_unit')->label('Expected Saving Unit')->placeholder('e.g. kWh, litres'),
                    Forms\Components\TextInput::make('expected_cost')->numeric()->prefix('TZS')->label('Estimated Implementation Cost'),
                    Forms\Components\TextInput::make('actual_saving')->numeric()->label('Actual Saving Achieved'),

                    Forms\Components\Select::make('assigned_to_id')
                        ->label('Assigned To')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('approved_by_id')
                        ->label('Approved By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\DatePicker::make('target_date')->required()->native(false),
                    Forms\Components\DatePicker::make('completion_date')->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['proposed' => 'Proposed', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(35),
                Tables\Columns\BadgeColumn::make('opportunity_type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['primary' => 'efficiency_improvement', 'success' => 'renewable_energy', 'warning' => 'technology_upgrade']),
                Tables\Columns\TextColumn::make('energy_source_affected')->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))->toggleable(),
                Tables\Columns\TextColumn::make('expected_saving_quantity')->suffix(fn ($record) => ' ' . $record->expected_saving_unit)->alignRight()->toggleable(),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To'),
                Tables\Columns\TextColumn::make('target_date')->date('d M Y')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['gray' => 'proposed', 'primary' => 'approved', 'warning' => 'in_progress', 'success' => 'completed', 'danger' => 'cancelled'])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['proposed' => 'Proposed', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            ])
            ->defaultSort('target_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnergyActionPlans::route('/'),
            'create' => Pages\CreateEnergyActionPlan::route('/create'),
            'edit' => Pages\EditEnergyActionPlan::route('/{record}/edit'),
        ];
    }
}
