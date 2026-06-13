<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenderResource\Pages;
use App\Models\Tender;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationGroup = 'Business Development';

    /**
     * Business Development module: Business Director and MD only.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage tenders') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage tenders') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage tenders') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Tender Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('tender_title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('tender_number')
                        ->label('Tender / Reference No.')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('procuring_entity')
                        ->maxLength(255),

                    Forms\Components\Select::make('client_id')
                        ->label('Client (if registered)')
                        ->relationship('client', 'company_name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('assigned_to')
                        ->label('Assigned To')
                        ->relationship('assignedTo', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Commercial & Timeline')
                ->columns(3)
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('estimated_value')
                                ->label('Estimated Value')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true),

                            Forms\Components\Select::make('currency')
                                ->options([
                                    'TZS' => 'TZS',
                                    'USD' => 'USD',
                                ])
                                ->default('TZS')
                                ->required()
                                ->native(false)
                                ->live(),
                        ]),

                    Forms\Components\TextInput::make('exchange_rate')
                        ->label('Exchange Rate (1 USD = ? TZS)')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('currency') === 'USD')
                        ->helperText('Used to display the equivalent value in the other currency.'),

                    Forms\Components\DatePicker::make('submission_deadline')
                        ->native(false),

                    Forms\Components\Select::make('win_probability')
                        ->label('Win Probability')
                        ->options([
                            10 => '10%',
                            25 => '25%',
                            50 => '50%',
                            75 => '75%',
                            90 => '90%',
                            100 => '100%',
                        ])
                        ->native(false),
                ]),

            Forms\Components\Section::make('Pipeline Status')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('stage')
                        ->label('Stage')
                        ->options([
                            'identified' => 'Identified',
                            'prequalification' => 'Prequalification',
                            'proposal_preparation' => 'Proposal Preparation',
                            'submitted' => 'Submitted',
                            'shortlisted' => 'Shortlisted',
                            'won' => 'Won',
                            'lost' => 'Lost',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default('identified')
                        ->native(false),

                    Forms\Components\Textarea::make('notes')
                        ->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tender_title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('procuring_entity')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('estimated_value')
                    ->label('Value')
                    ->formatStateUsing(fn (Tender $record): string => number_format((float) $record->estimated_value, 2).' '.$record->currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_value_converted')
                    ->label('Converted')
                    ->formatStateUsing(fn (Tender $record): string => number_format($record->estimated_value_converted, 2).' '.($record->currency === 'USD' ? 'TZS' : 'USD'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('submission_deadline')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('win_probability')
                    ->label('Win %')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('stage')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'identified', 'prequalification', 'proposal_preparation', 'shortlisted' => 'Pipeline',
                        'submitted' => 'Submitted',
                        'won' => 'Won',
                        'lost' => 'Lost',
                        'cancelled' => 'Cancelled',
                        default => str($state)->title(),
                    })
                    ->colors([
                        'gray' => ['identified', 'prequalification', 'proposal_preparation', 'shortlisted'],
                        'info' => 'submitted',
                        'success' => 'won',
                        'danger' => ['lost', 'cancelled'],
                    ]),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->options([
                        'identified' => 'Identified',
                        'prequalification' => 'Prequalification',
                        'proposal_preparation' => 'Proposal Preparation',
                        'submitted' => 'Submitted',
                        'shortlisted' => 'Shortlisted',
                        'won' => 'Won',
                        'lost' => 'Lost',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name')
                    ->searchable(),
            ])
            ->defaultSort('submission_deadline', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenders::route('/'),
            'create' => Pages\CreateTender::route('/create'),
            'edit' => Pages\EditTender::route('/{record}/edit'),
        ];
    }
}
