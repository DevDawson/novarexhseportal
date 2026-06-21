<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'monitoringChecks';
    protected static ?string $title = 'Field Monitoring & Verification (Step 13)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('checked_by')
                ->label('Checked By')
                ->relationship('checkedBy', 'name')
                ->searchable()
                ->default(auth()->id()),

            Forms\Components\DateTimePicker::make('checked_at')
                ->label('Check Date & Time')
                ->required()
                ->default(now())
                ->native(false),

            Forms\Components\Section::make('Verification Checklist')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('controls_implemented')
                        ->label('Controls Implemented?')
                        ->helperText('All planned control measures are in place'),

                    Forms\Components\Toggle::make('ppe_available')
                        ->label('PPE Available?')
                        ->helperText('Correct PPE is available and worn by all workers'),

                    Forms\Components\Toggle::make('permit_active')
                        ->label('Permit Active?')
                        ->helperText('Valid PTW or work authorization is in place'),

                    Forms\Components\Toggle::make('workers_briefed')
                        ->label('Workers Briefed?')
                        ->helperText('All workers have received JHA/toolbox talk'),

                    Forms\Components\Toggle::make('emergency_equipment_available')
                        ->label('Emergency Equipment Available?')
                        ->helperText('First aid kit, fire extinguisher, emergency contacts in place'),
                ]),

            Forms\Components\Textarea::make('notes')
                ->label('Observations / Notes')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('checked_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('checked_at')
                    ->label('Check Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('checkedBy.name')
                    ->label('Checked By'),

                Tables\Columns\IconColumn::make('controls_implemented')->boolean()->label('Controls'),
                Tables\Columns\IconColumn::make('ppe_available')->boolean()->label('PPE'),
                Tables\Columns\IconColumn::make('permit_active')->boolean()->label('Permit'),
                Tables\Columns\IconColumn::make('workers_briefed')->boolean()->label('Briefed'),
                Tables\Columns\IconColumn::make('emergency_equipment_available')->boolean()->label('Emergency Equip'),

                Tables\Columns\TextColumn::make('compliance_pct')
                    ->label('Compliance %')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        (float)$state >= 90 => 'success',
                        (float)$state >= 60 => 'warning',
                        default             => 'danger',
                    }),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
