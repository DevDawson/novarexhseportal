<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use App\Models\JhaCompetencyRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CompetencyRequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'competencyRequirements';
    protected static ?string $title = 'Competency Verification (Step 11)';
    protected static ?string $recordTitleAttribute = 'competency_type';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('competency_type')
                ->label('Competency / Certificate Required')
                ->options(JhaCompetencyRequirement::$competencyTypes)
                ->required()
                ->native(false),

            Forms\Components\Textarea::make('description')
                ->label('Description / Scope')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('required_workers')
                ->label('Required Workers')
                ->numeric()->minValue(0)->default(1)
                ->live(debounce: 400)
                ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                    $set('compliance_pct',
                        (int)$get('required_workers') > 0
                            ? round((int)$get('qualified_workers') / (int)$get('required_workers') * 100, 2)
                            : 0
                    )
                ),

            Forms\Components\TextInput::make('qualified_workers')
                ->label('Qualified Workers')
                ->numeric()->minValue(0)->default(0)
                ->live(debounce: 400)
                ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) =>
                    $set('compliance_pct',
                        (int)$get('required_workers') > 0
                            ? round((int)$get('qualified_workers') / (int)$get('required_workers') * 100, 2)
                            : 0
                    )
                ),

            Forms\Components\TextInput::make('compliance_pct')
                ->label('Compliance %')
                ->disabled()->dehydrated()->suffix('%'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('competency_type')
                    ->label('Competency / Certificate'),

                Tables\Columns\TextColumn::make('required_workers')
                    ->label('Required'),

                Tables\Columns\TextColumn::make('qualified_workers')
                    ->label('Qualified'),

                Tables\Columns\TextColumn::make('compliance_pct')
                    ->label('Compliance %')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        (float)$state >= 90 => 'success',
                        (float)$state >= 70 => 'warning',
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
