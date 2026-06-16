<?php

namespace App\Filament\Resources\HazardRegisterResource\RelationManagers;

use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $recordTitleAttribute = 'action_description';

    protected static ?string $title = 'Corrective Actions (HAZID_ACTIONS)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('action_description')
                ->label('Action Description')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Select::make('action_owner_id')
                ->label('Action Owner')
                ->options(User::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('department_id')
                ->label('Department')
                ->options(Department::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('priority')
                ->label('Priority')
                ->options([
                    'low'      => 'Low',
                    'medium'   => 'Medium',
                    'high'     => 'High',
                    'critical' => 'Critical',
                ])
                ->default('medium')
                ->required()
                ->native(false),

            Forms\Components\DatePicker::make('due_date')
                ->label('Due Date')
                ->native(false)
                ->required(),

            Forms\Components\Select::make('verification_status')
                ->label('Verification Status')
                ->options([
                    'pending'  => 'Pending',
                    'verified' => 'Verified',
                    'failed'   => 'Failed',
                ])
                ->default('pending')
                ->required()
                ->native(false),

            Forms\Components\Select::make('closure_status')
                ->label('Closure Status')
                ->options([
                    'open'   => 'Open',
                    'closed' => 'Closed',
                ])
                ->default('open')
                ->required()
                ->native(false)
                ->live(),

            Forms\Components\DatePicker::make('completed_date')
                ->label('Completed Date')
                ->native(false)
                ->visible(fn (Forms\Get $get): bool => $get('closure_status') === 'closed'),

            Forms\Components\Textarea::make('completion_notes')
                ->label('Completion Notes')
                ->rows(3)
                ->columnSpanFull()
                ->visible(fn (Forms\Get $get): bool => $get('closure_status') === 'closed'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action_description')
            ->columns([
                Tables\Columns\TextColumn::make('action_description')
                    ->label('Action')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('actionOwner.name')
                    ->label('Owner')
                    ->searchable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priority')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger'  => 'high',
                        'primary' => 'critical',
                    ]),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->color(fn ($record): string =>
                        ($record->closure_status === 'open' && $record->due_date && $record->due_date->isPast())
                            ? 'danger' : 'gray'
                    )
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('verification_status')
                    ->label('Verification')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger'  => 'failed',
                    ]),

                Tables\Columns\BadgeColumn::make('closure_status')
                    ->label('Closure')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'warning' => 'open',
                        'success' => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('completed_date')
                    ->label('Completed')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('closure_status')
                    ->options(['open' => 'Open', 'closed' => 'Closed']),
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options(['pending' => 'Pending', 'verified' => 'Verified', 'failed' => 'Failed']),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical']),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
    }
}
