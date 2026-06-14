<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false;
    }

    public static function canDelete($record): bool
    {
        // System accounts (used by automatic payroll posting) can never
        // be deleted, even by MD - deleting them would break
        // JournalPostingService.
        return ! $record->is_system && (auth()->user()?->hasRole('md') ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->maxLength(10)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?Account $record) => $record?->is_system ?? false)
                        ->helperText('Convention: 1xxx Assets, 2xxx Liabilities, 3xxx Equity, 4xxx Income, 5xxx Expenses.'),

                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn (?Account $record) => $record?->is_system ?? false),

                    Forms\Components\Select::make('type')
                        ->options([
                            'asset' => 'Asset',
                            'liability' => 'Liability',
                            'equity' => 'Equity',
                            'income' => 'Income',
                            'expense' => 'Expense',
                        ])
                        ->required()
                        ->native(false)
                        ->disabled(fn (?Account $record) => $record?->is_system ?? false)
                        ->live()
                        ->afterStateUpdated(function (string $state, Forms\Set $set) {
                            // Default normal_balance based on type (can be overridden).
                            $set('normal_balance', in_array($state, ['asset', 'expense']) ? 'debit' : 'credit');
                        }),

                    Forms\Components\Select::make('normal_balance')
                        ->label('Normal Balance')
                        ->options([
                            'debit' => 'Debit (increases with DR)',
                            'credit' => 'Credit (increases with CR)',
                        ])
                        ->required()
                        ->native(false)
                        ->disabled(fn (?Account $record) => $record?->is_system ?? false)
                        ->helperText('Assets & Expenses normally increase on Debit. Liabilities, Equity & Income normally increase on Credit.'),

                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive accounts are hidden from journal entry account pickers.'),

                    Forms\Components\Toggle::make('is_system')
                        ->label('System Account')
                        ->disabled()
                        ->helperText('System accounts are used by automatic payroll journal posting (JournalPostingService) and cannot be edited or deleted.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->colors([
                        'success' => 'asset',
                        'danger' => 'liability',
                        'primary' => 'equity',
                        'info' => 'income',
                        'warning' => 'expense',
                    ]),

                Tables\Columns\TextColumn::make('normal_balance')
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance (TZS)')
                    ->state(fn (Account $record) => $record->balance())
                    ->money('TZS')
                    ->alignEnd(),

                Tables\Columns\IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-pencil')
                    ->trueColor('gray')
                    ->falseColor('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->defaultSort('code')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
