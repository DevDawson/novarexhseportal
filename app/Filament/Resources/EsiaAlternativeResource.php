<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaAlternativeResource\Pages;
use App\Models\EsiaAlternative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaAlternativeResource extends Resource
{
    protected static ?string $model = EsiaAlternative::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 7: Alternatives Analysis';
    protected static ?string $modelLabel = 'Alternative';
    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // --------------------------------------------------------
            // SECTION 1 — Alternative Identification
            // --------------------------------------------------------
            Forms\Components\Section::make('Alternative Identification')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('screening_id')
                        ->label('Linked Screening (optional)')
                        ->relationship('screening', 'id',
                            fn ($query) => $query->select('id', 'project_id')
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => "Screening #{$record->id} — {$record->project?->title}")
                        ->searchable()
                        ->placeholder('Select if linked to a specific screening'),

                    Forms\Components\Select::make('alternative_type')
                        ->label('Alternative Type')
                        ->options(EsiaAlternative::TYPE_LABELS)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $type = $get('alternative_type');
                            if ($type === 'no_project') {
                                $set('environmental_impact', 1);
                                $set('cost_factor', 1);
                                $set('social_acceptance', 3);
                                $set('feasibility', 5);
                            }
                        }),

                    Forms\Components\Placeholder::make('type_hint')
                        ->label('Description')
                        ->content(fn (Get $get): string =>
                            EsiaAlternative::TYPE_DESCRIPTIONS[$get('alternative_type') ?? 'other'] ?? ''
                        ),

                    Forms\Components\TextInput::make('title')
                        ->label('Alternative Title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Solar PV instead of diesel generators')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Detailed Description')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),
                ]),

            // --------------------------------------------------------
            // SECTION 2 — Evaluation Criteria
            // --------------------------------------------------------
            Forms\Components\Section::make('Evaluation Criteria (Score 1–5)')
                ->description('AlternativeScore = EnvironmentalImpact + Cost + SocialAcceptance + Feasibility. Preference score computed automatically.')
                ->columns(2)
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('environmental_impact')
                            ->label('Environmental Impact (1=Negligible → 5=Severe)')
                            ->options([
                                1 => '1 — Negligible impact',
                                2 => '2 — Minor impact',
                                3 => '3 — Moderate impact',
                                4 => '4 — Major impact',
                                5 => '5 — Severe / irreversible impact',
                            ])
                            ->default(3)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $g, Set $s) => self::recalcPreference($g, $s))
                            ->helperText('Lower = better (less environmental harm)'),

                        Forms\Components\Select::make('cost_factor')
                            ->label('Cost Factor (1=Very Low → 5=Very High)')
                            ->options([
                                1 => '1 — Very low cost',
                                2 => '2 — Low cost',
                                3 => '3 — Moderate cost',
                                4 => '4 — High cost',
                                5 => '5 — Very high cost',
                            ])
                            ->default(3)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $g, Set $s) => self::recalcPreference($g, $s))
                            ->helperText('Lower = better (more cost-effective)'),

                        Forms\Components\Select::make('social_acceptance')
                            ->label('Social Acceptance (1=Very Low → 5=Very High)')
                            ->options([
                                1 => '1 — Strongly opposed',
                                2 => '2 — Low acceptance',
                                3 => '3 — Neutral / moderate',
                                4 => '4 — Good acceptance',
                                5 => '5 — Strongly supported',
                            ])
                            ->default(3)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $g, Set $s) => self::recalcPreference($g, $s))
                            ->helperText('Higher = better (community support)'),

                        Forms\Components\Select::make('feasibility')
                            ->label('Technical Feasibility (1=Not Feasible → 5=Highly Feasible)')
                            ->options([
                                1 => '1 — Not feasible',
                                2 => '2 — Marginally feasible',
                                3 => '3 — Moderately feasible',
                                4 => '4 — Feasible',
                                5 => '5 — Highly feasible',
                            ])
                            ->default(3)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $g, Set $s) => self::recalcPreference($g, $s))
                            ->helperText('Higher = better (technical viability)'),
                ])->columnSpanFull(),

                    Forms\Components\Placeholder::make('preference_preview')
                        ->label('Preference Score (auto-computed)')
                        ->content(function (Get $get): string {
                            $ei = (int) ($get('environmental_impact') ?: 3);
                            $cf = (int) ($get('cost_factor') ?: 3);
                            $sa = (int) ($get('social_acceptance') ?: 3);
                            $fe = (int) ($get('feasibility') ?: 3);
                            $score = ($sa + $fe) + (6 - $ei) + (6 - $cf);
                            $label = $score >= 16 ? 'Highly Preferred'
                                : ($score >= 12 ? 'Preferred'
                                : ($score >= 8  ? 'Marginal'
                                : 'Not Preferred'));
                            return "Preference Score: {$score} / 20 — {$label}";
                        })
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('preference_score')->default(12),
                ]),

            // --------------------------------------------------------
            // SECTION 3 — Recommendation
            // --------------------------------------------------------
            Forms\Components\Section::make('Recommendation')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_recommended')
                        ->label('Mark as Recommended Alternative')
                        ->helperText('Check this for the preferred alternative chosen for the project.')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('recommendation_notes')
                        ->label('Recommendation Notes / Justification')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('evaluated_by')
                        ->label('Evaluated By')
                        ->relationship('evaluatedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    Forms\Components\DatePicker::make('evaluated_at')
                        ->label('Evaluation Date')
                        ->native(false)
                        ->default(today()),
                ]),
        ]);
    }

    protected static function recalcPreference(Get $get, Set $set): void
    {
        $ei = (int) ($get('environmental_impact') ?: 3);
        $cf = (int) ($get('cost_factor') ?: 3);
        $sa = (int) ($get('social_acceptance') ?: 3);
        $fe = (int) ($get('feasibility') ?: 3);
        $set('preference_score', ($sa + $fe) + (6 - $ei) + (6 - $cf));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('alternative_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => EsiaAlternative::TYPE_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'no_project'    => 'gray',
                        'site'          => 'info',
                        'technology'    => 'primary',
                        'design'        => 'warning',
                        'process'       => 'success',
                        'energy_source' => 'success',
                        default         => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Alternative')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('environmental_impact')
                    ->label('EI')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'danger' : ($state >= 3 ? 'warning' : 'success'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('cost_factor')
                    ->label('Cost')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'danger' : ($state >= 3 ? 'warning' : 'success'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('social_acceptance')
                    ->label('SA')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('feasibility')
                    ->label('F')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('preference_score')
                    ->label('Preference Score')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "{$state} / 20")
                    ->color(fn (int $state): string => $state >= 16 ? 'success' : ($state >= 12 ? 'info' : ($state >= 8 ? 'warning' : 'danger')))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_recommended')
                    ->label('Recommended')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->trueColor('warning'),
            ])
            ->defaultSort('preference_score', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('alternative_type')
                    ->label('Type')
                    ->options(EsiaAlternative::TYPE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('recommended_only')
                    ->label('Recommended Only')
                    ->query(fn ($q) => $q->where('is_recommended', true))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaAlternatives::route('/'),
            'create' => Pages\CreateEsiaAlternative::route('/create'),
            'edit'   => Pages\EditEsiaAlternative::route('/{record}/edit'),
        ];
    }
}
