<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Business Development';

    protected static ?string $modelLabel = 'Client';

    /**
     * Tanzania's 26 administrative regions (mainland + Zanzibar).
     */
    public const TZ_REGIONS = [
        'Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera',
        'Katavi', 'Kigoma', 'Kilimanjaro', 'Lindi', 'Manyara', 'Mara',
        'Mbeya', 'Morogoro', 'Mtwara', 'Mwanza', 'Njombe', 'Pwani',
        'Rukwa', 'Ruvuma', 'Shinyanga', 'Simiyu', 'Singida', 'Tabora',
        'Tanga', 'Songwe',
        // Zanzibar
        'Kaskazini Unguja', 'Kusini Unguja', 'Mjini Magharibi',
        'Kaskazini Pemba', 'Kusini Pemba',
    ];

    /**
     * Clients are referenced across Projects, Tenders, and Invoices -
     * visible to MD, Business Development, Accounts, and HSE (to attach
     * new projects to the right client).
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'business_director', 'accountant', 'hse_staff']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'business_director']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'business_director']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Company Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('company_name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('client_type')
                        ->options([
                            'government' => 'Government',
                            'private' => 'Private',
                            'ngo' => 'NGO',
                            'individual' => 'Individual',
                        ])
                        ->default('private')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('tin_number')
                        ->label('TIN Number')
                        ->maxLength(255),

                    Forms\Components\Select::make('region')
                        ->options(array_combine(self::TZ_REGIONS, self::TZ_REGIONS))
                        ->searchable()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Contact Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('contact_person')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('address')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('client_type')
                    ->colors([
                        'info' => 'government',
                        'success' => 'private',
                        'warning' => 'ngo',
                        'gray' => 'individual',
                    ]),

                Tables\Columns\TextColumn::make('contact_person')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('region')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('Projects')
                    ->counts('projects')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_type')
                    ->options([
                        'government' => 'Government',
                        'private' => 'Private',
                        'ngo' => 'NGO',
                        'individual' => 'Individual',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);
    }
}
