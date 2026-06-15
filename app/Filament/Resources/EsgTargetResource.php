<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsgTargetResource\Pages;
use App\Models\EsgTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsgTargetResource extends Resource
{
    protected static ?string $model = EsgTarget::class;

    protected static ?string $navigationIcon  = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'ESG';
    protected static ?string $navigationLabel = 'ESG Targets';
    protected static ?string $modelLabel      = 'ESG Target';
    protected static ?int    $navigationSort  = 7;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage esg_targets') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage esg_targets') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage esg_targets') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Target Definition')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('indicator')
                        ->label('Indicator / KPI Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options(EsgTarget::CATEGORY_LABELS)
                        ->required(),

                    Forms\Components\TextInput::make('period')
                        ->label('Period')
                        ->placeholder('e.g. 2026 or 2026-Q2')
                        ->required()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('unit')
                        ->label('Unit')
                        ->required()
                        ->maxLength(30)
                        ->placeholder('e.g. %, tCO2e, USD'),

                    Forms\Components\Select::make('owner_id')
                        ->label('Owner')
                        ->relationship('owner', 'name')
                        ->searchable()
                        ->preload(),
                ]),

            Forms\Components\Section::make('Values & Status')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('baseline_value')
                        ->label('Baseline Value')
                        ->numeric(),

                    Forms\Components\TextInput::make('target_value')
                        ->label('Target Value')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('actual_value')
                        ->label('Actual Value')
                        ->numeric(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EsgTarget::STATUS_LABELS)
                        ->required()
                        ->default('not_started')
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn ($state) => EsgTarget::CATEGORY_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'environmental',
                        'info'    => 'social',
                        'warning' => 'governance',
                    ]),

                Tables\Columns\TextColumn::make('indicator')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('target_value')
                    ->label('Target')
                    ->numeric(2)
                    ->suffix(fn ($record) => ' ' . $record->unit),

                Tables\Columns\TextColumn::make('actual_value')
                    ->label('Actual')
                    ->numeric(2)
                    ->suffix(fn ($record) => $record->actual_value !== null ? ' ' . $record->unit : ''),

                Tables\Columns\TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record->progress_percent !== null
                        ? number_format($record->progress_percent, 1) . '%'
                        : '—')
                    ->color(fn ($record) => match (true) {
                        $record->progress_percent === null    => null,
                        $record->progress_percent >= 100      => 'success',
                        $record->progress_percent >= 75       => 'info',
                        $record->progress_percent >= 50       => 'warning',
                        default                               => 'danger',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => EsgTarget::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'achieved',
                        'info'    => 'on_track',
                        'warning' => 'at_risk',
                        'danger'  => 'off_track',
                        'gray'    => 'not_started',
                    ]),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(EsgTarget::CATEGORY_LABELS),
                Tables\Filters\SelectFilter::make('status')
                    ->options(EsgTarget::STATUS_LABELS),
            ])
            ->defaultSort('period', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsgTargets::route('/'),
            'create' => Pages\CreateEsgTarget::route('/create'),
            'edit'   => Pages\EditEsgTarget::route('/{record}/edit'),
        ];
    }
}
