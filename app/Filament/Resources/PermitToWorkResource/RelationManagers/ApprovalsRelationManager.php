<?php

namespace App\Filament\Resources\PermitToWorkResource\RelationManagers;

use App\Models\PtwApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $title = 'Approval Chain';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('approval_stage')
                ->label('Approval Stage')
                ->options(PtwApproval::STAGE_LABELS)
                ->required()
                ->native(false),

            Forms\Components\Select::make('approver_id')
                ->label('Approver')
                ->relationship('approver', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => auth()->id()),

            Forms\Components\Select::make('decision')
                ->label('Decision')
                ->options(PtwApproval::DECISION_LABELS)
                ->default('pending')
                ->required()
                ->native(false)
                ->live(),

            Forms\Components\Textarea::make('comments')
                ->label('Comments / Conditions')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('decided_at')
                ->label('Decision Date/Time')
                ->native(false)
                ->seconds(false)
                ->visible(fn (Forms\Get $get) => $get('decision') !== 'pending')
                ->default(now()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('approval_stage')
                    ->label('Stage')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PtwApproval::STAGE_LABELS[$state] ?? $state)
                    ->color('info'),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approver'),

                Tables\Columns\TextColumn::make('decision')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PtwApproval::DECISION_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => PtwApproval::DECISION_COLORS[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('comments')
                    ->limit(50),

                Tables\Columns\TextColumn::make('decided_at')
                    ->label('Decided At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Pending'),
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
