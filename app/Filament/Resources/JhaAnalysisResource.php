<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JhaAnalysisResource\Pages;
use App\Filament\Resources\JhaAnalysisResource\RelationManagers;
use App\Models\JhaAnalysis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JhaAnalysisResource extends Resource
{
    protected static ?string $model          = JhaAnalysis::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup= 'Job Hazard Analysis (JHA)';
    protected static ?int    $navigationSort = 1;
    protected static ?string $navigationLabel= 'JHA Register';
    protected static ?string $modelLabel     = 'JHA';
    protected static ?string $pluralModelLabel = 'JHA Register';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole([
            'md', 'hse_manager', 'hse_staff', 'lead_auditor', 'supervisor', 'business_director',
        ]) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('JHA Information')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('jha_number')
                        ->label('JHA Number')
                        ->default(fn () => JhaAnalysis::nextNumber())
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('title')
                        ->label('Work Activity / Title')
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('location')
                        ->label('Work Location')
                        ->required(),

                    Forms\Components\DatePicker::make('work_date')
                        ->label('Work Date')
                        ->required()
                        ->native(false)
                        ->default(now()),

                    Forms\Components\Textarea::make('work_description')
                        ->label('Work Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('department')
                        ->label('Department'),

                    Forms\Components\Select::make('prepared_by')
                        ->label('Prepared By')
                        ->relationship('preparedBy', 'name')
                        ->searchable()
                        ->default(auth()->id()),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(JhaAnalysis::$statuses)
                        ->default('draft')
                        ->native(false)
                        ->required(),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn () => auth()->id()),
                ]),

            Forms\Components\Section::make('Step 11 — Worker Competency Overview')
                ->columns(3)
                ->description('Competency Compliance % = Qualified Workers ÷ Total Workers × 100')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('total_workers')
                        ->label('Total Workers on Job')
                        ->numeric()->minValue(0)->default(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $t = (int)$get('total_workers');
                            $q = (int)$get('qualified_workers');
                            $set('competency_compliance_pct', $t > 0 ? round($q / $t * 100, 2) : 0);
                        }),

                    Forms\Components\TextInput::make('qualified_workers')
                        ->label('Qualified Workers')
                        ->numeric()->minValue(0)->default(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $t = (int)$get('total_workers');
                            $q = (int)$get('qualified_workers');
                            $set('competency_compliance_pct', $t > 0 ? round($q / $t * 100, 2) : 0);
                        }),

                    Forms\Components\TextInput::make('competency_compliance_pct')
                        ->label('Competency Compliance %')
                        ->numeric()->disabled()->dehydrated()->suffix('%'),
                ]),

            Forms\Components\Section::make('Rejection Details')
                ->visible(fn ($record) => $record?->status === 'rejected')
                ->schema([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->rows(2)->disabled()->dehydrated(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jha_number')
                    ->label('JHA No.')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()->limit(35),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->placeholder('—')->toggleable(),

                Tables\Columns\TextColumn::make('location')->toggleable(),

                Tables\Columns\TextColumn::make('work_date')
                    ->label('Work Date')->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'authorized'           => 'success',
                        'pm_approved',
                        'client_approved'      => 'primary',
                        'hse_approved',
                        'supervisor_approved'  => 'info',
                        'submitted'            => 'warning',
                        'rejected'             => 'danger',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => JhaAnalysis::$statuses[$state] ?? $state),

                Tables\Columns\TextColumn::make('competency_compliance_pct')
                    ->label('Competency %')
                    ->suffix('%')->placeholder('—')->sortable()
                    ->color(fn ($state) => match (true) {
                        (float)$state >= 90 => 'success',
                        (float)$state >= 70 => 'warning',
                        default             => 'danger',
                    }),

                Tables\Columns\TextColumn::make('preparedBy.name')
                    ->label('Prepared By')->toggleable(),
            ])
            ->defaultSort('work_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(JhaAnalysis::$statuses),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title'),
            ])
            ->actions([
                Tables\Actions\Action::make('submit')
                    ->label('Submit')->icon('heroicon-o-paper-airplane')->color('info')
                    ->visible(fn ($r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->action(fn ($r) => $r->update(['status' => 'submitted'])),

                Tables\Actions\Action::make('supervisorApprove')
                    ->label('Supervisor ✓')->icon('heroicon-o-check-circle')->color('warning')
                    ->visible(fn ($r) => $r->status === 'submitted')
                    ->modalHeading('Supervisor Approval — Electronic Signature')
                    ->modalDescription('By approving, your name and timestamp are recorded as the electronic signature for this review stage.')
                    ->requiresConfirmation()
                    ->action(function ($r) {
                        $r->update([
                            'status' => 'supervisor_approved',
                            'supervisor_approved_by' => auth()->id(),
                            'supervisor_approved_at' => now(),
                        ]);
                        Notification::make()->title('Supervisor approval recorded.')->success()->send();
                    }),

                Tables\Actions\Action::make('hseApprove')
                    ->label('HSE ✓')->icon('heroicon-o-shield-check')->color('primary')
                    ->visible(fn ($r) => $r->status === 'supervisor_approved')
                    ->modalHeading('HSE Review — Electronic Signature')
                    ->modalDescription('By approving, your name and timestamp are recorded as the electronic signature for HSE review.')
                    ->requiresConfirmation()
                    ->action(function ($r) {
                        $r->update([
                            'status' => 'hse_approved',
                            'hse_approved_by' => auth()->id(),
                            'hse_approved_at' => now(),
                        ]);
                        Notification::make()->title('HSE approval recorded.')->success()->send();
                    }),

                Tables\Actions\Action::make('pmApprove')
                    ->label('PM ✓')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn ($r) => $r->status === 'hse_approved')
                    ->modalHeading('Project Manager Approval — Electronic Signature')
                    ->modalDescription('By approving, your name and timestamp are recorded as the electronic signature.')
                    ->requiresConfirmation()
                    ->action(function ($r) {
                        $r->update([
                            'status' => 'pm_approved',
                            'pm_approved_by' => auth()->id(),
                            'pm_approved_at' => now(),
                        ]);
                        Notification::make()->title('PM approval recorded.')->success()->send();
                    }),

                Tables\Actions\Action::make('authorize')
                    ->label('Authorize')->icon('heroicon-o-lock-open')->color('success')
                    ->visible(fn ($r) => in_array($r->status, ['pm_approved', 'client_approved']))
                    ->modalHeading('Final Authorization — Electronic Signature')
                    ->modalDescription('This will AUTHORIZE the JHA and permit work to commence. Your name and timestamp are recorded.')
                    ->requiresConfirmation()
                    ->action(function ($r) {
                        $r->update([
                            'status' => 'authorized',
                            'authorized_by' => auth()->id(),
                            'authorized_at' => now(),
                        ]);
                        Notification::make()->title('JHA Authorized — work may commence.')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($r) => !in_array($r->status, ['authorized', 'rejected', 'draft']))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()->rows(3),
                    ])
                    ->action(function ($r, array $data) {
                        $r->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()->title('JHA rejected.')->danger()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\HazardsRelationManager::class,
            RelationManagers\ControlMeasuresRelationManager::class,
            RelationManagers\EnvironmentRelationManager::class,
            RelationManagers\LegalRequirementsRelationManager::class,
            RelationManagers\CompetencyRequirementsRelationManager::class,
            RelationManagers\MonitoringChecksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListJhaAnalyses::route('/'),
            'create' => Pages\CreateJhaAnalysis::route('/create'),
            'edit'   => Pages\EditJhaAnalysis::route('/{record}/edit'),
        ];
    }
}
