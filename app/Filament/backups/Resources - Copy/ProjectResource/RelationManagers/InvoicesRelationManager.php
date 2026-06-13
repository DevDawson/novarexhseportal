<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage invoices') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\DatePicker::make('invoice_date')
                    ->native(false)
                    ->default(now())
                    ->required(),

                Forms\Components\DatePicker::make('due_date')
                    ->native(false),
            ]),

            Forms\Components\Repeater::make('items')
                ->relationship('items')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set)),

                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('TZS')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set)),
                    ]),
                ])
                ->reorderable(false)
                ->addActionLabel('Add Line Item')
                ->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateTotals($get, $set))
                ->columnSpanFull(),

            Forms\Components\Grid::make(4)->schema([
                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->prefix('TZS')
                    ->readOnly()
                    ->dehydrated(),

                Forms\Components\TextInput::make('vat')
                    ->label('VAT (18%)')
                    ->numeric()
                    ->prefix('TZS')
                    ->readOnly()
                    ->dehydrated(),

                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('TZS')
                    ->readOnly()
                    ->dehydrated(),

                Forms\Components\TextInput::make('amount_paid')
                    ->numeric()
                    ->prefix('TZS')
                    ->default(0),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('draft')
                    ->required()
                    ->native(false),

                Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]),

            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    /**
     * Recalculate subtotal, VAT (18%), and total_amount from the
     * current set of line items.
     */
    protected static function recalculateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];

        $subtotal = collect($items)->sum(
            fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)
        );

        $vat = round($subtotal * 0.18, 2);

        $set('subtotal', round($subtotal, 2));
        $set('vat', $vat);
        $set('total_amount', round($subtotal + $vat, 2));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date('d M Y')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'warning' => ['partially_paid', 'overdue'],
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
