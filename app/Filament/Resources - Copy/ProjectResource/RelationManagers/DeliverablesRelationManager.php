<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DeliverablesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliverables';

    protected static ?string $title = 'Deliverables';

    protected static ?string $recordTitleAttribute = 'document_title';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage deliverables') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('document_title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('document_code')
                    ->maxLength(255),

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
            ]),

            Forms\Components\FileUpload::make('file_path')
                ->label('Document File')
                ->directory('deliverables')
                ->openable()
                ->columnSpanFull(),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('prepared_by')
                    ->relationship('preparedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id()),

                Forms\Components\Select::make('reviewed_by')
                    ->relationship('reviewedBy', 'name')
                    ->searchable()
                    ->preload(),
            ]),

            Forms\Components\Grid::make(3)->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_title')
            ->columns([
                Tables\Columns\TextColumn::make('document_code')
                    ->label('Code')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('document_title')
                    ->limit(35)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('document_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->title()),

                Tables\Columns\TextColumn::make('revision_no')
                    ->label('Rev'),

                Tables\Columns\TextColumn::make('due_date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'gray' => 'draft',
                        'warning' => ['internal_review', 'client_review'],
                        'success' => 'approved',
                        'danger' => 'superseded',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
