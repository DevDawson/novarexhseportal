<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaScopingIssueResource\Pages;
use App\Models\EsiaScopingIssue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaScopingIssueResource extends Resource
{
    protected static ?string $model = EsiaScopingIssue::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 3: Scoping';
    protected static ?string $modelLabel = 'Scoping Issue';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project & Screening Link')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required(),

                    Forms\Components\Select::make('screening_id')
                        ->label('Linked Screening (optional)')
                        ->relationship('screening', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) =>
                            "Category {$record->category} — Score {$record->screening_score}"
                        )
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Issue Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('issue_type')
                        ->label('Issue Type')
                        ->options(EsiaScopingIssue::ISSUE_TYPE_LABELS)
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('issue_title')
                        ->label('Issue Title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('data_required')
                        ->label('Data / Information Required')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('methodology')
                        ->label('Proposed Assessment Methodology')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Inclusion Decision')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('responsible_expert')
                        ->label('Responsible Expert / Consultant')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('included_in_scope')
                        ->label('Included in Scope')
                        ->default(true)
                        ->live(),

                    Forms\Components\Textarea::make('exclusion_justification')
                        ->label('Exclusion Justification')
                        ->rows(3)
                        ->columnSpanFull()
                        ->hidden(fn (Forms\Get $get) => (bool)$get('included_in_scope')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(30),

                Tables\Columns\BadgeColumn::make('issue_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string =>
                        EsiaScopingIssue::ISSUE_TYPE_LABELS[$state] ?? $state
                    )
                    ->color('primary'),

                Tables\Columns\TextColumn::make('issue_title')
                    ->label('Issue')->searchable()->limit(40),

                Tables\Columns\IconColumn::make('included_in_scope')
                    ->label('In Scope')
                    ->boolean(),

                Tables\Columns\TextColumn::make('responsible_expert')
                    ->label('Expert')
                    ->limit(25)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('issue_type')
                    ->label('Type')
                    ->options(EsiaScopingIssue::ISSUE_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('included_only')
                    ->label('In-Scope Issues Only')
                    ->query(fn ($query) => $query->where('included_in_scope', true))
                    ->toggle(),
            ])
            ->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaScopingIssues::route('/'),
            'create' => Pages\CreateEsiaScopingIssue::route('/create'),
            'edit'   => Pages\EditEsiaScopingIssue::route('/{record}/edit'),
        ];
    }
}
