<?php

namespace App\Filament\Resources\PermitToWorkResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InspectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'inspections';

    protected static ?string $title = 'Site Inspections';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('inspector_id')
                ->label('Inspector')
                ->relationship('inspector', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->default(fn () => auth()->id()),

            Forms\Components\DateTimePicker::make('inspected_at')
                ->label('Inspection Date/Time')
                ->native(false)
                ->seconds(false)
                ->required()
                ->default(now()),

            Forms\Components\TextInput::make('compliance_score')
                ->label('Compliance Score (0–100)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%')
                ->live(),

            Forms\Components\Toggle::make('is_compliant')
                ->label('Overall Compliant')
                ->default(true),

            Forms\Components\Textarea::make('findings')
                ->label('Inspection Findings')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('corrective_actions')
                ->label('Corrective Actions Required')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inspected_at')
                    ->label('Date/Time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('inspector.name')
                    ->label('Inspector'),

                Tables\Columns\TextColumn::make('compliance_score')
                    ->label('Score')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null  => 'gray',
                        $state >= 80     => 'success',
                        $state >= 60     => 'warning',
                        default          => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_compliant')
                    ->label('Compliant')
                    ->boolean(),

                Tables\Columns\TextColumn::make('findings')
                    ->limit(50),

                Tables\Columns\TextColumn::make('corrective_actions')
                    ->label('Corrective Actions')
                    ->limit(40)
                    ->placeholder('None required'),
            ])
            ->defaultSort('inspected_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
