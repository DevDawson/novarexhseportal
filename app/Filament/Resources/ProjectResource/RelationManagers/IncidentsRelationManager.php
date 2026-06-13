<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IncidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'incidents';

    protected static ?string $recordTitleAttribute = 'description';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Hidden::make('reported_by')
                ->default(fn () => auth()->id()),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DateTimePicker::make('incident_date')
                    ->native(false)
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('incident_type')
                    ->options([
                        'near_miss' => 'Near Miss',
                        'first_aid' => 'First Aid Case',
                        'medical_treatment' => 'Medical Treatment Case',
                        'lost_time' => 'Lost Time Injury',
                        'fatality' => 'Fatality',
                        'environmental' => 'Environmental Incident',
                        'property_damage' => 'Property Damage',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ])
                    ->required()
                    ->native(false),
            ]),

            Forms\Components\Textarea::make('description')
                ->label('What happened?')
                ->rows(3)
                ->required()
                ->columnSpanFull(),

            Forms\Components\Textarea::make('immediate_action')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'investigating' => 'Investigating',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->required()
                    ->native(false)
                    ->live(),

                Forms\Components\DatePicker::make('closed_date')
                    ->native(false)
                    ->visible(fn (Forms\Get $get) => $get('status') === 'closed'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location'),

                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => ['high', 'critical'],
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'investigating',
                        'success' => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->limit(40),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
