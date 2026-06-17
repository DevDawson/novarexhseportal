<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaRegulatorySubmissionResource\Pages;
use App\Models\EsiaRegulatorySubmission;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaRegulatorySubmissionResource extends Resource
{
    protected static ?string $model = EsiaRegulatorySubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 11: Regulatory Submissions';
    protected static ?string $modelLabel = 'Regulatory Submission';
    protected static ?int $navigationSort = 11;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project & Report')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required(),

                    Forms\Components\Select::make('report_id')
                        ->label('Linked ESIA Report (optional)')
                        ->relationship('report', 'report_title')
                        ->searchable()
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Submission Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('regulatory_authority')
                        ->label('Regulatory Authority')
                        ->default('NEMC')
                        ->required()->maxLength(255),

                    Forms\Components\Select::make('submission_type')
                        ->label('Submission Type')
                        ->options(EsiaRegulatorySubmission::SUBMISSION_TYPE_LABELS)
                        ->default('draft_eia')->required()->native(false),

                    Forms\Components\TextInput::make('reference_number')
                        ->label('Authority Reference Number')
                        ->placeholder('e.g. NEMC/EIA/2026/0123')
                        ->maxLength(100),

                    Forms\Components\Select::make('submitted_by')
                        ->label('Submitted By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\DatePicker::make('submitted_at')
                        ->label('Submission Date')->native(false),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EsiaRegulatorySubmission::STATUS_LABELS)
                        ->default('draft')->required()->native(false),

                    Forms\Components\Textarea::make('submission_notes')
                        ->label('Submission Notes')
                        ->rows(3)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Authority Review & Decision')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('decision_date')
                        ->label('Decision Date')->native(false),

                    Forms\Components\DatePicker::make('approval_expiry_date')
                        ->label('Approval Expiry Date')->native(false),

                    Forms\Components\Textarea::make('review_comments')
                        ->label('Review Comments from Authority')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('approval_conditions')
                        ->label('Approval Conditions / Requirements')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\FileUpload::make('certificate_file')
                        ->label('Approval Certificate / Letter')
                        ->directory('esia/certificates')
                        ->acceptedFileTypes(['application/pdf'])
                        ->openable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(25),

                Tables\Columns\TextColumn::make('regulatory_authority')
                    ->label('Authority')->badge()->color('gray'),

                Tables\Columns\BadgeColumn::make('submission_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($s) => EsiaRegulatorySubmission::SUBMISSION_TYPE_LABELS[$s] ?? $s)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Ref No.')->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($s) => EsiaRegulatorySubmission::STATUS_LABELS[$s] ?? $s)
                    ->colors([
                        'gray'    => 'draft',
                        'info'    => 'submitted',
                        'primary' => 'under_review',
                        'warning' => 'additional_info_required',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('approval_expiry_date')
                    ->label('Expiry')
                    ->date('d M Y')
                    ->color(fn ($record) =>
                        $record->is_approval_expired ? 'danger' :
                        ($record->is_approval_expiring ? 'warning' : 'gray')
                    )
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EsiaRegulatorySubmission::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('submission_type')
                    ->options(EsiaRegulatorySubmission::SUBMISSION_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Approval Expiring ≤60 Days')
                    ->query(fn ($q) => $q
                        ->where('status', 'approved')
                        ->whereNotNull('approval_expiry_date')
                        ->where('approval_expiry_date', '>', now())
                        ->where('approval_expiry_date', '<=', now()->addDays(60))
                    )
                    ->toggle(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaRegulatorySubmissions::route('/'),
            'create' => Pages\CreateEsiaRegulatorySubmission::route('/create'),
            'edit'   => Pages\EditEsiaRegulatorySubmission::route('/{record}/edit'),
        ];
    }
}
