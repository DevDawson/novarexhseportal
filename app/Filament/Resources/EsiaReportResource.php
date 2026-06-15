<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaReportResource\Pages;
use App\Models\EsiaReport;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaReportResource extends Resource
{
    protected static ?string $model = EsiaReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 10: ESIA Reports';
    protected static ?string $modelLabel = 'ESIA Report';
    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Report Identity')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('report_title')
                        ->label('Report Title')
                        ->required()->maxLength(255)->columnSpanFull(),

                    Forms\Components\Select::make('report_type')
                        ->label('Report Type')
                        ->options(EsiaReport::REPORT_TYPE_LABELS)
                        ->default('draft_esia')->required()->native(false),

                    Forms\Components\TextInput::make('version')
                        ->label('Version')
                        ->default('1.0')->maxLength(20),

                    Forms\Components\Select::make('author_id')
                        ->label('Author / Lead Assessor')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\DatePicker::make('date_prepared')
                        ->label('Date Prepared')->native(false),
                ]),

            Forms\Components\Section::make('Executive Summary')
                ->schema([
                    Forms\Components\Textarea::make('executive_summary')
                        ->label('Executive Summary')
                        ->rows(6)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Document & Review')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('document_file')
                        ->label('Report Document')
                        ->directory('esia/reports')
                        ->acceptedFileTypes(['application/pdf', 'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(EsiaReport::STATUS_LABELS)
                        ->default('draft')->required()->native(false),

                    Forms\Components\Select::make('reviewed_by')
                        ->label('Reviewed By')
                        ->options(User::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Review Date')->native(false),

                    Forms\Components\Textarea::make('review_comments')
                        ->label('Review Comments')
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

                Tables\Columns\TextColumn::make('report_title')
                    ->label('Title')->searchable()->limit(35),

                Tables\Columns\BadgeColumn::make('report_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($s) => EsiaReport::REPORT_TYPE_LABELS[$s] ?? $s)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('version')
                    ->label('v')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($s) => EsiaReport::STATUS_LABELS[$s] ?? $s)
                    ->colors([
                        'gray'    => 'draft',
                        'primary' => 'peer_review',
                        'info'    => 'final',
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('date_prepared')
                    ->label('Date')
                    ->date('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('report_type')
                    ->options(EsiaReport::REPORT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(EsiaReport::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('date_prepared', 'desc')
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.esia.report', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaReports::route('/'),
            'create' => Pages\CreateEsiaReport::route('/create'),
            'edit'   => Pages\EditEsiaReport::route('/{record}/edit'),
        ];
    }
}
