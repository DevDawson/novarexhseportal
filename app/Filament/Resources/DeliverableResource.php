<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliverableResource\Pages;
use App\Filament\Resources\DeliverableResource\RelationManagers;
use App\Models\Deliverable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliverableResource extends Resource
{
    protected static ?string $model = Deliverable::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Project Deliverables';

    protected static ?string $modelLabel = 'Deliverable';

    /**
     * Document Control register - HSE Staff prepare/manage, MD has oversight.
     * Business Director may need to track deliverables for client reporting.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage deliverables') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage deliverables') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage deliverables') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Document Information')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('document_title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('document_code')
                        ->maxLength(255)
                        ->helperText('e.g. WMC-PRJ001-RPT-001'),

                    Forms\Components\Select::make('document_type')
                        ->options([
                            'report' => 'Report',
                            'drawing' => 'Drawing',
                            'plan' => 'Plan',
                            'certificate' => 'Certificate',
                            'correspondence' => 'Correspondence',
                            'other' => 'Other',
                        ])
                        ->default('report')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('revision_no')
                        ->default('A')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\FileUpload::make('file_path')
                        ->label('Document File')
                        ->directory('deliverables')
                        ->openable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Workflow & Status')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('prepared_by')
                        ->label('Prepared By')
                        ->relationship('preparedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    Forms\Components\Select::make('reviewed_by')
                        ->label('Reviewed By')
                        ->relationship('reviewedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'internal_review' => 'Internal Review',
                            'client_review' => 'Client Review',
                            'approved' => 'Approved',
                            'superseded' => 'Superseded',
                        ])
                        ->default('draft')
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('due_date')
                        ->native(false),

                    Forms\Components\DatePicker::make('submission_date')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('document_title')
                    ->limit(35)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('document_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->title())
                    ->colors([
                        'primary' => ['report', 'plan'],
                        'info' => 'drawing',
                        'success' => 'certificate',
                        'gray' => ['correspondence', 'other'],
                    ]),

                Tables\Columns\TextColumn::make('revision_no')
                    ->label('Rev'),

                Tables\Columns\TextColumn::make('preparedBy.name')
                    ->label('Prepared By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('due_date')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (?\Illuminate\Support\Carbon $state, Deliverable $record): string => match (true) {
                        $state === null => 'gray',
                        $record->status === 'approved' => 'success',
                        $state->isPast() => 'danger',
                        $state->diffInDays(now()) <= 7 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'gray' => 'draft',
                        'warning' => ['internal_review', 'client_review'],
                        'success' => 'approved',
                        'danger' => 'superseded',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'report' => 'Report',
                        'drawing' => 'Drawing',
                        'plan' => 'Plan',
                        'certificate' => 'Certificate',
                        'correspondence' => 'Correspondence',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'internal_review' => 'Internal Review',
                        'client_review' => 'Client Review',
                        'approved' => 'Approved',
                        'superseded' => 'Superseded',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue (past due date, not approved)')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', '<', now())
                        ->where('status', '!=', 'approved')
                    ),
            ])
            ->defaultSort('due_date', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RevisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliverables::route('/'),
            'create' => Pages\CreateDeliverable::route('/create'),
            'edit' => Pages\EditDeliverable::route('/{record}/edit'),
        ];
    }
}
