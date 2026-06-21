<?php

namespace App\Filament\Resources\JhaAnalysisResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'Job Steps / Tasks';
    protected static ?string $recordTitleAttribute = 'task_description';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('step_number')
                ->label('Step #')
                ->numeric()
                ->minValue(1)
                ->required(),

            Forms\Components\Textarea::make('task_description')
                ->label('Task / Job Step Description')
                ->required()
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('step_number')
            ->defaultSort('step_number')
            ->columns([
                Tables\Columns\TextColumn::make('step_number')
                    ->label('Step #')
                    ->width(60)
                    ->sortable(),

                Tables\Columns\TextColumn::make('task_description')
                    ->label('Task Description')
                    ->wrap(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
