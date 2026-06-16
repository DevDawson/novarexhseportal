<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalPermitResource\Pages;
use App\Models\EnvironmentalPermit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnvironmentalPermitResource extends Resource
{
    protected static ?string $model = EnvironmentalPermit::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Environmental Management';

    protected static ?string $navigationLabel = 'Environmental Permits';

    protected static ?string $modelLabel = 'Environmental Permit';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Permit Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('permit_number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Forms\Components\Select::make('permit_type')
                        ->options([
                            'emission' => 'Emission Permit',
                            'discharge' => 'Discharge Permit',
                            'waste_disposal' => 'Waste Disposal Permit',
                            'water_abstraction' => 'Water Abstraction',
                            'land_use' => 'Land Use / Occupation',
                            'noise' => 'Noise Permit',
                            'eia_certificate' => 'EIA Certificate',
                            'operating_license' => 'Operating License',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->native(false),

                    Forms\Components\TextInput::make('issuing_authority')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('issue_date')
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('expiry_date')
                        ->native(false)
                        ->helperText('Leave blank for indefinite permits'),

                    Forms\Components\Select::make('status')
                        ->options(['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended', 'revoked' => 'Revoked', 'under_renewal' => 'Under Renewal'])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('responsible_officer_id')
                        ->label('Responsible Officer')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('renewal_reminder_days')
                        ->label('Renewal Reminder (days before expiry)')
                        ->numeric()
                        ->default(90),

                    Forms\Components\FileUpload::make('document_path')
                        ->label('Permit Document')
                        ->directory('environmental-permits')
                        ->openable(),

                    Forms\Components\Textarea::make('permit_conditions')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permit_number')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('permit_type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['primary' => 'eia_certificate', 'success' => 'operating_license', 'warning' => ['emission', 'discharge'], 'gray' => 'other']),
                Tables\Columns\TextColumn::make('issuing_authority')->limit(25),
                Tables\Columns\TextColumn::make('issue_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->is_expired ? 'danger' : ($record?->days_until_expiry <= 90 ? 'warning' : null)),
                Tables\Columns\TextColumn::make('project.name')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'danger' => ['expired', 'revoked'], 'warning' => ['suspended', 'under_renewal']]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('permit_type')
                    ->options(['emission' => 'Emission', 'discharge' => 'Discharge', 'waste_disposal' => 'Waste Disposal', 'water_abstraction' => 'Water Abstraction', 'land_use' => 'Land Use', 'noise' => 'Noise', 'eia_certificate' => 'EIA Certificate', 'operating_license' => 'Operating License', 'other' => 'Other']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended', 'revoked' => 'Revoked', 'under_renewal' => 'Under Renewal']),
            ])
            ->defaultSort('expiry_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnvironmentalPermits::route('/'),
            'create' => Pages\CreateEnvironmentalPermit::route('/create'),
            'edit' => Pages\EditEnvironmentalPermit::route('/{record}/edit'),
        ];
    }
}
