<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\Staff;
use App\Services\AttendanceCalculationService;
use App\Services\PayrollCalculationService;
use App\Services\PayslipService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'HR & Payroll';

    protected static ?string $recordTitleAttribute = 'staff_id';

    /**
     * Only HR Director and Accountant can access the Payroll module.
     * MD can view for oversight but not edit (see canEdit/canCreate).
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['hr_director', 'accountant', 'md']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['hr_director', 'accountant']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['hr_director', 'accountant']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('hr_director') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Employee & Period')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('staff_id')
                        ->label('Staff Member')
                        ->relationship('staff', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Staff $record) => "{$record->first_name} {$record->last_name} ({$record->staff_no})")
                        ->searchable(['first_name', 'last_name', 'staff_no'])
                        ->preload()
                        ->required()
                        ->live()
                        // Pre-fill employment_type, rates, and basic salary from the staff record.
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                            if (! $state) {
                                return;
                            }

                            $staff = Staff::find($state);

                            if (! $staff) {
                                return;
                            }

                            $set('employment_type', $staff->employment_type);
                            $set('basic_salary', $staff->basic_salary ?? 0);
                            $set('staff_hourly_rate', $staff->hourly_rate ?? 0);
                            $set('staff_daily_rate', $staff->daily_rate ?? 0);
                            $set('staff_contract_amount', $staff->contract_amount ?? 0);

                            self::recalculateAll($get, $set);
                        }),

                    Forms\Components\DatePicker::make('payroll_period')
                        ->label('Payroll Period (Month)')
                        ->displayFormat('F Y')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('employment_type')
                        ->options([
                            'permanent' => 'Permanent',
                            'part_time' => 'Part-Time',
                            'casual' => 'Casual',
                            'consultant' => 'Consultant',
                            'contract' => 'Contract',
                            'intern' => 'Intern',
                        ])
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Defaults from the staff record - determines which pay formula applies below.')
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),

                    // Hidden helper fields - not DB columns, used only to carry the
                    // staff member's rates into this form's live calculations.
                    Forms\Components\Hidden::make('staff_hourly_rate')->default(0)->dehydrated(false),
                    Forms\Components\Hidden::make('staff_daily_rate')->default(0)->dehydrated(false),
                    Forms\Components\Hidden::make('staff_contract_amount')->default(0)->dehydrated(false),
                ]),

            // ----------------------------------------------------------
            // PERMANENT / CONTRACT / INTERN
            // ----------------------------------------------------------
            Forms\Components\Section::make('Earnings - Salaried Staff')
                ->columns(3)
                ->visible(fn (Get $get) => in_array($get('employment_type'), ['permanent', 'contract', 'intern']))
                ->schema([
                    Forms\Components\TextInput::make('basic_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),

                    Forms\Components\TextInput::make('allowances')
                        ->label('Allowances (Transport + Housing + Communication + Other)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),

                    Forms\Components\TextInput::make('bonus')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),
                ]),

            // ----------------------------------------------------------
            // PART-TIME
            // ----------------------------------------------------------
            Forms\Components\Section::make('Earnings - Part-Time')
                ->columns(3)
                ->visible(fn (Get $get) => $get('employment_type') === 'part_time')
                ->schema([
                    Forms\Components\TextInput::make('hours_worked')
                        ->label('Hours Worked (this period)')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),

                    Forms\Components\Placeholder::make('hourly_rate_display')
                        ->label('Hourly Rate (from Staff record)')
                        ->content(fn (Get $get) => 'TZS '.number_format((float) ($get('staff_hourly_rate') ?? 0), 2)),

                    Forms\Components\Placeholder::make('part_time_formula')
                        ->label('Formula')
                        ->content('Gross Pay = Hours Worked x Hourly Rate, plus Overtime Pay below.'),
                ]),

            // ----------------------------------------------------------
            // CASUAL
            // ----------------------------------------------------------
            Forms\Components\Section::make('Earnings - Casual')
                ->columns(3)
                ->visible(fn (Get $get) => $get('employment_type') === 'casual')
                ->schema([
                    Forms\Components\TextInput::make('days_worked')
                        ->label('Days Worked (this period)')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),

                    Forms\Components\Placeholder::make('daily_rate_display')
                        ->label('Daily Rate (from Staff record)')
                        ->content(fn (Get $get) => 'TZS '.number_format((float) ($get('staff_daily_rate') ?? 0), 2)),

                    Forms\Components\Placeholder::make('casual_formula')
                        ->label('Formula')
                        ->content('Gross Pay = Days Worked x Daily Rate, plus Overtime Pay below.'),
                ]),

            // ----------------------------------------------------------
            // CONSULTANT
            // ----------------------------------------------------------
            Forms\Components\Section::make('Earnings - Consultant')
                ->columns(2)
                ->visible(fn (Get $get) => $get('employment_type') === 'consultant')
                ->schema([
                    Forms\Components\Placeholder::make('contract_amount_display')
                        ->label('Contract Amount (from Staff record)')
                        ->content(fn (Get $get) => 'TZS '.number_format((float) ($get('staff_contract_amount') ?? 0), 2)),

                    Forms\Components\TextInput::make('withholding_tax')
                        ->label('Withholding Tax')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->required()
                        ->helperText('Enter the withholding tax amount for this payment. Net Payment = Contract Amount - Withholding Tax.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),
                ]),

            // ----------------------------------------------------------
            // OVERTIME (Permanent / Part-Time / Casual / Contract)
            // ----------------------------------------------------------
            Forms\Components\Section::make('Overtime')
                ->description('Overtime Pay = Overtime Hours x Hourly Rate x 1.5. Pull totals from the Attendance log, or enter manually.')
                ->columns(4)
                ->visible(fn (Get $get) => in_array($get('employment_type'), ['permanent', 'part_time', 'casual', 'contract']))
                ->schema([
                    Forms\Components\TextInput::make('overtime_hours')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set)),

                    Forms\Components\TextInput::make('overtime_pay')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('pullAttendance')
                            ->label('Pull from Attendance')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->action(function (Get $get, Set $set) {
                                $staffId = $get('staff_id');
                                $period = $get('payroll_period');

                                if (! $staffId || ! $period) {
                                    return;
                                }

                                $totals = Payroll::pullAttendanceTotals((int) $staffId, $period);

                                $set('hours_worked', $totals['hours_worked']);
                                $set('days_worked', $totals['days_worked']);
                                $set('overtime_hours', $totals['overtime_hours']);

                                self::recalculateAll($get, $set);
                            }),
                    ])->columnSpan(2),
                ]),

            // ----------------------------------------------------------
            // GROSS TOTAL
            // ----------------------------------------------------------
            Forms\Components\Section::make('Gross Total')
                ->schema([
                    Forms\Components\TextInput::make('gross_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated(),
                ]),

            // ----------------------------------------------------------
            // STATUTORY DEDUCTIONS (not for Consultants)
            // ----------------------------------------------------------
            Forms\Components\Section::make('Statutory Deductions (Tanzania)')
                ->description('Auto-calculated from gross pay. Adjust rates in App\Models\Payroll if TRA/NSSF/WCF/NHIF tables change.')
                ->columns(2)
                ->visible(fn (Get $get) => $get('employment_type') !== 'consultant')
                ->schema([
                    Forms\Components\TextInput::make('paye')
                        ->label('PAYE (TRA)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf')
                        ->label('NSSF (Employee)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf_employer')
                        ->label('NSSF (Employer Contribution)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->helperText('For statutory remittance reporting only - not deducted from staff.'),

                    Forms\Components\TextInput::make('wcf')
                        ->label('WCF (Employer)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->helperText('For statutory remittance reporting only - not deducted from staff.'),

                    Forms\Components\TextInput::make('nhif')
                        ->label('NHIF / Health Insurance')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\Placeholder::make('auto_note')
                        ->label('')
                        ->content('PAYE, NSSF, WCF and NHIF recalculate automatically whenever Gross changes. You can override any value manually - Net Salary updates accordingly.')
                        ->columnSpanFull(),
                ]),

            // ----------------------------------------------------------
            // OTHER DEDUCTIONS (not for Consultants)
            // ----------------------------------------------------------
            Forms\Components\Section::make('Other Deductions')
                ->columns(3)
                ->visible(fn (Get $get) => $get('employment_type') !== 'consultant')
                ->schema([
                    Forms\Components\TextInput::make('loan_deduction')
                        ->label('Loan Deduction')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('advance_deduction')
                        ->label('Advance Deduction')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('other_deductions')
                        ->label('Other Deductions')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),
                ]),

            // ----------------------------------------------------------
            // NET PAY & PAYMENT
            // ----------------------------------------------------------
            Forms\Components\Section::make('Net Pay & Payment')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('net_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText(fn (Get $get) => $get('employment_type') === 'consultant'
                            ? 'Net Payment = Contract Amount - Withholding Tax'
                            : 'Net Salary = Gross - (PAYE + NSSF + NHIF + Loan + Advance + Other Deductions)'),

                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'paid' => 'Paid',
                        ])
                        ->required()
                        ->default('pending')
                        ->native(false),

                    Forms\Components\TextInput::make('approval_reference')
                        ->label('Approval Reference')
                        ->maxLength(255)
                        ->helperText('Recorded when Finance Manager / MD approves this payroll.'),

                    Forms\Components\DatePicker::make('payment_date')
                        ->native(false)
                        ->visible(fn (Get $get) => $get('payment_status') === 'paid'),

                    Forms\Components\TextInput::make('payment_reference')
                        ->label('Payment Reference / TXN ID')
                        ->maxLength(255)
                        ->visible(fn (Get $get) => $get('payment_status') === 'paid')
                        ->columnSpan(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.full_name')
                    ->label('Staff')
                    ->searchable(['staff.first_name', 'staff.last_name'])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('employment_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'success' => 'permanent',
                        'info' => ['contract', 'part_time'],
                        'warning' => 'casual',
                        'primary' => 'consultant',
                        'gray' => 'intern',
                    ]),

                Tables\Columns\TextColumn::make('payroll_period')
                    ->date('F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('overtime_pay')
                    ->label('OT Pay')
                    ->money('TZS')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('paye')
                    ->label('PAYE')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nssf')
                    ->label('NSSF')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nhif')
                    ->label('NHIF')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('withholding_tax')
                    ->label('WHT')
                    ->money('TZS')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('net_salary')
                    ->money('TZS')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'paid',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'part_time' => 'Part-Time',
                        'casual' => 'Casual',
                        'consultant' => 'Consultant',
                        'contract' => 'Contract',
                        'intern' => 'Intern',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ]),

                Tables\Filters\Filter::make('payroll_period')
                    ->form([
                        Forms\Components\DatePicker::make('from')->native(false),
                        Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('payroll_period', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('payroll_period', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadPayslip')
                    ->label('Payslip')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->action(fn (Payroll $record) => PayslipService::download($record)),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Full recalculation pipeline, run whenever any earnings input,
     * employment_type, or overtime field changes:
     *
     *   1. Overtime Pay = Overtime Hours x Hourly Rate x 1.5
     *   2. Gross = formula based on employment_type (see Payroll::calculateGrossPay)
     *   3. Statutory deductions (PAYE/NSSF/WCF/NHIF) from Gross - unless Consultant
     *   4. Net Salary
     */
    protected static function recalculateAll(Get $get, Set $set): void
    {
        $employmentType = $get('employment_type') ?? 'permanent';
        $hourlyRate = (float) ($get('staff_hourly_rate') ?? 0);

        // 1. Overtime Pay
        $overtimeHours = (float) ($get('overtime_hours') ?? 0);
        $overtimePay = 0.0;

        if ($employmentType !== 'consultant' && $overtimeHours > 0 && $hourlyRate > 0) {
            $overtimePay = AttendanceCalculationService::calculateOvertimePay($overtimeHours, $hourlyRate);
        }

        $set('overtime_pay', $overtimePay);

        // 2. Gross Pay (formula depends on employment_type)
        $gross = Payroll::calculateGrossPay($employmentType, [
            'basic_salary' => (float) ($get('basic_salary') ?? 0),
            'allowances' => (float) ($get('allowances') ?? 0),
            'bonus' => (float) ($get('bonus') ?? 0),
            'overtime_pay' => $overtimePay,
            'hours_worked' => (float) ($get('hours_worked') ?? 0),
            'hourly_rate' => $hourlyRate,
            'days_worked' => (float) ($get('days_worked') ?? 0),
            'daily_rate' => (float) ($get('staff_daily_rate') ?? 0),
            'contract_amount' => (float) ($get('staff_contract_amount') ?? 0),
        ]);

        $set('gross_salary', $gross);

        // 3. Statutory deductions (skip for Consultants)
        if ($employmentType === 'consultant') {
            $set('paye', 0);
            $set('nssf', 0);
            $set('nssf_employer', 0);
            $set('wcf', 0);
            $set('nhif', 0);

            // 4. Net Payment = Contract Amount - Withholding Tax
            $set('net_salary', round($gross - (float) ($get('withholding_tax') ?? 0), 2));

            return;
        }

        $deductions = PayrollCalculationService::calculate($gross);

        $set('paye', $deductions['paye']);
        $set('nssf', $deductions['nssf']);
        $set('nssf_employer', $deductions['nssf_employer']);
        $set('wcf', $deductions['wcf']);
        $set('nhif', $deductions['nhif']);

        self::recalculateNet($get, $set);
    }

    /**
     * Recalculate net salary from current gross + all deduction fields.
     * Used when a deduction is edited manually (without re-running the
     * full gross/statutory recalculation).
     */
    protected static function recalculateNet(Get $get, Set $set): void
    {
        $employmentType = $get('employment_type') ?? 'permanent';
        $gross = (float) ($get('gross_salary') ?? 0);

        if ($employmentType === 'consultant') {
            $set('net_salary', round($gross - (float) ($get('withholding_tax') ?? 0), 2));

            return;
        }

        $deductions = (float) ($get('paye') ?? 0)
            + (float) ($get('nssf') ?? 0)
            + (float) ($get('nhif') ?? 0)
            + (float) ($get('loan_deduction') ?? 0)
            + (float) ($get('advance_deduction') ?? 0)
            + (float) ($get('other_deductions') ?? 0);

        $set('net_salary', round($gross - $deductions, 2));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
