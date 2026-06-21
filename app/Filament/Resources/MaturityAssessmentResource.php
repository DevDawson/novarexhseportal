<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaturityAssessmentResource\Pages;
use App\Models\MaturityAssessment;
use App\Models\MaturityDimension;
use App\Models\MaturityIndicator;
use App\Models\MaturityScore;
use App\Services\MaturityScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaturityAssessmentResource extends Resource
{
    protected static ?string $model = MaturityAssessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'HSE System';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'HSE Maturity Index';

    protected static ?string $modelLabel = 'Maturity Assessment';

    protected static ?string $pluralModelLabel = 'Maturity Assessments';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'lead_auditor']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'lead_auditor']) ?? false;
    }

    public static function form(Form $form): Form
    {
        $dimensions = MaturityDimension::with('indicators')->orderBy('sort_order')->get();

        $schema = [
            Forms\Components\Section::make('Assessment Details')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project (optional)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('period_type')
                        ->label('Period Type')
                        ->options([
                            'monthly'   => 'Monthly',
                            'quarterly' => 'Quarterly',
                            'annual'    => 'Annual',
                        ])
                        ->default('quarterly')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('period')
                        ->label('Period')
                        ->required()
                        ->placeholder('e.g. 2026-Q2 or 2026-06')
                        ->default(now()->format('Y') . '-Q' . ceil(now()->month / 3)),

                    Forms\Components\Select::make('assessed_by')
                        ->label('Assessed By')
                        ->relationship('assessedBy', 'name')
                        ->searchable()
                        ->default(auth()->id()),

                    Forms\Components\DatePicker::make('assessed_at')
                        ->label('Assessment Date')
                        ->native(false)
                        ->default(now()),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(['draft' => 'Draft', 'finalised' => 'Finalised'])
                        ->default('draft')
                        ->native(false)
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Overall Notes')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn () => auth()->id()),
                ]),
        ];

        // One section per dimension with one score row per indicator
        foreach ($dimensions as $dim) {
            $indicatorFields = [];

            foreach ($dim->indicators as $indicator) {
                $key = "scores.{$indicator->id}";

                $indicatorFields[] = Forms\Components\Grid::make(12)
                    ->schema([
                        Forms\Components\Placeholder::make("label_{$indicator->id}")
                            ->label('')
                            ->content($indicator->name)
                            ->columnSpan(6),

                        Forms\Components\Select::make("{$key}.score")
                            ->label('Score')
                            ->options([
                                1 => '1 — Not implemented',
                                2 => '2 — Partially implemented',
                                3 => '3 — Defined',
                                4 => '4 — Implemented effectively',
                                5 => '5 — Optimized',
                            ])
                            ->default(1)
                            ->native(false)
                            ->required()
                            ->columnSpan(3),

                        Forms\Components\TextInput::make("{$key}.evidence")
                            ->label('Evidence / Notes')
                            ->placeholder('Optional evidence or notes')
                            ->columnSpan(3),

                        Forms\Components\Hidden::make("{$key}.indicator_id")
                            ->default($indicator->id),
                    ]);
            }

            $schema[] = Forms\Components\Section::make(
                "Dimension {$dim->code} — {$dim->name} (Weight: {$dim->weight}%)"
            )
                ->collapsed()
                ->schema($indicatorFields);
        }

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('All projects')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('period_type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'monthly'   => 'info',
                        'quarterly' => 'warning',
                        'annual'    => 'success',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('overall_score')
                    ->label('HSE MI Score')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' / 5.00' : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('maturity_level')
                    ->label('Maturity Level')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_contains((string)$state, 'Level 1') => 'danger',
                        str_contains((string)$state, 'Level 2') => 'warning',
                        str_contains((string)$state, 'Level 3') => 'info',
                        str_contains((string)$state, 'Level 4') => 'primary',
                        str_contains((string)$state, 'Level 5') => 'success',
                        default => 'gray',
                    })
                    ->placeholder('Not calculated'),

                Tables\Columns\TextColumn::make('assessedBy.name')
                    ->label('Assessed By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assessed_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state === 'finalised' ? 'success' : 'gray'),
            ])
            ->defaultSort('assessed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('period_type')
                    ->options(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual']),

                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'finalised' => 'Finalised']),
            ])
            ->actions([
                Tables\Actions\Action::make('autoFill')
                    ->label('Auto-Fill Scores')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Auto-Fill from System Data')
                    ->modalDescription('The system will calculate scores for supported indicators using live data from incidents, audits, CAPA, training, and other modules. Manual scores will not be overwritten.')
                    ->action(function ($record) {
                        $indicators = MaturityIndicator::whereNotNull('auto_source')->get();
                        $filled = 0;
                        foreach ($indicators as $ind) {
                            $score = MaturityScoringService::autoScore($ind->auto_source, $record->project_id);
                            MaturityScore::updateOrCreate(
                                ['assessment_id' => $record->id, 'indicator_id' => $ind->id],
                                ['score' => $score, 'auto_calculated' => true]
                            );
                            $filled++;
                        }
                        MaturityScoringService::recalculate($record);
                        Notification::make()
                            ->title("Auto-filled {$filled} indicators from system data.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('calculate')
                    ->label('Recalculate MI')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->action(function ($record) {
                        MaturityScoringService::recalculate($record);
                        Notification::make()
                            ->title('HSE Maturity Index recalculated: ' . number_format($record->fresh()->overall_score, 2))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('finalise')
                    ->label('Finalise')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft' && $record->overall_score !== null)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'finalised'])),

                Tables\Actions\Action::make('pdf')
                    ->label('PDF Scorecard')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.maturity', $record))
                    ->openUrlInNewTab(),

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
            'index'  => Pages\ListMaturityAssessments::route('/'),
            'create' => Pages\CreateMaturityAssessment::route('/create'),
            'edit'   => Pages\EditMaturityAssessment::route('/{record}/edit'),
        ];
    }
}
