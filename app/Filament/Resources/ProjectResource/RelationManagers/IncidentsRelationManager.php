<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Incident;
use App\Services\RiskScoringService;
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
            ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('likelihood')
                    ->label('Likelihood (L)')
                    ->options(RiskScoringService::ratingOptions())
                    ->default(0)
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateRisk($get, $set)),

                Forms\Components\Select::make('impact')
                    ->label('Impact / Severity (I)')
                    ->options(RiskScoringService::ratingOptions())
                    ->default(0)
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateRisk($get, $set)),

                Forms\Components\Placeholder::make('risk_score_preview')
                    ->label('Risk Score (R = L x I)')
                    ->content(function (Forms\Get $get) {
                        $score = RiskScoringService::score((int) $get('likelihood'), (int) $get('impact'));
                        $level = RiskScoringService::level($score);

                        return "{$score} / 25 - ".ucfirst($level);
                    }),

                Forms\Components\Hidden::make('severity')->default('low'),
                Forms\Components\Hidden::make('risk_score')->default(0),
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

                Tables\Columns\TextColumn::make('risk_score')
                    ->label('Risk (LxI)')
                    ->badge()
                    ->formatStateUsing(fn (int $state, Incident $record): string => "{$state}/25 - ".ucfirst($record->risk_level))
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state)),

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

    /**
     * Recalculate the live Risk Score / Level preview and keep the
     * hidden severity + risk_score fields in sync (also recomputed
     * authoritatively in Incident::booted() on save).
     */
    protected static function recalculateRisk(Forms\Get $get, Forms\Set $set): void
    {
        $score = RiskScoringService::score((int) $get('likelihood'), (int) $get('impact'));

        $set('risk_score', $score);
        $set('severity', RiskScoringService::level($score));
    }
}
