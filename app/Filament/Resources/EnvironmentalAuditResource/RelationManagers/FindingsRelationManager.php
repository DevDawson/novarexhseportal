<?php

namespace App\Filament\Resources\EnvironmentalAuditResource\RelationManagers;

use App\Models\EnvironmentalAuditFinding;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'findings';
    protected static ?string $title = 'Audit Findings';

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('3.1 Finding Identification')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('finding_number')
                        ->label('Finding No.')
                        ->placeholder('Auto-generated')
                        ->maxLength(20)
                        ->disabled(fn ($operation) => $operation === 'edit'),

                    Forms\Components\Select::make('finding_type')
                        ->label('Finding Type')
                        ->options(EnvironmentalAuditFinding::FINDING_TYPE_LABELS)
                        ->default('minor_nc')->required()->native(false),

                    Forms\Components\TextInput::make('clause_reference')
                        ->label('ISO / Legal Clause Reference')
                        ->placeholder('e.g. ISO 14001 §6.1.3, NEMC Reg. 2007 §15')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('process_area')
                        ->label('Process / Area')
                        ->placeholder('e.g. Waste yard, Monitoring lab, Site office')
                        ->maxLength(255),

                    Forms\Components\Select::make('checklist_item_id')
                        ->label('Linked Checklist Item (optional)')
                        ->relationship('checklistItem', 'item_code')
                        ->getOptionLabelFromRecordUsing(fn ($record) =>
                            "[{$record->item_code}] {$record->item_description}"
                        )
                        ->searchable()
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('3.2–3.3 Finding Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Finding Description')
                        ->rows(4)->required()->columnSpanFull(),

                    Forms\Components\Textarea::make('objective_evidence')
                        ->label('Objective Evidence (Photos / Documents / Records / Interviews)')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('root_cause_analysis')
                        ->label('Root Cause Analysis (5 Whys / Fishbone)')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Select::make('environmental_impact_category')
                        ->label('Environmental Impact Category')
                        ->options(EnvironmentalAuditFinding::IMPACT_CATEGORY_LABELS)
                        ->nullable()->native(false),
                ]),

            Forms\Components\Section::make('3.4 Risk Evaluation')
                ->columns(4)
                ->schema([
                    Forms\Components\Select::make('likelihood')
                        ->label('Likelihood (1–5)')
                        ->options([1 => '1 — Rare', 2 => '2 — Unlikely', 3 => '3 — Possible', 4 => '4 — Likely', 5 => '5 — Almost Certain'])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                            $set('risk_score', ((int)$get('likelihood') ?: 1) * ((int)$get('severity') ?: 1))
                        ),

                    Forms\Components\Select::make('severity')
                        ->label('Severity (1–5)')
                        ->options([1 => '1 — Negligible', 2 => '2 — Minor', 3 => '3 — Moderate', 4 => '4 — Major', 5 => '5 — Catastrophic'])
                        ->default(1)->required()->native(false)->live()
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                            $set('risk_score', ((int)$get('likelihood') ?: 1) * ((int)$get('severity') ?: 1))
                        ),

                    Forms\Components\TextInput::make('risk_score')
                        ->label('Risk Score (L×S)')
                        ->numeric()->readOnly()->default(1),

                    Forms\Components\Toggle::make('regulatory_impact')
                        ->label('Regulatory Impact?')
                        ->default(false),
                ]),

            Forms\Components\Section::make('3.5 Corrective Action Management')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('recommended_action')
                        ->label('Recommended Corrective Action')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\TextInput::make('action_owner')
                        ->label('Action Owner')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('department_responsible')
                        ->label('Department Responsible')
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('target_completion_date')
                        ->label('Target Completion Date')->native(false),

                    Forms\Components\Select::make('priority_level')
                        ->label('Priority Level')
                        ->options(EnvironmentalAuditFinding::PRIORITY_LABELS)
                        ->default('medium')->required()->native(false),

                    Forms\Components\Select::make('action_status')
                        ->label('Action Status')
                        ->options(EnvironmentalAuditFinding::ACTION_STATUS_LABELS)
                        ->default('open')->required()->native(false)->live(),

                    Forms\Components\Toggle::make('effectiveness_verified')
                        ->label('Effectiveness Verified?')
                        ->default(false)
                        ->visible(fn (Forms\Get $get) => $get('action_status') === 'closed'),

                    Forms\Components\Textarea::make('effectiveness_notes')
                        ->label('Effectiveness Verification Notes')
                        ->rows(3)->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => $get('action_status') === 'closed'),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('finding_number')
            ->defaultSort('finding_number')
            ->columns([
                Tables\Columns\TextColumn::make('finding_number')
                    ->label('No.')
                    ->badge()->color('gray')
                    ->width('80px'),

                Tables\Columns\TextColumn::make('finding_type')
                    ->label('Type')->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EnvironmentalAuditFinding::FINDING_TYPE_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EnvironmentalAuditFinding::FINDING_TYPE_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('description')
                    ->label('Finding')->limit(45)->wrap(),

                Tables\Columns\TextColumn::make('risk_level')
                    ->label('Risk')->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EnvironmentalAuditFinding::RISK_LEVEL_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EnvironmentalAuditFinding::RISK_LEVEL_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('action_owner')
                    ->label('Owner')->limit(20)->toggleable(),

                Tables\Columns\TextColumn::make('action_status')
                    ->label('Status')->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EnvironmentalAuditFinding::ACTION_STATUS_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EnvironmentalAuditFinding::ACTION_STATUS_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('target_completion_date')
                    ->label('Due')->date('d M Y')->sortable()
                    ->color(fn ($record) =>
                        ($record->action_status !== 'closed' && $record->target_completion_date?->isPast())
                            ? 'danger' : 'gray'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('finding_type')
                    ->options(EnvironmentalAuditFinding::FINDING_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('action_status')
                    ->options(EnvironmentalAuditFinding::ACTION_STATUS_LABELS),

                Tables\Filters\SelectFilter::make('risk_level')
                    ->options(EnvironmentalAuditFinding::RISK_LEVEL_LABELS),

                Tables\Filters\Filter::make('open_high_risk')
                    ->label('Open High/Critical Risk')
                    ->query(fn ($q) => $q
                        ->whereIn('risk_level', ['high', 'critical'])
                        ->where('action_status', '!=', 'closed')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('regulatory')
                    ->label('Regulatory Impact Only')
                    ->query(fn ($q) => $q->where('regulatory_impact', true))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Finding'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
