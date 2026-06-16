<?php

namespace App\Filament\Resources\PermitToWorkResource\RelationManagers;

use App\Models\PtwIsolationRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IsolationRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'isolationRecords';

    protected static ?string $title = 'LOTO / Isolation Records';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('equipment_tag')
                ->label('Equipment Tag / ID')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('equipment_description')
                ->label('Equipment Description')
                ->maxLength(255),

            Forms\Components\Select::make('isolation_type')
                ->label('Isolation Type')
                ->options(PtwIsolationRecord::ISOLATION_TYPE_LABELS)
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('isolation_point')
                ->label('Isolation Point (Valve / Breaker / Disconnect)')
                ->maxLength(255),

            Forms\Components\Select::make('locked_by_id')
                ->label('Locked / Tagged By')
                ->relationship('lockedBy', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => auth()->id()),

            Forms\Components\TextInput::make('key_number')
                ->label('Lock / Tag Number')
                ->maxLength(100),

            Forms\Components\DateTimePicker::make('lock_applied_at')
                ->label('Lock Applied At')
                ->native(false)
                ->seconds(false)
                ->default(now()),

            Forms\Components\Toggle::make('is_verified')
                ->label('Isolation Verified')
                ->live(),

            Forms\Components\Select::make('verified_by_id')
                ->label('Verified By')
                ->relationship('verifiedBy', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Forms\Get $get) => (bool) $get('is_verified')),

            Forms\Components\DateTimePicker::make('verified_at')
                ->label('Verified At')
                ->native(false)
                ->seconds(false)
                ->visible(fn (Forms\Get $get) => (bool) $get('is_verified')),

            Forms\Components\DateTimePicker::make('released_at')
                ->label('Released At (LOTO Removed)')
                ->native(false)
                ->seconds(false),

            Forms\Components\Select::make('released_by_id')
                ->label('Released By')
                ->relationship('releasedBy', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Forms\Get $get) => (bool) $get('released_at')),

            Forms\Components\Textarea::make('release_notes')
                ->label('Release Notes')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment_tag')
                    ->label('Equipment Tag')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('isolation_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PtwIsolationRecord::ISOLATION_TYPE_LABELS[$state] ?? $state)
                    ->color('info'),

                Tables\Columns\TextColumn::make('isolation_point')
                    ->label('Isolation Point')
                    ->limit(30),

                Tables\Columns\TextColumn::make('lockedBy.name')
                    ->label('Locked By'),

                Tables\Columns\TextColumn::make('key_number')
                    ->label('Lock No.'),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),

                Tables\Columns\TextColumn::make('released_at')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Released' : 'LOTO Active')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
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
