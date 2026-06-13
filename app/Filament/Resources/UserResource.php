<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Dashboard & Core Admin';

    protected static ?string $modelLabel = 'User Account';

    /**
     * Only MD and IT Technician manage system user accounts & roles.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage users') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage users') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage users') ?? false;
    }

    public static function canDelete($record): bool
    {
        // Only MD can delete accounts; IT can manage/reset but not delete.
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Account Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $context): bool => $context === 'create')
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->dehydrateStateUsing(fn (string $state) => Hash::make($state))
                        ->helperText(fn (string $context) => $context === 'edit'
                            ? 'Leave blank to keep the current password.'
                            : null),

                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Organisation')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('job_title')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Roles & Permissions')
                ->description('Controls which ERP modules this user can access.')
                ->schema([
                    Forms\Components\Select::make('roles')
                        ->label('Assigned Role(s)')
                        ->relationship('roles', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                            'md' => 'Managing Director (MD)',
                            'hr_director' => 'HR Director',
                            'business_director' => 'Business Director',
                            'accountant' => 'Accountant',
                            'it_technician' => 'IT Technician',
                            'hse_staff' => 'HSE Staff',
                            'secretary' => 'Secretary',
                            default => str($record->name)->replace('_', ' ')->title(),
                        })
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('job_title')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Role(s)')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'danger' => 'md',
                        'warning' => 'hr_director',
                        'info' => 'business_director',
                        'success' => 'accountant',
                        'gray' => ['it_technician', 'secretary'],
                        'primary' => 'hse_staff',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Role'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
