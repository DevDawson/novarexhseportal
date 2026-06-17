<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EthicsIncidentResource\Pages;
use App\Models\EthicsIncident;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EthicsIncidentResource extends Resource
{
    protected static ?string $model = EthicsIncident::class;

    protected static ?string $navigationIcon  = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?string $navigationLabel = 'Ethics Incidents';
    protected static ?string $modelLabel      = 'Ethics Incident';
    protected static ?int    $navigationSort  = 6;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage ethics_incidents') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage ethics_incidents') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage ethics_incidents') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Incident Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->label('Reference')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated')
                        ->visibleOn('edit'),

                    Forms\Components\DatePicker::make('reported_date')
                        ->label('Date Reported')
                        ->required()
                        ->default(now()),

                    Forms\Components\Select::make('incident_type')
                        ->label('Incident Type')
                        ->options(EthicsIncident::TYPE_LABELS)
                        ->required(),

                    Forms\Components\Select::make('severity')
                        ->label('Severity')
                        ->options(EthicsIncident::SEVERITY_LABELS)
                        ->required(),

                    Forms\Components\Toggle::make('is_anonymous')
                        ->label('Anonymous Report')
                        ->default(false),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Investigation & Closure')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EthicsIncident::STATUS_LABELS)
                        ->required()
                        ->default('reported'),

                    Forms\Components\Select::make('investigated_by')
                        ->label('Investigated By')
                        ->relationship('investigatedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('closure_date')
                        ->label('Closure Date'),

                    Forms\Components\Textarea::make('investigation_findings')
                        ->label('Investigation Findings')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('corrective_action')
                        ->label('Corrective Action')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reported_date')
                    ->label('Reported')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('incident_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => EthicsIncident::TYPE_LABELS[$state] ?? $state)
                    ->colors(['primary' => fn () => true]),

                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'danger'  => 'critical',
                        'warning' => 'high',
                        'info'    => 'medium',
                        'gray'    => 'low',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => EthicsIncident::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'reported',
                        'warning' => 'under_investigation',
                        'info'    => 'action_taken',
                        'success' => ['closed', 'no_action_required'],
                    ]),

                Tables\Columns\IconColumn::make('is_anonymous')
                    ->label('Anon')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('investigatedBy.name')
                    ->label('Investigator')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('closure_date')
                    ->label('Closed')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('incident_type')
                    ->options(EthicsIncident::TYPE_LABELS),
                Tables\Filters\SelectFilter::make('severity')
                    ->options(EthicsIncident::SEVERITY_LABELS),
                Tables\Filters\SelectFilter::make('status')
                    ->options(EthicsIncident::STATUS_LABELS),
                Tables\Filters\Filter::make('open')
                    ->label('Open')
                    ->query(fn ($query) => $query->whereNotIn('status', ['closed', 'no_action_required'])),
            ])
            ->defaultSort('reported_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEthicsIncidents::route('/'),
            'create' => Pages\CreateEthicsIncident::route('/create'),
            'edit'   => Pages\EditEthicsIncident::route('/{record}/edit'),
        ];
    }
}
