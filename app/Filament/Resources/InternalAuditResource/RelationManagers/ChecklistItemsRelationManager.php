<?php

namespace App\Filament\Resources\InternalAuditResource\RelationManagers;

use App\Models\AuditChecklistItem;
use App\Services\AuditManagementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChecklistItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistItems';

    protected static ?string $title = 'Audit Checklist';

    protected static ?string $recordTitleAttribute = 'clause_reference';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage audits') ?? false;
    }

    // ----------------------------------------------------------------
    // Form
    // ----------------------------------------------------------------

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('iso_standard')
                    ->label('ISO Standard')
                    ->options(AuditManagementService::standardLabels())
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('clause_reference')
                    ->label('Clause Reference')
                    ->placeholder('e.g. ISO 9001:8.5.1')
                    ->maxLength(30),
            ]),

            Forms\Components\Textarea::make('question')
                ->label('Audit Question / Requirement')
                ->rows(3)
                ->required()
                ->columnSpanFull(),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('requirement_type')
                    ->label('Requirement Type')
                    ->options(AuditChecklistItem::REQUIREMENT_LABELS)
                    ->default('mandatory')
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('response')
                    ->label('Auditor Response')
                    ->options(AuditChecklistItem::RESPONSE_LABELS)
                    ->default('not_assessed')
                    ->required()
                    ->native(false)
                    ->live(),

                Forms\Components\TextInput::make('score')
                    ->label('Score (0–5)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->placeholder('0 = NC, 5 = Full compliance')
                    ->visible(fn (Forms\Get $get) => ! in_array($get('response'), ['not_assessed', 'not_applicable'])),
            ]),

            Forms\Components\Textarea::make('evidence_notes')
                ->label('Evidence / Objective Evidence')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('auditor_notes')
                ->label('Auditor Notes')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    // ----------------------------------------------------------------
    // Table
    // ----------------------------------------------------------------

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                Tables\Columns\BadgeColumn::make('iso_standard')
                    ->label('Standard')
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('iso', 'ISO ', $state)))
                    ->colors([
                        'primary' => 'iso9001',
                        'success' => 'iso14001',
                        'warning' => 'iso45001',
                        'info'    => 'iso50001',
                        'gray'    => 'other',
                    ]),

                Tables\Columns\TextColumn::make('clause_reference')
                    ->label('Clause')
                    ->searchable()
                    ->width('140px'),

                Tables\Columns\TextColumn::make('question')
                    ->label('Requirement')
                    ->limit(70)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('response')
                    ->label('Response')
                    ->formatStateUsing(fn (string $state): string => AuditChecklistItem::RESPONSE_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'compliant',
                        'danger'  => 'non_compliant',
                        'warning' => 'observation',
                        'gray'    => fn ($state) => in_array($state, ['not_applicable', 'not_assessed']),
                    ]),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->placeholder('—')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('evidence_notes')
                    ->label('Evidence')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->getStateUsing(fn ($record) => ! empty($record->evidence_notes)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('iso_standard')
                    ->label('Standard')
                    ->options(AuditManagementService::standardLabels()),

                Tables\Filters\SelectFilter::make('response')
                    ->options(AuditChecklistItem::RESPONSE_LABELS),

                Tables\Filters\Filter::make('pending')
                    ->label('Pending Assessment Only')
                    ->query(fn ($query) => $query->where('response', 'not_assessed')),

                Tables\Filters\Filter::make('non_compliant')
                    ->label('Non-Compliant Only')
                    ->query(fn ($query) => $query->where('response', 'non_compliant')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('seed_iso9001')
                    ->label('+ ISO 9001 Checklist')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(fn ($livewire) => self::seedTemplate($livewire, 'iso9001')),

                Tables\Actions\Action::make('seed_iso14001')
                    ->label('+ ISO 14001 Checklist')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($livewire) => self::seedTemplate($livewire, 'iso14001')),

                Tables\Actions\Action::make('seed_iso45001')
                    ->label('+ ISO 45001 Checklist')
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn ($livewire) => self::seedTemplate($livewire, 'iso45001')),

                Tables\Actions\Action::make('seed_iso50001')
                    ->label('+ ISO 50001 Checklist')
                    ->icon('heroicon-o-plus-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(fn ($livewire) => self::seedTemplate($livewire, 'iso50001')),

                Tables\Actions\CreateAction::make()->label('Add Custom Item'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Assess'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    // ----------------------------------------------------------------
    // Seed ISO template items into the audit
    // ----------------------------------------------------------------

    private static function seedTemplate($livewire, string $standard): void
    {
        $audit = $livewire->getOwnerRecord();
        $items = AuditManagementService::checklistTemplate($standard);

        $now  = now();
        $rows = array_map(fn ($item) => array_merge($item, [
            'internal_audit_id' => $audit->id,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]), $items);

        \Illuminate\Support\Facades\DB::table('audit_checklist_items')->insert($rows);
    }
}
