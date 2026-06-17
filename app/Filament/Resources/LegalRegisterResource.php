<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalRegisterResource\Pages;
use App\Models\LegalRegisterItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LegalRegisterResource extends Resource
{
    protected static ?string $model = LegalRegisterItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Environmental Management (EMS)';

    protected static ?string $navigationLabel = 'Legal & Compliance Register';

    protected static ?string $modelLabel = 'Legal Requirement';

    protected static ?string $pluralModelLabel = 'Legal & Compliance Register';

    protected static ?int $navigationSort = 2;

    // ----------------------------------------------------------------
    // Access Control
    // ----------------------------------------------------------------

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage legal_register') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage legal_register') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage legal_register') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    // ----------------------------------------------------------------
    // Form
    // ----------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Requirement Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('requirement_title')
                        ->label('Requirement Title')
                        ->placeholder('e.g. Environmental Management Act, Cap 191 — EIA Certificate')
                        ->maxLength(255)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('requirement_type')
                        ->label('Type')
                        ->options(LegalRegisterItem::REQUIREMENT_TYPE_LABELS)
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('issuing_authority')
                        ->label('Issuing Authority')
                        ->placeholder('e.g. NEMC, OSHA Tanzania, EWURA')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('applicable_to')
                        ->label('Applicable To')
                        ->placeholder('Which projects, activities or departments this applies to')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Compliance Status')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('compliance_status')
                        ->label('Compliance Status')
                        ->options(LegalRegisterItem::COMPLIANCE_STATUS_LABELS)
                        ->default('not_assessed')
                        ->required()
                        ->native(false),

                    Forms\Components\FileUpload::make('evidence_file')
                        ->label('Evidence / Permit Document')
                        ->directory('ems/legal-register')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->openable(),

                    Forms\Components\DatePicker::make('expiry_date')
                        ->label('Expiry Date (Permits / Licences)')
                        ->native(false),

                    Forms\Components\DatePicker::make('last_review_date')
                        ->label('Last Review Date')
                        ->native(false),

                    Forms\Components\DatePicker::make('next_review_date')
                        ->label('Next Review Date')
                        ->native(false),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
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
                Tables\Columns\TextColumn::make('requirement_title')
                    ->label('Requirement')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('requirement_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string =>
                        LegalRegisterItem::REQUIREMENT_TYPE_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'danger'  => 'law',
                        'warning' => 'regulation',
                        'primary' => 'permit_license',
                        'info'    => 'client_requirement',
                        'gray'    => 'other',
                    ]),

                Tables\Columns\BadgeColumn::make('compliance_status')
                    ->label('Compliance')
                    ->formatStateUsing(fn (string $state): string =>
                        LegalRegisterItem::COMPLIANCE_STATUS_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'success' => 'compliant',
                        'danger'  => 'non_compliant',
                        'warning' => 'partially_compliant',
                        'gray'    => 'not_assessed',
                    ]),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date('d M Y')
                    ->color(function (LegalRegisterItem $record): string {
                        if (! $record->expiry_date) {
                            return 'gray';
                        }
                        if ($record->is_expired) {
                            return 'danger';
                        }
                        if ($record->expiry_date->diffInDays(now()) <= 60) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_review_date')
                    ->label('Next Review')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('issuing_authority')
                    ->label('Authority')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('requirement_type')
                    ->label('Type')
                    ->options(LegalRegisterItem::REQUIREMENT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('compliance_status')
                    ->label('Compliance Status')
                    ->options(LegalRegisterItem::COMPLIANCE_STATUS_LABELS),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiring Within 60 Days')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('expiry_date')
                        ->where('expiry_date', '>=', now()->toDateString())
                        ->where('expiry_date', '<=', now()->addDays(60)->toDateString())
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('expired')
                    ->label('Already Expired')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('expiry_date')
                        ->where('expiry_date', '<', now()->toDateString())
                    )
                    ->toggle(),
            ])
            ->defaultSort('expiry_date')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLegalRegisters::route('/'),
            'create' => Pages\CreateLegalRegister::route('/create'),
            'edit'   => Pages\EditLegalRegister::route('/{record}/edit'),
        ];
    }
}
