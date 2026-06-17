<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GrievanceResource\Pages;
use App\Models\Grievance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GrievanceResource extends Resource
{
    protected static ?string $model = Grievance::class;

    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?string $navigationLabel = 'Grievances';
    protected static ?string $modelLabel      = 'Grievance';
    protected static ?int    $navigationSort  = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage grievances') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage grievances') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage grievances') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Grievance Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->label('Reference')
                        ->placeholder('Auto-generated')
                        ->dehydrated(false)
                        ->disabled()
                        ->visibleOn('edit'),

                    Forms\Components\DatePicker::make('received_date')
                        ->label('Date Received')
                        ->required()
                        ->default(now()),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options(Grievance::CATEGORY_LABELS)
                        ->required(),

                    Forms\Components\Select::make('severity')
                        ->label('Severity')
                        ->options(Grievance::SEVERITY_LABELS)
                        ->required(),

                    Forms\Components\Toggle::make('is_anonymous')
                        ->label('Anonymous Complaint')
                        ->live()
                        ->default(false),

                    Forms\Components\Select::make('stakeholder_id')
                        ->label('Stakeholder (if known)')
                        ->relationship('stakeholder', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\TextInput::make('complainant_name')
                        ->label('Complainant Name')
                        ->maxLength(255)
                        ->hidden(fn (Forms\Get $get) => $get('is_anonymous')),

                    Forms\Components\TextInput::make('complainant_contact')
                        ->label('Complainant Contact')
                        ->maxLength(255)
                        ->hidden(fn (Forms\Get $get) => $get('is_anonymous')),

                    Forms\Components\Textarea::make('description')
                        ->label('Description of Grievance')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Investigation & Resolution')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(Grievance::STATUS_LABELS)
                        ->required()
                        ->default('open'),

                    Forms\Components\Select::make('assigned_to')
                        ->label('Assigned To')
                        ->relationship('assignedTo', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('target_resolution_date')
                        ->label('Target Resolution Date'),

                    Forms\Components\DatePicker::make('actual_resolution_date')
                        ->label('Actual Resolution Date'),

                    Forms\Components\Textarea::make('investigation_notes')
                        ->label('Investigation Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('resolution')
                        ->label('Resolution')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('complainant_satisfied')
                        ->label('Complainant Satisfied?')
                        ->options([1 => 'Yes', 0 => 'No'])
                        ->placeholder('—'),
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

                Tables\Columns\TextColumn::make('received_date')
                    ->label('Received')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn ($state) => Grievance::CATEGORY_LABELS[$state] ?? $state)
                    ->colors(['primary' => fn () => true]),

                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'danger'  => 'high',
                        'warning' => 'medium',
                        'success' => 'low',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => Grievance::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => ['under_review', 'action_taken'],
                        'success' => ['resolved', 'closed'],
                    ]),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('target_resolution_date')
                    ->label('Target')
                    ->date()
                    ->color(fn ($record) => $record?->is_overdue ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\IconColumn::make('complainant_satisfied')
                    ->label('Satisfied')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Grievance::STATUS_LABELS),
                Tables\Filters\SelectFilter::make('severity')
                    ->options(Grievance::SEVERITY_LABELS),
                Tables\Filters\SelectFilter::make('category')
                    ->options(Grievance::CATEGORY_LABELS),
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn ($query) => $query
                        ->whereNotNull('target_resolution_date')
                        ->where('target_resolution_date', '<', now())
                        ->whereNotIn('status', ['resolved', 'closed'])
                    ),
            ])
            ->defaultSort('received_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGrievances::route('/'),
            'create' => Pages\CreateGrievance::route('/create'),
            'edit'   => Pages\EditGrievance::route('/{record}/edit'),
        ];
    }
}
