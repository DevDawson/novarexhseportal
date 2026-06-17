<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompetencyAssessmentResource\Pages;
use App\Models\CompetencyAssessment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompetencyAssessmentResource extends Resource
{
    protected static ?string $model = CompetencyAssessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Training & Competency';

    protected static ?string $navigationLabel = 'Competency Assessments';

    protected static ?string $modelLabel = 'Competency Assessment';

    protected static ?int $navigationSort = 3;

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
            Forms\Components\Section::make('Assessment Details')
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

                    Forms\Components\Select::make('competency_area')
                        ->options([
                            'safety' => 'Safety',
                            'environmental' => 'Environmental',
                            'technical' => 'Technical',
                            'emergency_response' => 'Emergency Response',
                            'leadership' => 'Leadership',
                            'quality' => 'Quality',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('competency_description')
                        ->label('Competency Being Assessed')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('assessment_method')
                        ->options([
                            'observation' => 'Observation',
                            'written_test' => 'Written Test',
                            'practical_demonstration' => 'Practical Demonstration',
                            'supervisor_review' => 'Supervisor Review',
                            'simulation' => 'Simulation / Drill',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('assessed_by_id')
                        ->label('Assessed By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('assessment_date')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('score')
                        ->label('Score (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->nullable(),

                    Forms\Components\Select::make('result')
                        ->options([
                            'competent' => 'Competent',
                            'not_yet_competent' => 'Not Yet Competent',
                            'requires_training' => 'Requires Training',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('next_assessment_date')
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
                Tables\Columns\TextColumn::make('competency_description')->label('Competency')->limit(35)->searchable(),
                Tables\Columns\BadgeColumn::make('competency_area')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['danger' => 'safety', 'success' => 'environmental', 'primary' => 'technical', 'warning' => 'emergency_response']),
                Tables\Columns\TextColumn::make('assessment_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('score')->label('Score %')->alignCenter(),
                Tables\Columns\BadgeColumn::make('result')
                    ->colors(['success' => 'competent', 'warning' => 'not_yet_competent', 'danger' => 'requires_training'])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('next_assessment_date')->date('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('competency_area')
                    ->options(['safety' => 'Safety', 'environmental' => 'Environmental', 'technical' => 'Technical', 'emergency_response' => 'Emergency Response', 'leadership' => 'Leadership', 'quality' => 'Quality']),
                Tables\Filters\SelectFilter::make('result')
                    ->options(['competent' => 'Competent', 'not_yet_competent' => 'Not Yet Competent', 'requires_training' => 'Requires Training']),
            ])
            ->defaultSort('assessment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompetencyAssessments::route('/'),
            'create' => Pages\CreateCompetencyAssessment::route('/create'),
            'edit' => Pages\EditCompetencyAssessment::route('/{record}/edit'),
        ];
    }
}
