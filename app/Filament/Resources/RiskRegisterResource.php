<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiskRegisterResource\Pages;
use App\Models\Risk;
use App\Models\User;
use App\Services\RiskScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RiskRegisterResource extends Resource
{
    protected static ?string $model = Risk::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Risk Assessment (HAZID)';

    protected static ?string $navigationLabel = 'Risk Register';

    protected static ?string $modelLabel = 'Risk';

    protected static ?string $pluralModelLabel = 'Risk Register';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage risks') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage risks') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage risks') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Risk Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'title')
                        ->label('Project')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('category')
                        ->options([
                            'safety' => 'Safety',
                            'environmental' => 'Environmental',
                            'financial' => 'Financial',
                            'operational' => 'Operational',
                            'legal' => 'Legal / Compliance',
                            'reputational' => 'Reputational',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('risk_title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Risk Scoring (Qualitative)')
                ->columns(3)
                ->description('Qualitative risk score: R = Likelihood × Severity. Score auto-calculated on save.')
                ->schema([
                    Forms\Components\Select::make('likelihood')
                        ->options([1 => '1 – Rare', 2 => '2 – Unlikely', 3 => '3 – Possible', 4 => '4 – Likely', 5 => '5 – Almost Certain'])
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('severity')
                        ->options([1 => '1 – Negligible', 2 => '2 – Minor', 3 => '3 – Moderate', 4 => '4 – Major', 5 => '5 – Catastrophic'])
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Placeholder::make('risk_rating_preview')
                        ->label('Risk Score (L × S)')
                        ->content(fn ($get) => ($get('likelihood') && $get('severity'))
                            ? (($get('likelihood') * $get('severity')) . ' — ' . ucfirst(RiskScoringService::level($get('likelihood') * $get('severity'))))
                            : '—'),
                ]),

            Forms\Components\Section::make('Treatment & Ownership')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('mitigation_measures')
                        ->label('Mitigation / Treatment Measures')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('risk_owner_id')
                        ->label('Risk Owner')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['open' => 'Open', 'mitigated' => 'Mitigated', 'closed' => 'Closed'])
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Next Review Date')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('risk_title')
                    ->label('Risk')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->colors(['danger' => 'safety', 'warning' => 'environmental', 'primary' => 'financial', 'gray' => ['operational', 'legal', 'reputational']]),

                Tables\Columns\TextColumn::make('likelihood')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('severity')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('risk_rating')->label('Score')->sortable()->alignCenter(),

                Tables\Columns\BadgeColumn::make('risk_level')
                    ->label('Level')
                    ->getStateUsing(fn ($record) => $record->risk_level)
                    ->colors(['success' => 'low', 'warning' => 'medium', 'danger' => 'high', 'primary' => 'critical']),

                Tables\Columns\TextColumn::make('riskOwner.name')->label('Risk Owner')->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'open', 'success' => 'mitigated', 'gray' => 'closed']),

                Tables\Columns\TextColumn::make('review_date')->date('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(['safety' => 'Safety', 'environmental' => 'Environmental', 'financial' => 'Financial', 'operational' => 'Operational', 'legal' => 'Legal', 'reputational' => 'Reputational']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['open' => 'Open', 'mitigated' => 'Mitigated', 'closed' => 'Closed']),
            ])
            ->defaultSort('risk_rating', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiskRegisters::route('/'),
            'create' => Pages\CreateRiskRegister::route('/create'),
            'edit' => Pages\EditRiskRegister::route('/{record}/edit'),
        ];
    }
}
