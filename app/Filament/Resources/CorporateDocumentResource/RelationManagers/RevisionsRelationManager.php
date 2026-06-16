<?php

namespace App\Filament\Resources\CorporateDocumentResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision History';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('revision_number')
                ->required()
                ->maxLength(20)
                ->placeholder('e.g. Rev 1'),

            Forms\Components\Select::make('status')
                ->options(['draft' => 'Draft', 'under_review' => 'Under Review', 'approved' => 'Approved', 'superseded' => 'Superseded'])
                ->required()
                ->native(false),

            Forms\Components\DatePicker::make('revision_date')
                ->required()
                ->native(false),

            Forms\Components\Textarea::make('revision_reason')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Select::make('revised_by_id')
                ->label('Revised By')
                ->options(User::all()->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->default(fn () => auth()->id())
                ->native(false),

            Forms\Components\Select::make('reviewed_by_id')
                ->label('Reviewed By')
                ->options(User::all()->pluck('name', 'id'))
                ->searchable()
                ->native(false),

            Forms\Components\Select::make('approved_by_id')
                ->label('Approved By')
                ->options(User::all()->pluck('name', 'id'))
                ->searchable()
                ->native(false),

            Forms\Components\DatePicker::make('approved_date')
                ->native(false),

            Forms\Components\FileUpload::make('file_path')
                ->label('Revised Document File')
                ->directory('document-revisions')
                ->openable(),

            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revision_number')->label('Revision')->sortable(),
                Tables\Columns\TextColumn::make('revision_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('revision_reason')->limit(40),
                Tables\Columns\TextColumn::make('revisedBy.name')->label('Revised By'),
                Tables\Columns\TextColumn::make('reviewedBy.name')->label('Reviewed By')->toggleable(),
                Tables\Columns\TextColumn::make('approvedBy.name')->label('Approved By')->toggleable(),
                Tables\Columns\TextColumn::make('approved_date')->date('d M Y')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['gray' => 'draft', 'warning' => 'under_review', 'success' => 'approved', 'danger' => 'superseded'])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->defaultSort('revision_date', 'desc');
    }
}
