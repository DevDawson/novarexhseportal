<?php

namespace App\Filament\Resources\EnvironmentalAuditResource\RelationManagers;

use App\Models\EnvironmentalAuditChecklistItem;
use App\Services\EnvironmentalAuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChecklistItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistItems';
    protected static ?string $title = 'Checklist Assessment (42 Items)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Item Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('item_code')
                        ->label('Item Code')->disabled(),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options(EnvironmentalAuditService::categoryLabels())
                        ->disabled(),

                    Forms\Components\Textarea::make('item_description')
                        ->label('Requirement')
                        ->rows(2)->disabled()->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Assessment')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('compliance_status')
                        ->label('Compliance Status')
                        ->options(EnvironmentalAuditChecklistItem::COMPLIANCE_STATUS_LABELS)
                        ->default('not_applicable')
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('evidence_notes')
                        ->label('Objective Evidence / Observations')
                        ->placeholder('Describe the evidence reviewed (documents, records, interviews, observations)')
                        ->rows(4),

                    Forms\Components\FileUpload::make('evidence_file')
                        ->label('Evidence File (Photo / Document)')
                        ->directory('audits/checklist-evidence')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->openable(),

                    Forms\Components\Textarea::make('findings_notes')
                        ->label('Findings / Non-Conformance Notes')
                        ->placeholder('Note any gaps, deviations, or findings observed')
                        ->rows(3),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_code')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->label('Code')
                    ->badge()->color('gray')
                    ->width('70px'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Cat.')
                    ->badge()->color('primary')
                    ->width('60px'),

                Tables\Columns\TextColumn::make('item_description')
                    ->label('Requirement')
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('compliance_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EnvironmentalAuditChecklistItem::COMPLIANCE_STATUS_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EnvironmentalAuditChecklistItem::COMPLIANCE_STATUS_COLORS[$state] ?? 'gray'
                    )
                    ->width('160px'),

                Tables\Columns\IconColumn::make('evidence_notes')
                    ->label('Evidence')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->evidence_notes))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->width('70px'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(EnvironmentalAuditService::categoryLabels()),

                Tables\Filters\SelectFilter::make('compliance_status')
                    ->label('Status')
                    ->options(EnvironmentalAuditChecklistItem::COMPLIANCE_STATUS_LABELS),

                Tables\Filters\Filter::make('needs_assessment')
                    ->label('Pending Assessment Only')
                    ->query(fn ($q) => $q->where('compliance_status', 'not_applicable'))
                    ->toggle(),

                Tables\Filters\Filter::make('non_compliant')
                    ->label('Non-Compliant Only')
                    ->query(fn ($q) => $q->where('compliance_status', 'non_compliant'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Custom Item'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Assess'),
            ])
            ->bulkActions([]);
    }
}
