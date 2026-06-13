<?php

namespace App\Filament\Resources\DeliverableResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision History';

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('revision_no')
                    ->required()
                    ->maxLength(50),

                Forms\Components\DateTimePicker::make('revised_at')
                    ->native(false)
                    ->default(now())
                    ->required(),
            ]),

            Forms\Components\FileUpload::make('file_path')
                ->label('Revised Document')
                ->directory('deliverables/revisions')
                ->openable()
                ->required()
                ->columnSpanFull(),

            Forms\Components\Textarea::make('change_description')
                ->label('What changed in this revision?')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Hidden::make('revised_by')
                ->default(fn () => auth()->id()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revision_no')
                    ->label('Rev'),

                Tables\Columns\TextColumn::make('revised_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('revisedBy.name')
                    ->label('Revised By'),

                Tables\Columns\TextColumn::make('change_description')
                    ->limit(50),
            ])
            ->defaultSort('revised_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // Creating a new revision here should also bump the
                    // parent Deliverable's revision_no / file_path - wire
                    // this up via an afterCreate() hook if desired:
                    //
                    // ->after(function (\App\Models\DeliverableRevision $record, RelationManager $livewire) {
                    //     $livewire->getOwnerRecord()->update([
                    //         'revision_no' => $record->revision_no,
                    //         'file_path' => $record->file_path,
                    //     ]);
                    // })
                    ,
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
