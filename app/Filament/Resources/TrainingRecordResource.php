<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingRecordResource\Pages;
use App\Models\Staff;
use App\Models\TrainingRecord;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainingRecordResource extends Resource
{
    protected static ?string $model = TrainingRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Training & Competency';

    protected static ?string $navigationLabel = 'Training Records';

    protected static ?string $modelLabel = 'Training Record';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage training') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage training') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage training') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Training Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('staff_id')
                        ->label('Staff Member')
                        ->relationship('staff', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('training_type')
                        ->options([
                            'induction' => 'Induction',
                            'refresher' => 'Refresher',
                            'toolbox_talk' => 'Toolbox Talk',
                            'external' => 'External Training',
                            'certification' => 'Certification Training',
                            'e_learning' => 'E-Learning',
                            'drill_exercise' => 'Drill / Exercise',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('training_title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('topic')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('provider')
                        ->label('Training Provider / Facilitator'),

                    Forms\Components\TextInput::make('conducted_by')
                        ->label('Conducted By'),

                    Forms\Components\DatePicker::make('date_attended')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('duration_hours')
                        ->label('Duration (Hours)')
                        ->numeric()
                        ->minValue(0)
                        ->default(1),

                    Forms\Components\Select::make('result')
                        ->options(['passed' => 'Passed', 'failed' => 'Failed', 'not_assessed' => 'Not Assessed'])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('certificate_number')
                        ->label('Certificate Number')
                        ->nullable(),

                    Forms\Components\DatePicker::make('expiry_date')
                        ->label('Certificate Expiry Date')
                        ->native(false),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.full_name')->label('Staff Member')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('training_title')->searchable()->limit(35),

                Tables\Columns\BadgeColumn::make('training_type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['primary' => ['induction', 'certification'], 'warning' => 'refresher', 'gray' => ['toolbox_talk', 'e_learning', 'drill_exercise']]),

                Tables\Columns\TextColumn::make('date_attended')->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('duration_hours')->label('Hours')->alignCenter(),

                Tables\Columns\BadgeColumn::make('result')
                    ->colors(['success' => 'passed', 'danger' => 'failed', 'gray' => 'not_assessed']),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->is_expired ? 'danger' : ($record?->is_expiring_soon ? 'warning' : null)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('training_type')
                    ->options(['induction' => 'Induction', 'refresher' => 'Refresher', 'toolbox_talk' => 'Toolbox Talk', 'external' => 'External', 'certification' => 'Certification', 'e_learning' => 'E-Learning', 'drill_exercise' => 'Drill']),
                Tables\Filters\SelectFilter::make('result')
                    ->options(['passed' => 'Passed', 'failed' => 'Failed', 'not_assessed' => 'Not Assessed']),
            ])
            ->defaultSort('date_attended', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingRecords::route('/'),
            'create' => Pages\CreateTrainingRecord::route('/create'),
            'edit' => Pages\EditTrainingRecord::route('/{record}/edit'),
        ];
    }
}
