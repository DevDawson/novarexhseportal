<?php

namespace App\Filament\Resources\PermitToWorkResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ToolboxTalksRelationManager extends RelationManager
{
    protected static string $relationship = 'toolboxTalks';

    protected static ?string $title = 'Toolbox Talks';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('conducted_by_id')
                ->label('Conducted By')
                ->relationship('conductedBy', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->default(fn () => auth()->id()),

            Forms\Components\DateTimePicker::make('conducted_at')
                ->label('Date & Time')
                ->native(false)
                ->seconds(false)
                ->required()
                ->default(now()),

            Forms\Components\TextInput::make('number_of_attendees')
                ->label('Number of Attendees')
                ->numeric()
                ->minValue(1)
                ->default(1),

            Forms\Components\Textarea::make('topics_covered')
                ->label('Topics Covered')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('attendees')
                ->label('Attendees (Names / Employee IDs)')
                ->rows(3)
                ->helperText('One name per line')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('summary')
                ->label('Talk Summary')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('safety_concerns_raised')
                ->label('Safety Concerns Raised')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conducted_at')
                    ->label('Date/Time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('conductedBy.name')
                    ->label('Conducted By'),

                Tables\Columns\TextColumn::make('topics_covered')
                    ->label('Topics')
                    ->limit(50),

                Tables\Columns\TextColumn::make('number_of_attendees')
                    ->label('Attendees')
                    ->suffix(' persons'),

                Tables\Columns\TextColumn::make('safety_concerns_raised')
                    ->label('Safety Concerns')
                    ->limit(40)
                    ->placeholder('None raised'),
            ])
            ->defaultSort('conducted_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
