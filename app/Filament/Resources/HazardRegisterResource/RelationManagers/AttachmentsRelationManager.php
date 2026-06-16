<?php

namespace App\Filament\Resources\HazardRegisterResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $recordTitleAttribute = 'file_name';

    protected static ?string $title = 'Attachments & Evidence (HAZID_ATTACHMENTS)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('file_path')
                ->label('File')
                ->required()
                ->disk('public')
                ->directory('hazard-attachments')
                ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->maxSize(20480)
                ->columnSpanFull()
                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                    if ($state) {
                        $set('file_name', basename($state));
                        $ext = strtolower(pathinfo($state, PATHINFO_EXTENSION));
                        $set('file_type', $ext);
                    }
                }),

            Forms\Components\TextInput::make('file_name')
                ->label('File Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('file_type')
                ->label('File Type / Extension')
                ->maxLength(50)
                ->placeholder('e.g. pdf, jpg, xlsx'),

            Forms\Components\Select::make('attachment_type')
                ->label('Attachment Type')
                ->options([
                    'inspection_report' => 'Inspection Report',
                    'photograph'        => 'Photograph',
                    'training_record'   => 'Training Record',
                    'work_permit'       => 'Work Permit',
                    'certificate'       => 'Certificate',
                    'test_report'       => 'Test Report',
                    'other'             => 'Other',
                ])
                ->default('other')
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('description')
                ->label('Description')
                ->maxLength(255)
                ->placeholder('Brief description of the document')
                ->columnSpanFull(),

            Forms\Components\Select::make('uploaded_by_id')
                ->label('Uploaded By')
                ->options(User::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->default(fn () => auth()->id())
                ->required(),

            Forms\Components\DatePicker::make('upload_date')
                ->label('Upload Date')
                ->native(false)
                ->default(now())
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('attachment_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inspection_report' => 'Inspection Report',
                        'photograph'        => 'Photograph',
                        'training_record'   => 'Training Record',
                        'work_permit'       => 'Work Permit',
                        'certificate'       => 'Certificate',
                        'test_report'       => 'Test Report',
                        default             => 'Other',
                    })
                    ->colors([
                        'info'    => ['inspection_report', 'test_report'],
                        'success' => ['certificate', 'training_record'],
                        'warning' => 'photograph',
                        'primary' => 'work_permit',
                        'gray'    => 'other',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('upload_date')
                    ->label('Upload Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Ext.')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('attachment_type')
                    ->label('Attachment Type')
                    ->options([
                        'inspection_report' => 'Inspection Report',
                        'photograph'        => 'Photograph',
                        'training_record'   => 'Training Record',
                        'work_permit'       => 'Work Permit',
                        'certificate'       => 'Certificate',
                        'test_report'       => 'Test Report',
                        'other'             => 'Other',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by_id'] = $data['uploaded_by_id'] ?? auth()->id();
                        $data['upload_date'] = $data['upload_date'] ?? now()->toDateString();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('upload_date', 'desc');
    }
}
