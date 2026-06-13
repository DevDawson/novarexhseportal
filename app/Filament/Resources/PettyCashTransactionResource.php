<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PettyCashTransactionResource\Pages;
use App\Models\PettyCashTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PettyCashTransactionResource extends Resource
{
    protected static ?string $model = PettyCashTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?string $modelLabel = 'Petty Cash / Utility Transaction';

    protected static ?int $navigationSort = 2;

    /**
     * Finance module: MD and Accountant manage the petty cash book.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage petty_cash') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage petty_cash') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage petty_cash') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Transaction Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('transaction_type')
                        ->options([
                            'top_up' => 'Top-Up (Cash In)',
                            'expense' => 'Expense (Cash Out)',
                            'utility_payment' => 'Utility Payment (Cash Out)',
                        ])
                        ->default('expense')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            // Top-ups don't need a category.
                            if ($state === 'top_up') {
                                $set('category', null);
                            }
                        }),

                    Forms\Components\Select::make('category')
                        ->options([
                            'office_supplies' => 'Office Supplies',
                            'electricity' => 'Electricity (LUKU)',
                            'water' => 'Water (DAWASA)',
                            'internet' => 'Internet',
                            'rent' => 'Rent',
                            'transport' => 'Transport',
                            'other' => 'Other',
                        ])
                        ->native(false)
                        ->visible(fn (Get $get) => $get('transaction_type') !== 'top_up')
                        ->required(fn (Get $get) => $get('transaction_type') !== 'top_up'),

                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateBalance($get, $set)),

                    Forms\Components\DatePicker::make('transaction_date')
                        ->native(false)
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateBalance($get, $set)),

                    Forms\Components\Select::make('project_id')
                        ->label('Project (optional)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->helperText('Attribute this transaction to a specific project, if applicable.'),

                    Forms\Components\TextInput::make('balance_after')
                        ->label('Running Balance (After This Transaction)')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Auto-calculated from the previous balance + this transaction.'),

                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('recorded_by')
                        ->default(fn () => auth()->id()),
                ]),
        ]);
    }

    /**
     * Estimate balance_after by taking the most recent prior transaction's
     * balance_after and applying this transaction's effect (top_up = +,
     * expense/utility_payment = -). This is a convenience preview only -
     * the Accountant can override if reconciling against a physical book.
     */
    protected static function recalculateBalance(Get $get, Set $set): void
    {
        $date = $get('transaction_date');
        $type = $get('transaction_type');
        $amount = (float) ($get('amount') ?? 0);

        $previousBalance = PettyCashTransaction::query()
            ->when($date, fn ($query) => $query->where('transaction_date', '<=', $date))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->value('balance_after') ?? 0;

        $delta = $type === 'top_up' ? $amount : -$amount;

        $set('balance_after', round((float) $previousBalance + $delta, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('transaction_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'success' => 'top_up',
                        'danger' => ['expense', 'utility_payment'],
                    ]),

                Tables\Columns\TextColumn::make('category')
                    ->formatStateUsing(fn (?string $state): string => $state ? str($state)->replace('_', ' ')->title() : '-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('TZS')
                    ->sortable()
                    ->color(fn (PettyCashTransaction $record) => $record->transaction_type === 'top_up' ? 'success' : 'danger')
                    ->formatStateUsing(fn (PettyCashTransaction $record, $state) => ($record->transaction_type === 'top_up' ? '+ ' : '- ').number_format($state, 2)),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Balance')
                    ->money('TZS')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'top_up' => 'Top-Up',
                        'expense' => 'Expense',
                        'utility_payment' => 'Utility Payment',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'office_supplies' => 'Office Supplies',
                        'electricity' => 'Electricity (LUKU)',
                        'water' => 'Water (DAWASA)',
                        'internet' => 'Internet',
                        'rent' => 'Rent',
                        'transport' => 'Transport',
                        'other' => 'Other',
                    ]),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
                    }),
            ])
            ->defaultSort('transaction_date', 'desc')
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
            'index' => Pages\ListPettyCashTransactions::route('/'),
            'create' => Pages\CreatePettyCashTransaction::route('/create'),
            'edit' => Pages\EditPettyCashTransaction::route('/{record}/edit'),
        ];
    }
}
