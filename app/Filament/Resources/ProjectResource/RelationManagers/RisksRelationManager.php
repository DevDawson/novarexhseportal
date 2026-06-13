<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Risk;
use App\Services\RiskScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RisksRelationManager extends RelationManager
{
    protected static string $relationship = 'risks';

    protected static ?string $recordTitleAttribute = 'risk_title';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage risks') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('risk_title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('category')
                    ->options([
                        'safety' => 'Safety',
                        'environmental' => 'Environmental',
                        'financial' => 'Financial',
                        'operational' => 'Operational',
                        'legal' => 'Legal',
                        'reputational' => 'Reputational',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('likelihood')
                    ->label('Likelihood (L)')
                    ->options(RiskScoringService::ratingOptions())
                    ->default(0)
                    ->required()
                    ->native(false)
                    ->live(),

                Forms\Components\Select::make('severity')
                    ->label('Impact / Severity (I)')
                    ->options(RiskScoringService::ratingOptions())
                    ->default(0)
                    ->required()
                    ->native(false)
                    ->live(),
            ]),

            Forms\Components\Placeholder::make('risk_rating_preview')
                ->label('Risk Score (R = L x I) / Risk Level')
                ->content(function (Forms\Get $get) {
                    $rating = RiskScoringService::score((int) $get('likelihood'), (int) $get('severity'));
                    $level = RiskScoringService::level($rating);

                    return "{$rating} / 25 - ".ucfirst($level);
                }),

            Forms\Components\Textarea::make('mitigation_measures')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('risk_owner_id')
                    ->label('Risk Owner')
                    ->relationship('riskOwner', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'mitigated' => 'Mitigated',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('review_date')
                    ->native(false),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('risk_title')
            ->columns([
                Tables\Columns\TextColumn::make('risk_title')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'danger' => 'safety',
                        'success' => 'environmental',
                        'warning' => ['financial', 'legal'],
                        'gray' => ['operational', 'reputational'],
                    ]),

                Tables\Columns\TextColumn::make('risk_rating')
                    ->label('Risk Score (LxI)')
                    ->badge()
                    ->formatStateUsing(fn (int $state, Risk $record): string => "{$state}/25 - ".ucfirst($record->risk_level))
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('riskOwner.name')
                    ->label('Owner')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'mitigated',
                        'success' => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('review_date')
                    ->date('d M Y')
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('risk_rating', 'desc');
    }
}
