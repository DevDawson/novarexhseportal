<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'HSE System';

    protected static ?int $navigationSort = 1;

    /**
     * Projects are the central record referenced by Incidents, Risks,
     * ESIA/Audits, Field Expenses, Invoices, and Deliverables - so most
     * operational roles need at least read access.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole([
            'md', 'business_director', 'hse_staff', 'accountant', 'hr_director',
        ]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'business_director', 'hse_staff']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'business_director', 'hse_staff']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('project_code')
                        ->label('Project Code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('e.g. WMC-2026-001 - unique reference used on invoices & deliverables.'),

                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->relationship('client', 'company_name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('service_type')
                        ->options([
                            'esia' => 'ESIA',
                            'environmental_audit' => 'Environmental Audit',
                            'social_audit' => 'Social Audit',
                            'training' => 'Training',
                            'monitoring' => 'Monitoring',
                            'consultancy' => 'Consultancy',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('project_manager_id')
                        ->label('Project Manager')
                        ->relationship('projectManager', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('location')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Timeline & Contract Value')
                ->columns(3)
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->native(false),

                    Forms\Components\DatePicker::make('end_date')
                        ->native(false)
                        ->afterOrEqual('start_date'),

                    Forms\Components\TextInput::make('contract_value')
                        ->label('Contract Value')
                        ->numeric()
                        ->prefix('TZS'),

                    Forms\Components\Select::make('status')
                        ->options([
                            'planning' => 'Planning',
                            'ongoing' => 'Ongoing',
                            'completed' => 'Completed',
                            'on_hold' => 'On Hold',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('planning')
                        ->required()
                        ->native(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('service_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'primary' => ['esia', 'environmental_audit', 'social_audit'],
                        'info' => ['training', 'monitoring'],
                        'gray' => ['consultancy', 'other'],
                    ]),

                Tables\Columns\TextColumn::make('projectManager.name')
                    ->label('PM')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contract_value')
                    ->money('TZS')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'planning',
                        'info' => 'ongoing',
                        'success' => 'completed',
                        'warning' => 'on_hold',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('service_type')
                    ->options([
                        'esia' => 'ESIA',
                        'environmental_audit' => 'Environmental Audit',
                        'social_audit' => 'Social Audit',
                        'training' => 'Training',
                        'monitoring' => 'Monitoring',
                        'consultancy' => 'Consultancy',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'company_name')
                    ->searchable(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Each manager appears as a tab on the Project Edit page. Visibility
     * within each manager is further scoped by its own canViewForRecord()
     * permission check, so a user without e.g. 'manage invoices' simply
     * won't see that tab.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\IncidentsRelationManager::class,
            RelationManagers\RisksRelationManager::class,
            RelationManagers\EsiaAuditsRelationManager::class,
            RelationManagers\FieldExpensesRelationManager::class,
            RelationManagers\DeliverablesRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
