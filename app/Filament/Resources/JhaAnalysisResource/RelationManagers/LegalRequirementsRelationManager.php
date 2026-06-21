<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use App\Models\JhaLegalRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LegalRequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'legalRequirements';
    protected static ?string $title = 'Legal Compliance (Step 10)';
    protected static ?string $recordTitleAttribute = 'legislation';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('legislation')
                ->label('Applicable Legislation / Standard')
                ->options(JhaLegalRequirement::$legislationOptions)
                ->required()
                ->native(false),

            Forms\Components\Textarea::make('requirement_detail')
                ->label('Specific Requirement')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Toggle::make('compliant')
                ->label('Compliant?')
                ->default(false),

            Forms\Components\Textarea::make('notes')
                ->label('Notes / Evidence')
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('legislation')->wrap(),

                Tables\Columns\TextColumn::make('requirement_detail')
                    ->label('Requirement')
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('compliant')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('seedLegal')
                    ->label('Add Standard Requirements')
                    ->icon('heroicon-o-plus-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('This will add all standard Tanzanian / international HSE legal requirements to this JHA.')
                    ->action(function () {
                        $standards = array_keys(JhaLegalRequirement::$legislationOptions);
                        foreach ($standards as $std) {
                            if ($std === 'Other') continue;
                            $this->ownerRecord->legalRequirements()->firstOrCreate(['legislation' => $std]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
