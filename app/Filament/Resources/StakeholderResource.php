<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StakeholderResource\Pages;
use App\Models\Stakeholder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StakeholderResource extends Resource
{
    protected static ?string $model = Stakeholder::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?string $navigationLabel = 'Stakeholders';
    protected static ?string $modelLabel      = 'Stakeholder';
    protected static ?int    $navigationSort  = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage stakeholders') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage stakeholders') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage stakeholders') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Stakeholder Identity')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options(Stakeholder::CATEGORY_LABELS)
                        ->required(),

                    Forms\Components\TextInput::make('organisation')
                        ->label('Organisation')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('contact_person')
                        ->label('Contact Person')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(50),
                ]),

            Forms\Components\Section::make('Influence & Engagement')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('influence_level')
                        ->label('Influence Level (1–5)')
                        ->options([1=>'1 – Low', 2=>'2', 3=>'3 – Medium', 4=>'4', 5=>'5 – High'])
                        ->required(),

                    Forms\Components\Select::make('interest_level')
                        ->label('Interest Level (1–5)')
                        ->options([1=>'1 – Low', 2=>'2', 3=>'3 – Medium', 4=>'4', 5=>'5 – High'])
                        ->required(),

                    Forms\Components\Select::make('engagement_strategy')
                        ->label('Engagement Strategy')
                        ->options(Stakeholder::STRATEGY_LABELS)
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn ($state) => Stakeholder::CATEGORY_LABELS[$state] ?? $state)
                    ->colors([
                        'primary'   => 'community',
                        'success'   => 'government',
                        'warning'   => 'ngo',
                        'info'      => 'client',
                        'gray'      => fn ($state) => in_array($state, ['supplier', 'employee', 'media', 'investor', 'other']),
                    ]),

                Tables\Columns\TextColumn::make('organisation')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('influence_level')
                    ->label('Influence')
                    ->sortable(),

                Tables\Columns\TextColumn::make('interest_level')
                    ->label('Interest')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('engagement_strategy')
                    ->label('Strategy')
                    ->formatStateUsing(fn ($state) => Stakeholder::STRATEGY_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'manage_closely',
                        'warning' => 'keep_satisfied',
                        'info'    => 'keep_informed',
                        'gray'    => 'monitor',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('engagements_count')
                    ->label('Engagements')
                    ->counts('engagements')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(Stakeholder::CATEGORY_LABELS),
                Tables\Filters\SelectFilter::make('engagement_strategy')
                    ->options(Stakeholder::STRATEGY_LABELS),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStakeholders::route('/'),
            'create' => Pages\CreateStakeholder::route('/create'),
            'edit'   => Pages\EditStakeholder::route('/{record}/edit'),
        ];
    }
}
