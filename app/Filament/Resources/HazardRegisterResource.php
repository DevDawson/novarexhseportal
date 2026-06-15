<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazardRegisterResource\Pages;
use App\Models\HazardRegister;
use App\Models\User;
use App\Services\RiskScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HazardRegisterResource extends Resource
{
    protected static ?string $model = HazardRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'HSE & Technical Operations';

    protected static ?string $navigationLabel = 'HIRA';

    protected static ?string $modelLabel = 'Hazard';

    protected static ?string $pluralModelLabel = 'Hazard Register (HIRA)';

    protected static ?int $navigationSort = 4;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage hazards') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage hazards') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage hazards') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    // ----------------------------------------------------------------
    // Form
    // ----------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([

            // --------------------------------------------------------
            // SECTION 1: Activity & Hazard
            // --------------------------------------------------------
            Forms\Components\Section::make('Activity & Hazard')
                ->description('Describe the task or activity being assessed and the hazard associated with it.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / not project specific')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('activity_task')
                        ->label('Activity / Task')
                        ->placeholder('e.g. Welding on scaffold platform')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('location')
                        ->label('Location / Site')
                        ->maxLength(255)
                        ->placeholder('e.g. Site B - Level 3'),

                    Forms\Components\Textarea::make('hazard_description')
                        ->label('Hazard Description')
                        ->helperText('What could go wrong? What is the source of harm?')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('hazard_category')
                        ->label('Hazard Category')
                        ->options(HazardRegister::HAZARD_CATEGORY_LABELS)
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('who_might_be_harmed')
                        ->label('Who Might Be Harmed?')
                        ->placeholder('e.g. Welders, nearby workers, site visitors')
                        ->rows(2),
                ]),

            // --------------------------------------------------------
            // SECTION 2: Initial Risk Assessment (before controls)
            // --------------------------------------------------------
            Forms\Components\Section::make('Initial Risk Assessment')
                ->description('Rate the risk BEFORE any controls are applied.')
                ->columns(2)
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('initial_likelihood')
                            ->label('Likelihood (L)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateInitialRisk($get, $set)),

                        Forms\Components\Select::make('initial_severity')
                            ->label('Severity (S)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateInitialRisk($get, $set)),

                        Forms\Components\Placeholder::make('initial_risk_preview')
                            ->label('Initial Risk Score (L × S)')
                            ->content(function (Forms\Get $get) {
                                $score = RiskScoringService::score(
                                    (int) $get('initial_likelihood'),
                                    (int) $get('initial_severity')
                                );
                                $level = RiskScoringService::level($score);

                                return "{$score} / 25 — " . ucfirst($level);
                            }),
                    ])->columnSpanFull(),

                    Forms\Components\Hidden::make('initial_risk_score')->default(0),
                ]),

            // --------------------------------------------------------
            // SECTION 3: Controls
            // --------------------------------------------------------
            Forms\Components\Section::make('Controls')
                ->description('Document existing controls and any additional controls to be applied. Follow the Hierarchy of Controls (Elimination → PPE).')
                ->schema([
                    Forms\Components\Textarea::make('existing_controls')
                        ->label('Existing Controls')
                        ->helperText('What controls are currently in place?')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\CheckboxList::make('additional_controls')
                        ->label('Additional Controls to Apply (Hierarchy of Controls)')
                        ->options(HazardRegister::CONTROL_HIERARCHY_OPTIONS)
                        ->columns(1)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('additional_controls_description')
                        ->label('Additional Controls — Detail')
                        ->helperText('Describe specifically what additional controls will be implemented.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 4: Residual Risk Assessment (after controls)
            // --------------------------------------------------------
            Forms\Components\Section::make('Residual Risk Assessment')
                ->description('Rate the risk AFTER all controls are applied. Target residual risk should be Low or Medium.')
                ->columns(2)
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('residual_likelihood')
                            ->label('Likelihood (L)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateResidualRisk($get, $set)),

                        Forms\Components\Select::make('residual_severity')
                            ->label('Severity (S)')
                            ->options(RiskScoringService::ratingOptions())
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateResidualRisk($get, $set)),

                        Forms\Components\Placeholder::make('residual_risk_preview')
                            ->label('Residual Risk Score (L × S)')
                            ->content(function (Forms\Get $get) {
                                $score = RiskScoringService::score(
                                    (int) $get('residual_likelihood'),
                                    (int) $get('residual_severity')
                                );
                                $level = RiskScoringService::level($score);

                                return "{$score} / 25 — " . ucfirst($level);
                            }),
                    ])->columnSpanFull(),

                    Forms\Components\Hidden::make('residual_risk_score')->default(0),
                ]),

            // --------------------------------------------------------
            // SECTION 5: Action Tracking
            // --------------------------------------------------------
            Forms\Components\Section::make('Action Tracking')
                ->description('Assign responsibility and set a review date for this hazard.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('responsible_person_id')
                        ->label('Responsible Person')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Next Review Date')
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(HazardRegister::STATUS_LABELS)
                        ->default('open')
                        ->required()
                        ->native(false),
                ]),
        ]);
    }

    // ----------------------------------------------------------------
    // Table
    // ----------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity_task')
                    ->label('Activity / Task')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company-wide')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('hazard_category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => HazardRegister::HAZARD_CATEGORY_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => ['physical', 'mechanical', 'electrical'],
                        'warning' => ['chemical', 'biological'],
                        'info'    => ['ergonomic', 'psychosocial'],
                        'success' => ['environmental'],
                    ]),

                Tables\Columns\TextColumn::make('initial_risk_score')
                    ->label('Initial Risk')
                    ->badge()
                    ->formatStateUsing(function (int $state): string {
                        $level = RiskScoringService::level($state);

                        return "{$state}/25 — " . ucfirst($level);
                    })
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('residual_risk_score')
                    ->label('Residual Risk')
                    ->badge()
                    ->formatStateUsing(function (int $state): string {
                        $level = RiskScoringService::level($state);

                        return "{$state}/25 — " . ucfirst($level);
                    })
                    ->color(fn (int $state) => RiskScoringService::colorForScore($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('responsiblePerson.name')
                    ->label('Responsible')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('review_date')
                    ->label('Review Date')
                    ->date('d M Y')
                    ->color(fn (HazardRegister $record): string =>
                        ($record->review_date && $record->review_date->isPast() && $record->status !== 'closed')
                            ? 'danger'
                            : 'gray'
                    )
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => HazardRegister::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'danger'  => 'open',
                        'warning' => 'controls_in_progress',
                        'primary' => 'controlled',
                        'success' => 'closed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hazard_category')
                    ->label('Category')
                    ->options(HazardRegister::HAZARD_CATEGORY_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(HazardRegister::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('overdue_review')
                    ->label('Overdue Review')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('review_date')
                        ->where('review_date', '<', now())
                        ->where('status', '!=', 'closed')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('high_critical')
                    ->label('High / Critical Residual Risk')
                    ->query(fn (Builder $query) => $query->where('residual_risk_score', '>=', 10))
                    ->toggle(),
            ])
            ->defaultSort('residual_risk_score', 'desc')
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.hira', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ----------------------------------------------------------------
    // Live risk recalculation helpers
    // ----------------------------------------------------------------

    protected static function recalculateInitialRisk(Forms\Get $get, Forms\Set $set): void
    {
        $score = RiskScoringService::score(
            (int) $get('initial_likelihood'),
            (int) $get('initial_severity')
        );
        $set('initial_risk_score', $score);
    }

    protected static function recalculateResidualRisk(Forms\Get $get, Forms\Set $set): void
    {
        $score = RiskScoringService::score(
            (int) $get('residual_likelihood'),
            (int) $get('residual_severity')
        );
        $set('residual_risk_score', $score);
    }

    // ----------------------------------------------------------------
    // Relations & Pages
    // ----------------------------------------------------------------

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHazardRegisters::route('/'),
            'create' => Pages\CreateHazardRegister::route('/create'),
            'edit'   => Pages\EditHazardRegister::route('/{record}/edit'),
        ];
    }
}
