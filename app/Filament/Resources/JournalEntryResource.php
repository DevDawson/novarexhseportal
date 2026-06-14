<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\Account;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?string $navigationLabel = 'Journal Entries';

    protected static ?int $navigationSort = 21;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false;
    }

    public static function canCreate(): bool
    {
        // Only manual entries can be created here - automatic postings
        // come from JournalPostingService (Payroll approval/payment).
        return auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false;
    }

    /**
     * Automatic postings (payroll_approval / payroll_payment) cannot be
     * edited directly - they must stay in sync with the Payroll record
     * that generated them. Only manual entries are editable.
     */
    public static function canEdit($record): bool
    {
        return $record->source_type === 'manual'
            && (auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false);
    }

    public static function canDelete($record): bool
    {
        return $record->source_type === 'manual'
            && (auth()->user()?->hasRole('md') ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Entry Details')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => JournalEntry::nextReference(now()))
                        ->disabled(fn (?JournalEntry $record) => $record !== null)
                        ->dehydrated(),

                    Forms\Components\DatePicker::make('entry_date')
                        ->required()
                        ->native(false)
                        ->default(now()),

                    Forms\Components\Hidden::make('source_type')->default('manual'),
                ]),

            Forms\Components\TextInput::make('description')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Section::make('Journal Lines')
                ->description('Total Debit must equal Total Credit before this entry can be saved.')
                ->schema([
                    Forms\Components\Repeater::make('lines')
                        ->relationship('lines')
                        ->label('')
                        ->schema([
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\Select::make('account_id')
                                    ->label('Account')
                                    ->options(
                                        Account::where('is_active', true)
                                            ->orderBy('code')
                                            ->get()
                                            ->mapWithKeys(fn (Account $a) => [$a->id => $a->display_name])
                                    )
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('debit')
                                    ->numeric()
                                    ->prefix('TZS')
                                    ->default(0)
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('credit')
                                    ->numeric()
                                    ->prefix('TZS')
                                    ->default(0)
                                    ->live(onBlur: true),
                            ]),

                            Forms\Components\TextInput::make('description')
                                ->label('Line Description (optional)')
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ])
                        ->minItems(2)
                        ->addActionLabel('Add Line')
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('balance_check')
                        ->label('')
                        ->content(function (Get $get) {
                            $lines = $get('lines') ?? [];

                            $totalDebit = collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
                            $totalCredit = collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));
                            $diff = round($totalDebit - $totalCredit, 2);

                            $debitFmt = number_format($totalDebit, 2);
                            $creditFmt = number_format($totalCredit, 2);

                            if ($diff === 0.0) {
                                return new \Illuminate\Support\HtmlString(
                                    "<span class=\"text-success-600 font-medium\">✓ Balanced - Total Debit: TZS {$debitFmt} = Total Credit: TZS {$creditFmt}</span>"
                                );
                            }

                            return new \Illuminate\Support\HtmlString(
                                "<span class=\"text-danger-600 font-medium\">✗ Not balanced - Debit: TZS {$debitFmt}, Credit: TZS {$creditFmt}, Difference: TZS ".number_format(abs($diff), 2)."</span>"
                            );
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('entry_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('source_type')
                    ->formatStateUsing(fn (string $state) => JournalEntry::SOURCE_LABELS[$state] ?? $state)
                    ->colors([
                        'gray' => 'manual',
                        'info' => 'payroll_approval',
                        'success' => 'payroll_payment',
                        'warning' => 'statutory_remittance',
                    ]),

                Tables\Columns\TextColumn::make('total_debit')
                    ->label('Total (TZS)')
                    ->state(fn (JournalEntry $record) => $record->total_debit)
                    ->money('TZS')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('postedBy.name')
                    ->label('Posted By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Source')
                    ->options(JournalEntry::SOURCE_LABELS),

                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('entry_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('entry_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                        $lines = $data['lines'] ?? [];
                        unset($data['lines']);

                        $totalDebit = collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
                        $totalCredit = collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));

                        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                            Notification::make()
                                ->title('Entry not balanced')
                                ->body('Total Debit (TZS '.number_format($totalDebit, 2).') must equal Total Credit (TZS '.number_format($totalCredit, 2).').')
                                ->danger()
                                ->send();

                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'lines' => 'Total Debit must equal Total Credit.',
                            ]);
                        }

                        $data['posted_by'] = auth()->id();

                        $entry = $model::create($data);

                        foreach ($lines as $line) {
                            $entry->lines()->create($line);
                        }

                        return $entry;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'view' => Pages\ViewJournalEntry::route('/{record}'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
