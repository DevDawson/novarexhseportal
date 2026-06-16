<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazopStudyResource\Pages;
use App\Filament\Resources\HazopStudyResource\RelationManagers;
use App\Models\Department;
use App\Models\HazopStudy;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HazopStudyResource extends Resource
{
    protected static ?string $model = HazopStudy::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Risk Management';

    protected static ?string $navigationLabel = 'HAZOP Studies';

    protected static ?string $modelLabel = 'HAZOP Study';

    protected static ?string $pluralModelLabel = 'HAZOP Studies (Quantitative)';

    protected static ?int $navigationSort = 4;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage hazop') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage hazop') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage hazop') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_manager']) ?? false;
    }

    // ----------------------------------------------------------------
    // Form — Study master record
    // ----------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Study Information')
                ->description('Define the scope, P&ID reference, and team for this HAZOP study.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('study_ref')
                        ->label('Study Reference No.')
                        ->placeholder('Auto-generated on save')
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(HazopStudy::STATUS_LABELS)
                        ->default('draft')
                        ->required()
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('title')
                        ->label('Study Title')
                        ->placeholder('e.g. Offshore Wellhead Platform — Process HAZOP 2026')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('project_id')
                        ->label('Project / Facility')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company-wide / not project specific')
                        ->columnSpan(1),

                    Forms\Components\Select::make('department_id')
                        ->label('Responsible Department')
                        ->options(Department::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('facility_area')
                        ->label('Facility / Process Area')
                        ->maxLength(255)
                        ->placeholder('e.g. Gas compression train A')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('pid_reference')
                        ->label('P&ID / Drawing Reference')
                        ->maxLength(255)
                        ->placeholder('e.g. PID-NVX-2026-001 Rev.2')
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('study_date')
                        ->label('Study Date')
                        ->native(false)
                        ->default(now())
                        ->columnSpan(1),

                    Forms\Components\Select::make('facilitator_id')
                        ->label('HAZOP Facilitator')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Scope & Objectives')
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('study_scope')
                        ->label('Study Scope')
                        ->helperText('Define what is included and excluded from this HAZOP study.')
                        ->rows(3),

                    Forms\Components\Textarea::make('study_objectives')
                        ->label('Study Objectives')
                        ->helperText('What are the specific objectives and expected outcomes?')
                        ->rows(3),

                    Forms\Components\Textarea::make('process_description')
                        ->label('Process Description')
                        ->helperText('Describe the process, system, or activity being studied.')
                        ->rows(4),

                    Forms\Components\Textarea::make('team_members')
                        ->label('HAZOP Team Members')
                        ->helperText('List all team members, their roles and disciplines (one per line).')
                        ->rows(4)
                        ->placeholder("Process Engineer — Lead\nHSE Officer — Safety Representative\nInstrument Engineer\nOperations Supervisor"),
                ]),

            Forms\Components\Section::make('Review & Approval')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('reviewed_by_id')
                        ->label('Reviewed By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Review Date')
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Select::make('approved_by_id')
                        ->label('Approved By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('approval_date')
                        ->label('Approval Date')
                        ->native(false)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('approval_comments')
                        ->label('Approval Comments')
                        ->rows(3)
                        ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('study_ref')
                    ->label('Study Ref.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Study Title')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project / Facility')
                    ->placeholder('Company-wide')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('pid_reference')
                    ->label('P&ID Ref.')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('study_date')
                    ->label('Study Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('facilitator.name')
                    ->label('Facilitator')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nodes_count')
                    ->label('Nodes')
                    ->counts('nodes')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => HazopStudy::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'gray'    => ['draft', 'closed'],
                        'primary' => 'in_progress',
                        'info'    => 'complete',
                        'warning' => 'under_review',
                        'success' => 'approved',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(HazopStudy::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('active')
                    ->label('Active Studies (not closed)')
                    ->query(fn (Builder $query) => $query->whereNotIn('status', ['closed']))
                    ->toggle(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('download_procedure')
                    ->label('Download HAZOP Procedure (ISO)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn () => route('pdf.hazop.procedure'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Study Report')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.hazop.study', $record))
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
    // Relations & Pages
    // ----------------------------------------------------------------

    public static function getRelations(): array
    {
        return [
            RelationManagers\NodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHazopStudies::route('/'),
            'create' => Pages\CreateHazopStudy::route('/create'),
            'edit'   => Pages\EditHazopStudy::route('/{record}/edit'),
        ];
    }
}
