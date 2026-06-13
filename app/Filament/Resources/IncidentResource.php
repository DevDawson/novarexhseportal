<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentResource\Pages;
use App\Models\Incident;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'HSE & Technical Operations';

    protected static ?string $modelLabel = 'Incident Report';

    /**
     * HSE Staff manage incidents day-to-day. MD has oversight access.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    /**
     * Only MD or HSE Staff may delete an incident record (audit trail integrity).
     */
    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Incident Report')
                ->description('Log new safety, environmental, or security incidents as soon as possible.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / not project specific'),

                    Forms\Components\Hidden::make('reported_by')
                        ->default(fn () => auth()->id()),

                    Forms\Components\DateTimePicker::make('incident_date')
                        ->label('Date & Time of Incident')
                        ->native(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('location')
                        ->label('Location / Site')
                        ->maxLength(255)
                        ->required(),

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

                    Forms\Components\Radio::make('severity')
                        ->label('Risk / Severity Level')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'critical' => 'Critical',
                        ])
                        ->inline()
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label('What happened?')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Investigation & Corrective Action')
                ->collapsible()
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('immediate_action')
                        ->label('Immediate Action Taken')
                        ->rows(2),

                    Forms\Components\Textarea::make('root_cause')
                        ->label('Root Cause (if determined)')
                        ->rows(2),

                    Forms\Components\Textarea::make('corrective_actions')
                        ->label('Corrective / Preventive Actions')
                        ->rows(2),

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
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('incident_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'gray' => ['near_miss', 'first_aid'],
                        'warning' => ['medical_treatment', 'property_damage', 'environmental'],
                        'danger' => ['lost_time', 'fatality'],
                    ]),

                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => ['high', 'critical'],
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'investigating',
                        'success' => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Reported By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'investigating' => 'Investigating',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('incident_date', 'desc')
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
            'index' => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),
            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
}
