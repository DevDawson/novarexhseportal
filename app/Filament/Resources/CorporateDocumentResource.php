<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorporateDocumentResource\Pages;
use App\Filament\Resources\CorporateDocumentResource\RelationManagers\RevisionsRelationManager;
use App\Models\CorporateDocument;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CorporateDocumentResource extends Resource
{
    protected static ?string $model = CorporateDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Document Control';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $modelLabel = 'Document';

    protected static ?string $pluralModelLabel = 'Document Register';

    /**
     * Corporate documents (policies, certificates, licenses) are
     * referenced across departments - most senior roles can view,
     * but only MD/Secretary/HR maintain them.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage corporate_documents') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage corporate_documents') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage corporate_documents') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Document Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('category')
                        ->options([
                            'policy' => 'Policy',
                            'procedure' => 'Procedure',
                            'work_instruction' => 'Work Instruction',
                            'form' => 'Form / Template',
                            'certificate' => 'Certificate',
                            'license' => 'License',
                            'manual' => 'Manual',
                            'sop' => 'SOP',
                            'register' => 'Register',
                            'other' => 'Other',
                        ])
                        ->default('other')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('document_number')
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('issue_date')
                        ->native(false),

                    Forms\Components\DatePicker::make('expiry_date')
                        ->native(false)
                        ->afterOrEqual('issue_date')
                        ->helperText('Leave blank for documents that do not expire (e.g. internal policies/manuals).'),

                    Forms\Components\FileUpload::make('file_path')
                        ->label('Document File')
                        ->directory('corporate-documents')
                        ->openable()
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'archived' => 'Archived',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),

                    Forms\Components\Hidden::make('uploaded_by')
                        ->default(fn () => auth()->id()),

                    Forms\Components\TextInput::make('current_revision')
                        ->label('Current Revision')
                        ->maxLength(20)
                        ->placeholder('e.g. Rev 0'),

                    Forms\Components\TextInput::make('document_owner')
                        ->label('Document Owner / Author')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('distribution_list')
                        ->label('Distribution List')
                        ->maxLength(500)
                        ->helperText('Roles or departments that receive this document'),

                    Forms\Components\DatePicker::make('next_review_date')
                        ->label('Next Review Date')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn (string $state): string => str($state)->upper())
                    ->colors([
                        'primary' => ['policy', 'manual', 'sop'],
                        'success' => 'certificate',
                        'warning' => 'license',
                        'gray' => 'other',
                    ]),

                Tables\Columns\TextColumn::make('document_number')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->date('d M Y')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('No expiry')
                    ->badge()
                    ->color(function (?\Illuminate\Support\Carbon $state): string {
                        if (! $state) {
                            return 'gray';
                        }

                        if ($state->isPast()) {
                            return 'danger';
                        }

                        if ($state->diffInDays(now()) <= 30) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('current_revision')->label('Revision')->toggleable(),
                Tables\Columns\TextColumn::make('document_owner')->label('Owner')->toggleable(),
                Tables\Columns\TextColumn::make('next_review_date')->date('d M Y')->label('Next Review')->toggleable(),
                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                        'gray' => 'archived',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'policy' => 'Policy', 'procedure' => 'Procedure', 'work_instruction' => 'Work Instruction',
                        'form' => 'Form', 'certificate' => 'Certificate', 'license' => 'License',
                        'manual' => 'Manual', 'sop' => 'SOP', 'register' => 'Register', 'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiring within 30 days')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                        ->whereNotNull('expiry_date')
                        ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                    ),
            ])
            ->defaultSort('expiry_date', 'asc')
            ->actions([
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
            RevisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorporateDocuments::route('/'),
            'create' => Pages\CreateCorporateDocument::route('/create'),
            'edit' => Pages\EditCorporateDocument::route('/{record}/edit'),
        ];
    }
}
