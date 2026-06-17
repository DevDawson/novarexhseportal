<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificationResource\Pages;
use App\Models\Certification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CertificationResource extends Resource
{
    protected static ?string $model = Certification::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Training & Competency';

    protected static ?string $navigationLabel = 'Certifications';

    protected static ?string $modelLabel = 'Certification';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage training') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage training') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage training') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Certification Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('staff_id')
                        ->label('Staff Member')
                        ->relationship('staff', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('certification_name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('issuing_body')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('certificate_number')
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('issue_date')
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('expiry_date')
                        ->native(false)
                        ->helperText('Leave blank for lifetime certifications'),

                    Forms\Components\Select::make('status')
                        ->options(['valid' => 'Valid', 'expired' => 'Expired', 'suspended' => 'Suspended', 'revoked' => 'Revoked'])
                        ->required()
                        ->native(false),

                    Forms\Components\FileUpload::make('document_path')
                        ->label('Certificate Document')
                        ->directory('certifications')
                        ->openable(),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.full_name')->label('Staff Member')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('certification_name')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('issuing_body')->toggleable(),
                Tables\Columns\TextColumn::make('certificate_number')->toggleable(),
                Tables\Columns\TextColumn::make('issue_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->is_expired ? 'danger' : ($record?->days_until_expiry <= 90 ? 'warning' : null)),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'valid', 'danger' => 'expired', 'warning' => 'suspended', 'gray' => 'revoked']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['valid' => 'Valid', 'expired' => 'Expired', 'suspended' => 'Suspended', 'revoked' => 'Revoked']),
            ])
            ->defaultSort('expiry_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertifications::route('/'),
            'create' => Pages\CreateCertification::route('/create'),
            'edit' => Pages\EditCertification::route('/{record}/edit'),
        ];
    }
}
