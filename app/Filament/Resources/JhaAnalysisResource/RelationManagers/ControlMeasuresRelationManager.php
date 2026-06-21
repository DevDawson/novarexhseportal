<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use App\Models\JhaControlMeasure;
use App\Models\JhaHazard;
use App\Models\JhaTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ControlMeasuresRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'Control Measures — Hierarchy of Controls (Step 7)';

    public function form(Form $form): Form
    {
        $jhaId    = $this->ownerRecord->id;
        $hazardOptions = JhaHazard::query()
            ->whereHas('task', fn ($q) => $q->where('jha_analysis_id', $jhaId))
            ->with('task')
            ->get()
            ->mapWithKeys(fn ($h) => [
                $h->id => "Step {$h->task->step_number}: {$h->hazard_description} [{$h->initial_risk_level}]",
            ]);

        return $form->schema([
            Forms\Components\Select::make('jha_hazard_id')
                ->label('Hazard')
                ->options($hazardOptions)
                ->required()
                ->native(false),

            Forms\Components\Select::make('hierarchy_level')
                ->label('Hierarchy Level (Step 7)')
                ->options(JhaControlMeasure::$hierarchyLabels)
                ->required()
                ->native(false)
                ->live()
                ->helperText(fn (Forms\Get $get) =>
                    JhaControlMeasure::$hierarchyExamples[$get('hierarchy_level') ?? 1] ?? ''
                ),

            Forms\Components\Textarea::make('description')
                ->label('Control Measure Description')
                ->required()
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('responsible_person')
                ->label('Responsible Person'),

            Forms\Components\Select::make('status')
                ->label('Implementation Status')
                ->options([
                    'planned'     => 'Planned',
                    'implemented' => 'Implemented',
                    'verified'    => 'Verified',
                ])
                ->default('planned')
                ->native(false)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JhaControlMeasure::query()
                    ->whereHas('hazard.task', fn ($q) => $q->where('jha_analysis_id', $this->ownerRecord->id))
            )
            ->defaultSort('hierarchy_level')
            ->columns([
                Tables\Columns\TextColumn::make('hierarchy_level')
                    ->label('Level')
                    ->formatStateUsing(fn ($state) => JhaControlMeasure::$hierarchyLabels[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        1 => 'success',
                        2 => 'primary',
                        3 => 'info',
                        4 => 'warning',
                        5 => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('hazard.hazard_description')
                    ->label('Hazard')
                    ->limit(30),

                Tables\Columns\TextColumn::make('description')
                    ->label('Control Measure')
                    ->limit(45),

                Tables\Columns\TextColumn::make('responsible_person')
                    ->label('Responsible')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'verified'    => 'success',
                        'implemented' => 'primary',
                        default       => 'gray',
                    }),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
