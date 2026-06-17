<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialIndicatorResource\Pages;
use App\Models\SocialIndicator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialIndicatorResource extends Resource
{
    protected static ?string $model = SocialIndicator::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?string $navigationLabel = 'Social Indicators';
    protected static ?string $modelLabel      = 'Social Indicator';
    protected static ?int    $navigationSort  = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage social_indicators') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage social_indicators') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage social_indicators') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Social Performance Data')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('indicator_type')
                        ->label('Indicator')
                        ->options(SocialIndicator::INDICATOR_LABELS)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            $unit = SocialIndicator::INDICATOR_DEFAULT_UNITS[$state] ?? null;
                            if ($unit) {
                                $set('unit', $unit);
                            }
                        }),

                    Forms\Components\TextInput::make('period')
                        ->label('Period')
                        ->placeholder('e.g. 2026-Q1 or 2026-06')
                        ->required()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('value')
                        ->label('Value')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('unit')
                        ->label('Unit')
                        ->maxLength(30)
                        ->placeholder('Auto-filled from indicator'),

                    Forms\Components\Select::make('recorded_by')
                        ->label('Recorded By')
                        ->relationship('recordedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

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

                Tables\Columns\TextColumn::make('indicator_type')
                    ->label('Indicator')
                    ->formatStateUsing(fn ($state) => SocialIndicator::INDICATOR_LABELS[$state] ?? $state)
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit'),

                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('indicator_type')
                    ->label('Indicator')
                    ->options(SocialIndicator::INDICATOR_LABELS),
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
            'index'  => Pages\ListSocialIndicators::route('/'),
            'create' => Pages\CreateSocialIndicator::route('/create'),
            'edit'   => Pages\EditSocialIndicator::route('/{record}/edit'),
        ];
    }
}
