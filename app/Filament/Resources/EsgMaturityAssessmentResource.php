<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsgMaturityAssessmentResource\Pages;
use App\Models\EsgMaturityAssessment;
use App\Services\EsgMaturityService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsgMaturityAssessmentResource extends Resource
{
    protected static ?string $model           = EsgMaturityAssessment::class;
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?string $navigationLabel = 'ESG Maturity Index';
    protected static ?string $modelLabel      = 'ESG Maturity Assessment';
    protected static ?int    $navigationSort  = 10;

    public static function canViewAny(): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'esg_officer', 'business_director']) ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'esg_officer']) ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'esg_officer']) ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasRole('md') ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Assessment Header ────────────────────────────────────────
            Forms\Components\Section::make('Assessment Period')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('period')
                        ->label('Period')
                        ->placeholder('e.g. 2026 or 2026-Q2')
                        ->required()->maxLength(20)
                        ->default(now()->year),

                    Forms\Components\Select::make('period_type')
                        ->options(['annual' => 'Annual', 'quarterly' => 'Quarterly'])
                        ->default('annual')->required()->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['draft' => 'Draft', 'finalized' => 'Finalized'])
                        ->default('draft')->required()->native(false),

                    Forms\Components\Select::make('assessed_by_id')
                        ->label('Assessed By')
                        ->relationship('assessedBy', 'name')
                        ->searchable()->preload()->default(auth()->id()),
                ]),

            // ── E: Environmental (Weight 40%) ───────────────────────────
            Forms\Components\Section::make('E — Environmental Performance (Weight: 40%)')
                ->description('Formula: E = (CR + WR + ER + WTR + EMS) ÷ 5')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('cr_score')
                        ->label('CR — Compliance Rate (%)')
                        ->helperText('Auto: Env. audit checklist compliant items ÷ total assessed')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('wr_score')
                        ->label('WR — Waste Diversion Rate (%)')
                        ->helperText('Auto: Waste recycled/recovered ÷ total waste generated')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('er_score')
                        ->label('ER — Emissions Reduction Rate (%)')
                        ->helperText('Semi-auto: GHG reduction vs prior year; 50 = no change, 100 = full reduction')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('wtr_score')
                        ->label('WTR — Water Reduction Efficiency (%)')
                        ->helperText('Semi-auto: Water consumption reduction vs prior year baseline')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('ems_score')
                        ->label('EMS — EMS Maturity Index (%)')
                        ->helperText('Auto: Live EMI from EMS Maturity Service (CR×25 + AS×20 + …)')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\Placeholder::make('e_score_display')
                        ->label('E Score (Auto-computed on save)')
                        ->content(fn ($record) => $record
                            ? number_format((float) $record->e_score, 2) . '%'
                            : 'Saved after entering all 5 values'),
                ]),

            // ── S: Social (Weight 30%) ───────────────────────────────────
            Forms\Components\Section::make('S — Social Performance (Weight: 30%)')
                ->description('Formula: S = (TR + LTIFR + EWR + CSR + DEI) ÷ 5')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('tr_score')
                        ->label('TR — Training Completion Rate (%)')
                        ->helperText('Auto: Training records with result = passed ÷ total')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('ltifr_score')
                        ->label('LTIFR — LTIFR Performance Score (%)')
                        ->helperText('Semi-auto: Derived from high-severity incident rate; 100 = zero LTIs')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('ewr_score')
                        ->label('EWR — Employee Well-being Score (%)')
                        ->helperText('Manual: From employee satisfaction surveys, wellness program participation, leave utilisation')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('csr_score')
                        ->label('CSR — Community Engagement Score (%)')
                        ->helperText('Semi-auto: Grievance resolution rate + stakeholder engagement frequency')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('dei_score')
                        ->label('DEI — Diversity, Equity & Inclusion (%)')
                        ->helperText('Semi-auto: Gender balance proxy from Staff records; augment with DEI survey data')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\Placeholder::make('s_score_display')
                        ->label('S Score (Auto-computed on save)')
                        ->content(fn ($record) => $record
                            ? number_format((float) $record->s_score, 2) . '%'
                            : 'Saved after entering all 5 values'),
                ]),

            // ── G: Governance (Weight 30%) ───────────────────────────────
            Forms\Components\Section::make('G — Governance Performance (Weight: 30%)')
                ->description('Formula: G = (CCR + ACR + DCR + ECR + MRR) ÷ 5')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('ccr_score')
                        ->label('CCR — Compliance & Ethics Score (%)')
                        ->helperText('Semi-auto: Active governance policies ÷ total; supplement with ethics assessment')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('acr_score')
                        ->label('ACR — Audit Closure Rate (%)')
                        ->helperText('Auto: AMS NCs + Env. audit findings closed ÷ total')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('dcr_score')
                        ->label('DCR — Document Control Rate (%)')
                        ->helperText('Auto: Active (non-expired) corporate documents ÷ total')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('ecr_score')
                        ->label('ECR — Corrective Action Closure Rate (%)')
                        ->helperText('Auto: Closed CAPA actions ÷ total CAPA actions')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\TextInput::make('mrr_score')
                        ->label('MRR — Management Review Rate (%)')
                        ->helperText('Manual: Management review meetings conducted ÷ planned this period × 100')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                    Forms\Components\Placeholder::make('g_score_display')
                        ->label('G Score (Auto-computed on save)')
                        ->content(fn ($record) => $record
                            ? number_format((float) $record->g_score, 2) . '%'
                            : 'Saved after entering all 5 values'),
                ]),

            // ── ESG-MI Result ────────────────────────────────────────────
            Forms\Components\Section::make('ESG-MI Result (Auto-computed)')
                ->columns(3)
                ->schema([
                    Forms\Components\Placeholder::make('e_result')
                        ->label('E Score (×40%)')
                        ->content(fn ($record) => $record ? number_format((float) $record->e_score, 2) . '%' : '—'),

                    Forms\Components\Placeholder::make('s_result')
                        ->label('S Score (×30%)')
                        ->content(fn ($record) => $record ? number_format((float) $record->s_score, 2) . '%' : '—'),

                    Forms\Components\Placeholder::make('g_result')
                        ->label('G Score (×30%)')
                        ->content(fn ($record) => $record ? number_format((float) $record->g_score, 2) . '%' : '—'),

                    Forms\Components\Placeholder::make('esg_mi_result')
                        ->label('ESG Maturity Index (EMI)')
                        ->content(fn ($record) => $record
                            ? number_format((float) $record->esg_mi, 2) . '% — '
                              . EsgMaturityAssessment::emiToLevel((float) $record->esg_mi)
                            : '—'),

                    Forms\Components\Placeholder::make('esg_mi_formula')
                        ->label('Formula Check')
                        ->content(fn ($record) => $record
                            ? '(' . number_format((float) $record->e_score, 1) . '×40 + '
                              . number_format((float) $record->s_score, 1) . '×30 + '
                              . number_format((float) $record->g_score, 1) . '×30) ÷ 100'
                            : '—')
                        ->columnSpan(2),
                ]),

            Forms\Components\Textarea::make('notes')
                ->label('Assessment Notes')
                ->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period')
                    ->badge()->color('gray')->sortable(),

                Tables\Columns\TextColumn::make('period_type')
                    ->formatStateUsing(fn ($s) => ucfirst($s))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($s) => ucfirst($s))
                    ->color(fn ($state) => $state === 'finalized' ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('e_score')
                    ->label('E (40%)')
                    ->formatStateUsing(fn ($s) => $s ? number_format((float)$s, 1) . '%' : '—')
                    ->badge()->color('success'),

                Tables\Columns\TextColumn::make('s_score')
                    ->label('S (30%)')
                    ->formatStateUsing(fn ($s) => $s ? number_format((float)$s, 1) . '%' : '—')
                    ->badge()->color('info'),

                Tables\Columns\TextColumn::make('g_score')
                    ->label('G (30%)')
                    ->formatStateUsing(fn ($s) => $s ? number_format((float)$s, 1) . '%' : '—')
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('esg_mi')
                    ->label('ESG-MI')
                    ->formatStateUsing(fn ($s) => $s ? number_format((float)$s, 2) . '%' : '—')
                    ->badge()
                    ->color(fn ($record) => $record->esg_mi
                        ? EsgMaturityAssessment::emiToColor(EsgMaturityAssessment::emiToLevel((float) $record->esg_mi))
                        : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('esg_mi')
                    ->label('Level')
                    ->name('level')
                    ->formatStateUsing(fn ($s) => $s ? EsgMaturityAssessment::emiToLevel((float) $s) : '—')
                    ->badge()
                    ->color(fn ($record) => $record->esg_mi
                        ? EsgMaturityAssessment::emiToColor(EsgMaturityAssessment::emiToLevel((float) $record->esg_mi))
                        : 'gray'),

                Tables\Columns\TextColumn::make('assessedBy.name')
                    ->label('Assessed By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('assessed_at')
                    ->label('Date')->date('d M Y')->sortable(),
            ])
            ->defaultSort('period', 'desc')
            ->actions([
                Tables\Actions\Action::make('auto_fill')
                    ->label('Auto-Fill')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('This will overwrite auto-calculable scores with live data from the ERP. Manual scores (EWR, MRR) will not be changed.')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        $auto = EsgMaturityService::autoFill();
                        $updates = [];
                        $sources = [];
                        foreach ($auto as $key => $data) {
                            if ($data['score'] !== null) {
                                $updates["{$key}_score"] = $data['score'];
                                $sources[$key] = $data['source'];
                            }
                        }
                        $updates['auto_sources'] = $sources;
                        $record->update($updates);
                        EsgMaturityService::recalculate($record);
                        Notification::make()->title('Auto-fill complete — check scores then finalise')->success()->send();
                    }),

                Tables\Actions\Action::make('recalculate')
                    ->label('Recalculate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function ($record) {
                        EsgMaturityService::recalculate($record);
                        Notification::make()->title('ESG-MI recalculated')->success()->send();
                    }),

                Tables\Actions\Action::make('finalize')
                    ->label('Finalise')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        EsgMaturityService::recalculate($record);
                        $record->update(['status' => 'finalized', 'assessed_at' => now(), 'assessed_by_id' => auth()->id()]);
                        Notification::make()->title('ESG Maturity Assessment finalised')->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsgMaturityAssessments::route('/'),
            'create' => Pages\CreateEsgMaturityAssessment::route('/create'),
            'edit'   => Pages\EditEsgMaturityAssessment::route('/{record}/edit'),
        ];
    }
}
