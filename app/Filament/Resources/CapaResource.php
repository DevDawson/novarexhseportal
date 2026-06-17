<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CapaResource\Pages;
use App\Models\CapaAction;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CapaResource extends Resource
{
    protected static ?string $model = CapaAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Risk Management';

    protected static ?string $navigationLabel = 'CAPA';

    protected static ?string $modelLabel = 'CAPA Action';

    protected static ?string $pluralModelLabel = 'Corrective & Preventive Actions';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage capa') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage capa') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage capa') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('CAPA Reference')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('capa_reference')
                        ->label('CAPA Reference')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Auto-generated on save'),

                    Forms\Components\Select::make('capa_type')
                        ->options(['corrective' => 'Corrective Action', 'preventive' => 'Preventive Action'])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'])
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Source')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('source_type')
                        ->options([
                            'incident' => 'Incident',
                            'audit' => 'Audit Finding',
                            'inspection' => 'Inspection',
                            'risk_assessment' => 'Risk Assessment',
                            'compliance_finding' => 'Compliance Finding',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('incident_id')
                        ->label('Related Incident')
                        ->relationship('incident', 'description')
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('source_type') === 'incident'),

                    Forms\Components\Select::make('audit_id')
                        ->label('Related Audit')
                        ->relationship('audit', 'audit_reference')
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('source_type') === 'audit'),

                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Action Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('category')
                        ->options(['safety' => 'Safety', 'environmental' => 'Environmental', 'quality' => 'Quality', 'process' => 'Process', 'compliance' => 'Compliance', 'other' => 'Other'])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(4),

                    Forms\Components\Textarea::make('root_cause')
                        ->rows(3)
                        ->label('Root Cause Analysis'),
                ]),

            Forms\Components\Section::make('Assignment & Deadline')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('raised_by_id')
                        ->label('Raised By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->native(false),

                    Forms\Components\Select::make('assigned_to_id')
                        ->label('Assigned To')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('due_date')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Implementation')
                ->schema([
                    Forms\Components\Textarea::make('action_taken')
                        ->rows(4)
                        ->label('Action Taken / Implementation Details'),

                    Forms\Components\DatePicker::make('completion_date')
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['open' => 'Open', 'in_progress' => 'In Progress', 'pending_verification' => 'Pending Verification', 'closed' => 'Closed'])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('approved_by_id')
                        ->label('Approved By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Effectiveness Verification')
                ->schema([
                    Forms\Components\Toggle::make('effectiveness_verified')
                        ->label('Effectiveness Verified')
                        ->live(),

                    Forms\Components\Textarea::make('effectiveness_notes')
                        ->rows(3)
                        ->visible(fn ($get) => $get('effectiveness_verified')),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('effectiveness_verified')),

                    Forms\Components\DatePicker::make('verified_date')
                        ->native(false)
                        ->visible(fn ($get) => $get('effectiveness_verified')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('capa_reference')
                    ->label('Ref.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('capa_type')
                    ->colors(['warning' => 'corrective', 'success' => 'preventive'])
                    ->formatStateUsing(fn ($state) => $state === 'corrective' ? 'Corrective' : 'Preventive'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\BadgeColumn::make('source_type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['danger' => 'incident', 'warning' => 'audit', 'gray' => 'other']),

                Tables\Columns\BadgeColumn::make('priority')
                    ->colors(['gray' => 'low', 'warning' => 'medium', 'danger' => 'high', 'primary' => 'critical']),

                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To'),

                Tables\Columns\TextColumn::make('due_date')->date('d M Y')->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'open', 'primary' => 'in_progress', 'gray' => 'pending_verification', 'success' => 'closed'])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),

                Tables\Columns\IconColumn::make('effectiveness_verified')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('capa_type')->options(['corrective' => 'Corrective', 'preventive' => 'Preventive']),
                Tables\Filters\SelectFilter::make('status')->options(['open' => 'Open', 'in_progress' => 'In Progress', 'pending_verification' => 'Pending Verification', 'closed' => 'Closed']),
                Tables\Filters\SelectFilter::make('priority')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical']),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCapaActions::route('/'),
            'create' => Pages\CreateCapaAction::route('/create'),
            'edit' => Pages\EditCapaAction::route('/{record}/edit'),
        ];
    }
}
