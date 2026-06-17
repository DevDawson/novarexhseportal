<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?int $navigationSort = 1;

    /**
     * Finance module: MD and Accountant only.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage invoices') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage invoices') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage invoices') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Invoice Details')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->relationship('client', 'company_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        // When a client is picked, narrow the project list to
                        // that client's projects (handled via the project_id
                        // select's options closure below).
                        ->afterStateUpdated(fn (Set $set) => $set('project_id', null)),

                    Forms\Components\Select::make('project_id')
                        ->label('Project (optional)')
                        ->relationship(
                            name: 'project',
                            titleAttribute: 'title',
                            modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query, Get $get) => $get('client_id')
                                ? $query->where('client_id', $get('client_id'))
                                : $query,
                        )
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('invoice_number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->default(fn () => 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Invoice::max('id') + 1), 4, '0', STR_PAD_LEFT)),
                ]),

            Forms\Components\Section::make('Dates')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('invoice_date')
                        ->native(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\DatePicker::make('due_date')
                        ->native(false)
                        ->afterOrEqual('invoice_date')
                        ->default(now()->addDays(30)),
                ]),

            Forms\Components\Section::make('Line Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->label('')
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
                ]),

            Forms\Components\Section::make('Totals & Status')
                ->columns(4)
                ->schema([
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
                        ->native(false)
                        ->columnSpan(2),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->toggleable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money('TZS')
                    ->color(fn (Invoice $record) => $record->balance > 0 ? 'danger' : 'success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'warning' => ['partially_paid', 'overdue'],
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'company_name')
                    ->searchable(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue (unpaid past due date)')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                        ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
                        ->whereDate('due_date', '<', now())
                    ),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Invoice $record) => $record->status !== 'paid' && $record->status !== 'cancelled')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->update([
                        'status' => 'paid',
                        'amount_paid' => $record->total_amount,
                    ])),

                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Invoice $record) => route('pdf.invoice', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('sendEmail')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('to_email')
                            ->label('Recipient Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('body')
                            ->label('Message')
                            ->required()
                            ->rows(5),
                    ])
                    ->fillForm(fn (Invoice $record) => [
                        'to_email' => $record->client->email ?? '',
                        'subject'  => 'Invoice ' . $record->invoice_number . ' — ' . Setting::companyName(),
                        'body'     => "Dear " . ($record->client->contact_person ?: $record->client->company_name) . ",\n\nPlease find attached invoice " . $record->invoice_number . " for TZS " . number_format((float)$record->total_amount, 2) . ".\n\nPayment is due by " . ($record->due_date ? $record->due_date->format('d M Y') : 'the agreed date') . ".\n\nPlease quote the invoice number with your payment.\n\nKind regards,\n" . Setting::companyName(),
                    ])
                    ->action(function (Invoice $record, array $data): void {
                        Mail::to($data['to_email'])->send(new InvoiceMail($record, $data['body']));
                        Notification::make()
                            ->title('Invoice emailed to ' . $data['to_email'])
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Send Invoice by Email')
                    ->modalSubmitActionLabel('Send Email'),

                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function (Invoice $record): string {
                        $phone = preg_replace('/[^\d]/', '', $record->client->phone ?? '');
                        $message = "Hello " . ($record->client->contact_person ?: $record->client->company_name) . ",\n\nPlease find your invoice details below:\n\nInvoice No: " . $record->invoice_number . "\nDate: " . $record->invoice_date->format('d M Y') . "\nTotal: TZS " . number_format((float)$record->total_amount, 2) . "\nBalance Due: TZS " . number_format($record->balance, 2) . "\nDue Date: " . ($record->due_date ? $record->due_date->format('d M Y') : 'As agreed') . "\n\nKindly quote the invoice number with your payment.\n\nThank you,\n" . Setting::companyName();
                        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Invoice $record) => !empty($record->client->phone)),

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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
