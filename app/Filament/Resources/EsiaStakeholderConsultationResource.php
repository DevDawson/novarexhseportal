<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaStakeholderConsultationResource\Pages;
use App\Models\EsiaStakeholderConsultation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaStakeholderConsultationResource extends Resource
{
    protected static ?string $model = EsiaStakeholderConsultation::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 9: Stakeholder Consultation';
    protected static ?string $modelLabel = 'Stakeholder Consultation';
    protected static ?int $navigationSort = 9;

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
                        ->searchable()->preload()->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('screening_id')
                        ->label('Linked Screening (optional)')
                        ->relationship('screening', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) =>
                            "Category {$record->category} — Score {$record->screening_score}"
                        )
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Select::make('consultation_type')
                        ->label('Consultation Method')
                        ->options(EsiaStakeholderConsultation::CONSULTATION_TYPE_LABELS)
                        ->default('public_meeting')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Consultation Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Consultation Title / Event Name')
                        ->required()->maxLength(255)->columnSpanFull(),

                    Forms\Components\TextInput::make('venue')
                        ->label('Venue / Location')
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('consultation_date')
                        ->label('Date of Consultation')
                        ->native(false),

                    Forms\Components\TextInput::make('number_attended')
                        ->label('Number of Participants')
                        ->numeric()->default(0)->minValue(0),

                    Forms\Components\TextInput::make('facilitator')
                        ->label('Facilitator / Moderator')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('stakeholder_groups')
                        ->label('Stakeholder Groups Consulted')
                        ->placeholder('e.g. Local residents, NGOs, Government agencies, Women groups, Youth')
                        ->rows(2)->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Agenda / Description')
                        ->rows(3)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Outcomes & Incorporation')
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('key_concerns_raised')
                        ->label('Key Concerns / Issues Raised by Stakeholders')
                        ->rows(4),

                    Forms\Components\Textarea::make('responses_given')
                        ->label('Responses / Commitments Given')
                        ->rows(4),

                    Forms\Components\Textarea::make('how_incorporated')
                        ->label('How Feedback Was Incorporated into ESIA')
                        ->rows(4),
                ]),

            Forms\Components\Section::make('Status & Documentation')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EsiaStakeholderConsultation::STATUS_LABELS)
                        ->default('planned')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('conducted_by')
                        ->label('Conducted / Recorded By')
                        ->relationship('conductedBy', 'name')
                        ->searchable()->preload(),

                    Forms\Components\FileUpload::make('minutes_file')
                        ->label('Meeting Minutes / Attendance Register')
                        ->directory('esia/consultations')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes')
                        ->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(28),

                Tables\Columns\TextColumn::make('title')
                    ->label('Event')->searchable()->limit(30),

                Tables\Columns\TextColumn::make('consultation_type')
                    ->label('Method')
                    ->badge()->color('primary')
                    ->formatStateUsing(fn (?string $s): string =>
                        EsiaStakeholderConsultation::CONSULTATION_TYPE_LABELS[$s] ?? ($s ?? '—')
                    ),

                Tables\Columns\TextColumn::make('number_attended')
                    ->label('Attended')
                    ->badge()->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EsiaStakeholderConsultation::STATUS_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EsiaStakeholderConsultation::STATUS_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('consultation_date')
                    ->label('Date')->date('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('consultation_type')
                    ->label('Method')
                    ->options(EsiaStakeholderConsultation::CONSULTATION_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(EsiaStakeholderConsultation::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\Filter::make('completed')
                    ->label('Completed Only')
                    ->query(fn ($q) => $q->where('status', 'completed'))
                    ->toggle(),
            ])
            ->defaultSort('consultation_date', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaStakeholderConsultations::route('/'),
            'create' => Pages\CreateEsiaStakeholderConsultation::route('/create'),
            'edit'   => Pages\EditEsiaStakeholderConsultation::route('/{record}/edit'),
        ];
    }
}
