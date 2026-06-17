<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultantInvoiceResource\Pages;
use App\Models\ConsultantInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConsultantInvoiceResource extends Resource
{
    protected static ?string $model = ConsultantInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Consultant Payments';

    protected static ?string $modelLabel = 'Consultant Payment Request';

    protected static ?string $pluralModelLabel = 'Consultant Payment Requests';

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

            // ── SECTION 1: Consultant & Project ──────────────────────────
            Forms\Components\Section::make('Consultant & Project')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('consultant_type')
                        ->label('Consultant Type')
                        ->options([
                            'external' => 'External / Sub-consultant',
                            'staff'    => 'Internal Staff / Consultant',
                        ])
                        ->default('external')
                        ->live()
                        ->required()
                        ->afterStateUpdated(function (Set $set) {
                            $set('staff_id', null);
                            $set('consultant_name', null);
                        }),

                    Forms\Components\Select::make('staff_id')
                        ->label('Staff Member')
                        ->relationship('staff', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('consultant_type') === 'staff')
                        ->required(fn (Get $get) => $get('consultant_type') === 'staff'),

                    Forms\Components\TextInput::make('consultant_name')
                        ->label('Consultant / Company Name')
                        ->visible(fn (Get $get) => $get('consultant_type') !== 'staff')
                        ->required(fn (Get $get) => $get('consultant_type') !== 'staff')
                        ->maxLength(255),
                ]),

            // ── SECTION 2: Proforma Invoice Details ───────────────────────
            Forms\Components\Section::make('Stage 1 — Proforma Invoice')
                ->description('Enter the details as they appear on the consultant\'s Proforma Invoice.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('proforma_number')
                        ->label('Proforma Invoice Number')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('proforma_date')
                        ->label('Proforma Date')
                        ->native(false)
                        ->required(),

                    Forms\Components\Textarea::make('service_description')
                        ->label('Description of Services')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('proforma_net_amount')
                        ->label('Net Amount (TZS)')
                        ->numeric()
                        ->prefix('TZS')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $net = (float) ($get('proforma_net_amount') ?? 0);
                            $vat = round($net * 0.18, 2);
                            $set('proforma_vat_amount', $vat);
                            $set('proforma_total_amount', round($net + $vat, 2));
                        }),

                    Forms\Components\TextInput::make('proforma_vat_amount')
                        ->label('VAT Amount (18%)')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('proforma_total_amount')
                        ->label('Total Amount (TZS)')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\FileUpload::make('proforma_attachment')
                        ->label('Proforma Invoice Document')
                        ->disk('private')
                        ->directory('consultant-invoices/proforma')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120)
                        ->columnSpanFull()
                        ->helperText('Attach the scanned Proforma Invoice (PDF or image, max 5 MB).'),
                ]),

            // ── SECTION 3: Consultant Registration Details ────────────────
            Forms\Components\Section::make('Consultant Registration & Contact')
                ->description('Fill from the Proforma Invoice header — TIN and VRN are mandatory for EFD compliance.')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('consultant_tin')
                        ->label('TIN Number')
                        ->maxLength(50)
                        ->placeholder('e.g. 100-XXX-XXX'),

                    Forms\Components\TextInput::make('consultant_vrn')
                        ->label('VRN (VAT Reg. No.)')
                        ->maxLength(50)
                        ->placeholder('e.g. 40-XXXXXXXX-X'),

                    Forms\Components\TextInput::make('consultant_business_reg')
                        ->label('Business Reg. / BRELA No.')
                        ->maxLength(100),

                    Forms\Components\Textarea::make('consultant_address')
                        ->label('Physical Address')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('consultant_phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(30),

                    Forms\Components\TextInput::make('consultant_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                ]),

            // ── SECTION 4: Consultant Bank Details ────────────────────────
            Forms\Components\Section::make('Consultant Bank Details')
                ->description('Bank details as provided on the Proforma Invoice.')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('consultant_bank_name')
                        ->label('Bank Name')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('consultant_bank_branch')
                        ->label('Branch')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('consultant_bank_account_name')
                        ->label('Account Name')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('consultant_bank_account_number')
                        ->label('Account Number')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('consultant_bank_swift')
                        ->label('SWIFT / BIC')
                        ->maxLength(20),
                ]),

            // ── SECTION 5: Proforma Verification (Accountant) ─────────────
            Forms\Components\Section::make('Stage 2 — Proforma Verification')
                ->description('Accountant confirms the Proforma Invoice is valid and matches expectations.')
                ->columns(2)
                ->visible(fn ($record) => $record && in_array($record->status, [
                    'proforma_received', 'proforma_verified', 'awaiting_efd', 'efd_received', 'paid', 'rejected',
                ]))
                ->schema([
                    Forms\Components\DateTimePicker::make('proforma_verified_at')
                        ->label('Verified At')
                        ->native(false)
                        ->readOnly(),

                    Forms\Components\Select::make('proforma_verified_by')
                        ->label('Verified By')
                        ->relationship('proformaVerifiedBy', 'name')
                        ->searchable()
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Textarea::make('proforma_verification_notes')
                        ->label('Verification Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            // ── SECTION 6: EFD / VFD Receipt ─────────────────────────────
            Forms\Components\Section::make('Stage 3 — EFD / VFD Receipt')
                ->description('Enter EFD or VFD receipt details once received from the consultant.')
                ->columns(3)
                ->visible(fn ($record) => $record && in_array($record->status, [
                    'awaiting_efd', 'efd_received', 'paid',
                ]))
                ->schema([
                    Forms\Components\TextInput::make('efd_receipt_number')
                        ->label('EFD / VFD Receipt Number')
                        ->maxLength(100)
                        ->placeholder('e.g. DC-XXXXXXXXXX'),

                    Forms\Components\DatePicker::make('efd_receipt_date')
                        ->label('Receipt Date')
                        ->native(false),

                    Forms\Components\TextInput::make('efd_amount')
                        ->label('Receipt Amount (TZS)')
                        ->numeric()
                        ->prefix('TZS'),

                    Forms\Components\FileUpload::make('efd_attachment')
                        ->label('EFD / VFD Receipt Scan')
                        ->disk('private')
                        ->directory('consultant-invoices/efd')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120)
                        ->columnSpanFull()
                        ->helperText('Attach the scanned EFD or VFD receipt (PDF or image).'),
                ]),

            // ── SECTION 7: Payment ────────────────────────────────────────
            Forms\Components\Section::make('Stage 4 — Payment')
                ->description('Record the actual payment made to the consultant.')
                ->columns(3)
                ->visible(fn ($record) => $record && in_array($record->status, ['efd_received', 'paid']))
                ->schema([
                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->native(false),

                    Forms\Components\TextInput::make('payment_reference')
                        ->label('Payment Reference / Voucher No.')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('payment_amount')
                        ->label('Amount Paid (TZS)')
                        ->numeric()
                        ->prefix('TZS'),

                    Forms\Components\Textarea::make('payment_notes')
                        ->label('Payment Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            // ── General notes ─────────────────────────────────────────────
            Forms\Components\Textarea::make('notes')
                ->label('Internal Notes')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Hidden::make('created_by')
                ->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proforma_number')
                    ->label('Proforma #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('Consultant')
                    ->getStateUsing(fn ($record) => $record->display_name)
                    ->searchable(query: fn ($query, $search) => $query
                        ->where('consultant_name', 'like', "%{$search}%")
                        ->orWhereHas('staff', fn ($q) => $q
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                        )
                    ),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('proforma_date')
                    ->label('Proforma Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('proforma_total_amount')
                    ->label('Proforma Total')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('efd_receipt_number')
                    ->label('EFD/VFD #')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('Paid')
                    ->money('TZS')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ConsultantInvoice::$statuses[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'pending'           => 'gray',
                        'proforma_received',
                        'awaiting_efd'      => 'warning',
                        'proforma_verified',
                        'efd_received'      => 'info',
                        'paid'              => 'success',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ConsultantInvoice::$statuses),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // ── Advance workflow actions ──────────────────────────────

                Tables\Actions\Action::make('markProformaReceived')
                    ->label('Mark Received')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Proforma Received')
                    ->modalDescription('Confirm that the consultant\'s Proforma Invoice has been received and is ready for review.')
                    ->action(fn ($record) => $record->update(['status' => 'proforma_received'])),

                Tables\Actions\Action::make('verifyProforma')
                    ->label('Verify Proforma')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'proforma_received')
                    ->form([
                        Forms\Components\Textarea::make('proforma_verification_notes')
                            ->label('Verification Notes (optional)')
                            ->rows(3),
                    ])
                    ->modalHeading('Verify Proforma Invoice')
                    ->modalDescription('Confirm the Proforma Invoice details are correct. The consultant will then be requested to send an EFD/VFD receipt.')
                    ->modalSubmitActionLabel('Verify & Request EFD/VFD')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'                      => 'awaiting_efd',
                            'proforma_verified_at'        => now(),
                            'proforma_verified_by'        => auth()->id(),
                            'proforma_verification_notes' => $data['proforma_verification_notes'] ?? null,
                        ]);
                        Notification::make()
                            ->title('Proforma verified — awaiting EFD/VFD from consultant.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('enterEfd')
                    ->label('Enter EFD/VFD')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'awaiting_efd')
                    ->form([
                        Forms\Components\TextInput::make('efd_receipt_number')
                            ->label('EFD / VFD Receipt Number')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. DC-XXXXXXXXXX'),

                        Forms\Components\DatePicker::make('efd_receipt_date')
                            ->label('Receipt Date')
                            ->native(false)
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('efd_amount')
                            ->label('Receipt Amount (TZS)')
                            ->numeric()
                            ->prefix('TZS')
                            ->required(),

                        Forms\Components\FileUpload::make('efd_attachment')
                            ->label('EFD / VFD Receipt Scan (optional)')
                            ->disk('private')
                            ->directory('consultant-invoices/efd')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120),
                    ])
                    ->modalHeading('Record EFD / VFD Receipt')
                    ->modalSubmitActionLabel('Save EFD/VFD')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'             => 'efd_received',
                            'efd_receipt_number' => $data['efd_receipt_number'],
                            'efd_receipt_date'   => $data['efd_receipt_date'],
                            'efd_amount'         => $data['efd_amount'],
                            'efd_attachment'     => $data['efd_attachment'] ?? null,
                        ]);
                        Notification::make()
                            ->title('EFD/VFD recorded — ready for payment.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'efd_received')
                    ->form([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->native(false)
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference / Voucher No.')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Amount Paid (TZS)')
                            ->numeric()
                            ->prefix('TZS')
                            ->required(),

                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Payment Notes (optional)')
                            ->rows(2),
                    ])
                    ->modalHeading('Record Payment')
                    ->modalSubmitActionLabel('Confirm Payment')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'            => 'paid',
                            'payment_date'      => $data['payment_date'],
                            'payment_reference' => $data['payment_reference'],
                            'payment_amount'    => $data['payment_amount'],
                            'payment_notes'     => $data['payment_notes'] ?? null,
                        ]);
                        Notification::make()
                            ->title('Payment recorded successfully.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'proforma_received']))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->modalHeading('Reject Payment Request')
                    ->modalSubmitActionLabel('Reject')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->title('Payment request rejected.')
                            ->warning()
                            ->send();
                    }),

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
            'index'  => Pages\ListConsultantInvoices::route('/'),
            'create' => Pages\CreateConsultantInvoice::route('/create'),
            'edit'   => Pages\EditConsultantInvoice::route('/{record}/edit'),
        ];
    }
}
