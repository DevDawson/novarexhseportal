<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use App\Models\JhaEnvironment;
use App\Models\JhaTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EnvironmentRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'Environmental & ESG Screening (Step 9)';

    public function form(Form $form): Form
    {
        $jhaId = $this->ownerRecord->id;

        return $form->schema([
            Forms\Components\Select::make('jha_task_id')
                ->label('Task / Job Step')
                ->options(
                    JhaTask::where('jha_analysis_id', $jhaId)->orderBy('step_number')
                        ->pluck('task_description', 'id')
                )
                ->required()
                ->native(false),

            // Environmental questions
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Toggle::make('waste_generated')->label('Waste Generated?')->live(),
                Forms\Components\Textarea::make('waste_description')->label('Waste Description')->rows(1)
                    ->visible(fn (Forms\Get $get) => (bool) $get('waste_generated')),

                Forms\Components\Toggle::make('air_emissions')->label('Air Emissions?')->live(),
                Forms\Components\Textarea::make('air_description')->label('Air Emissions Description')->rows(1)
                    ->visible(fn (Forms\Get $get) => (bool) $get('air_emissions')),

                Forms\Components\Toggle::make('water_discharge')->label('Water Discharge?')->live(),
                Forms\Components\Textarea::make('water_description')->label('Water Discharge Description')->rows(1)
                    ->visible(fn (Forms\Get $get) => (bool) $get('water_discharge')),

                Forms\Components\Toggle::make('energy_consumption')->label('Significant Energy Consumption?')->live(),
                Forms\Components\Textarea::make('energy_description')->label('Energy Description')->rows(1)
                    ->visible(fn (Forms\Get $get) => (bool) $get('energy_consumption')),

                Forms\Components\Toggle::make('biodiversity_impact')->label('Biodiversity Impact?')->live(),
                Forms\Components\Textarea::make('biodiversity_description')->label('Biodiversity Description')->rows(1)
                    ->visible(fn (Forms\Get $get) => (bool) $get('biodiversity_impact')),

                Forms\Components\Toggle::make('community_impact')->label('Community Impact?')->live(),
                Forms\Components\Textarea::make('community_description')->label('Community Description')->rows(1)
                    ->visible(fn (Forms\Get $get) => (bool) $get('community_impact')),
            ]),

            Forms\Components\Section::make('Environmental Risk Score (Likelihood × Consequence)')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('env_likelihood')
                        ->label('Likelihood')
                        ->options([1=>'1 — Rare',2=>'2 — Unlikely',3=>'3 — Possible',4=>'4 — Likely',5=>'5 — Almost Certain'])
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('env_risk_score', (int)$get('env_likelihood') * (int)$get('env_consequence'))
                        ),

                    Forms\Components\Select::make('env_consequence')
                        ->label('Consequence')
                        ->options([1=>'1 — Negligible',2=>'2 — Minor',3=>'3 — Moderate',4=>'4 — Major',5=>'5 — Catastrophic'])
                        ->default(1)->required()->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                            $set('env_risk_score', (int)$get('env_likelihood') * (int)$get('env_consequence'))
                        ),

                    Forms\Components\TextInput::make('env_risk_score')
                        ->label('Env Risk Score')
                        ->disabled()->dehydrated()->default(1)->suffix('/ 25'),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JhaEnvironment::query()
                    ->whereHas('task', fn ($q) => $q->where('jha_analysis_id', $this->ownerRecord->id))
            )
            ->columns([
                Tables\Columns\TextColumn::make('task.task_description')
                    ->label('Task')->limit(30),

                Tables\Columns\IconColumn::make('waste_generated')->boolean()->label('Waste'),
                Tables\Columns\IconColumn::make('air_emissions')->boolean()->label('Air'),
                Tables\Columns\IconColumn::make('water_discharge')->boolean()->label('Water'),
                Tables\Columns\IconColumn::make('energy_consumption')->boolean()->label('Energy'),
                Tables\Columns\IconColumn::make('biodiversity_impact')->boolean()->label('Biodiversity'),
                Tables\Columns\IconColumn::make('community_impact')->boolean()->label('Community'),

                Tables\Columns\TextColumn::make('env_risk_score')
                    ->label('Env Risk')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 16 => 'danger',
                        $state >= 9  => 'warning',
                        default      => 'success',
                    }),

                Tables\Columns\TextColumn::make('env_risk_level')
                    ->label('Level')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'high'   => 'danger',
                        'medium' => 'warning',
                        default  => 'success',
                    }),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
