<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaScreeningResource\Pages;
use App\Models\EsiaScreening;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaScreeningResource extends Resource
{
    protected static ?string $model = EsiaScreening::class;
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 2: Screening';
    protected static ?string $modelLabel = 'ESIA Screening';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project')
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

            Forms\Components\Section::make('Screening Factors')
                ->description('Score each factor 1–5. Total determines ESIA category: ≤5=C, 6–10=B, ≥11=A.')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('scale')
                        ->label('Scale of Project (1–5)')
                        ->options([
                            1 => '1 — Local / Small',
                            2 => '2 — District-wide',
                            3 => '3 — Regional',
                            4 => '4 — National',
                            5 => '5 — Transboundary / International',
                        ])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            $s('screening_score', (int)$g('scale') + (int)$g('sensitivity') + (int)$g('pollution_potential'))
                        ),

                    Forms\Components\Select::make('sensitivity')
                        ->label('Environmental Sensitivity (1–5)')
                        ->options([
                            1 => '1 — Low sensitivity area',
                            2 => '2 — Somewhat sensitive',
                            3 => '3 — Moderately sensitive',
                            4 => '4 — Highly sensitive',
                            5 => '5 — Protected / Critical habitat',
                        ])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            $s('screening_score', (int)$g('scale') + (int)$g('sensitivity') + (int)$g('pollution_potential'))
                        ),

                    Forms\Components\Select::make('pollution_potential')
                        ->label('Pollution Potential (1–5)')
                        ->options([
                            1 => '1 — Negligible',
                            2 => '2 — Low',
                            3 => '3 — Moderate',
                            4 => '4 — High',
                            5 => '5 — Very High',
                        ])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $g, Forms\Set $s) =>
                            $s('screening_score', (int)$g('scale') + (int)$g('sensitivity') + (int)$g('pollution_potential'))
                        ),

                    Forms\Components\Placeholder::make('screening_score_preview')
                        ->label('Screening Score (auto)')
                        ->content(function (Forms\Get $get): string {
                            $score = (int)$get('scale') + (int)$get('sensitivity') + (int)$get('pollution_potential');
                            $cat = $score >= 11 ? 'A — Full ESIA' : ($score >= 6 ? 'B — Limited EIA' : 'C — No EIA');
                            return "Score: {$score} / 15 → Category {$cat}";
                        })
                        ->columnSpan(3),

                    Forms\Components\Hidden::make('screening_score')->default(3),
                ]),

            Forms\Components\Section::make('Description & Justification')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('project_description')
                        ->label('Brief Project Description')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('screening_justification')
                        ->label('Screening Justification')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Screening Checklist')
                ->description('Confirm each checklist item has been assessed during the screening process.')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('land_acquisition_involved')
                        ->label('Land Acquisition Involved?')
                        ->helperText('Project requires acquisition of land from current occupants.'),

                    Forms\Components\Toggle::make('biodiversity_risk_checked')
                        ->label('Biodiversity Risk Assessed?')
                        ->helperText('Potential impacts on flora, fauna, and ecosystems have been reviewed.'),

                    Forms\Components\Toggle::make('sensitive_area_check')
                        ->label('Sensitive Area Check Done?')
                        ->helperText('Proximity to protected areas, water bodies, or cultural sites assessed.'),

                    Forms\Components\Toggle::make('pollution_check_done')
                        ->label('Pollution Potential Assessed?')
                        ->helperText('Potential for air, water, soil, or noise pollution formally assessed.'),
                ]),

            Forms\Components\Section::make('Review & Status')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('screened_by')
                        ->label('Screened By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\DatePicker::make('screened_at')
                        ->label('Screening Date')
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->label('Review Status')
                        ->options(EsiaScreening::STATUS_LABELS)
                        ->default('pending')
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('reviewer_notes')
                        ->label('Reviewer Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => "Category {$state}")
                    ->colors([
                        'danger'  => 'A',
                        'warning' => 'B',
                        'success' => 'C',
                    ]),

                Tables\Columns\TextColumn::make('screening_score')
                    ->label('Score')
                    ->badge()
                    ->color(fn (int $state) => $state >= 11 ? 'danger' : ($state >= 6 ? 'warning' : 'success')),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string =>
                        EsiaScreening::STATUS_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'gray'    => 'pending',
                        'primary' => 'in_review',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('screened_at')
                    ->label('Screening Date')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(['A' => 'Category A', 'B' => 'Category B', 'C' => 'Category C']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(EsiaScreening::STATUS_LABELS),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaScreenings::route('/'),
            'create' => Pages\CreateEsiaScreening::route('/create'),
            'edit'   => Pages\EditEsiaScreening::route('/{record}/edit'),
        ];
    }
}
