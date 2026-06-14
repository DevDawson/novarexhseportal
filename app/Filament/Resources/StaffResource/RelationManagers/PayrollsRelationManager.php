<?php

namespace App\Filament\Resources\StaffResource\RelationManagers;

use App\Models\Payroll;
use App\Services\AttendanceCalculationService;
use App\Services\PayrollCalculationService;
use App\Services\PayslipService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    protected static ?string $title = 'Payroll History';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage payroll') ?? false;
    }

    public function form(Form $form): Form
    {
        $staff = $this->getOwnerRecord();

        return $form->schema([

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('payroll_period')
                    ->label('Payroll Period (Month)')
                    ->displayFormat('F Y')
                    ->required()
                    ->native(false),

                Forms\Components\Hidden::make('employment_type')
                    ->default($staff->employment_type),

                Forms\Components\Placeholder::make('employment_type_display')
                    ->label('Employment Type')
                    ->content(str($staff->employment_type)->replace('_', ' ')->title()),
            ]),

            // ------------------------------------------------------------
            // PERMANENT / CONTRACT / INTERN
            // ------------------------------------------------------------
            Forms\Components\Section::make('Earnings - Salaried')
                ->columns(3)
                ->visible(in_array($staff->employment_type, ['permanent', 'contract', 'intern']))
                ->schema([
                    Forms\Components\TextInput::make('basic_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->default($staff->basic_salary)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),

                    Forms\Components\TextInput::make('allowances')
                        ->label('Allowances (Transport + Housing + Comms + Other)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),

                    Forms\Components\TextInput::make('bonus')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),
                ]),

            // ------------------------------------------------------------
            // PART-TIME
            // ------------------------------------------------------------
            Forms\Components\Section::make('Earnings - Part-Time')
                ->columns(2)
                ->visible($staff->employment_type === 'part_time')
                ->schema([
                    Forms\Components\TextInput::make('hours_worked')
                        ->label('Hours Worked')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),

                    Forms\Components\Placeholder::make('hourly_rate_display')
                        ->label('Hourly Rate')
                        ->content('TZS '.number_format((float) ($staff->hourly_rate ?? 0), 2)),
                ]),

            // ------------------------------------------------------------
            // CASUAL
            // ------------------------------------------------------------
            Forms\Components\Section::make('Earnings - Casual')
                ->columns(2)
                ->visible($staff->employment_type === 'casual')
                ->schema([
                    Forms\Components\TextInput::make('days_worked')
                        ->label('Days Worked')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),

                    Forms\Components\Placeholder::make('daily_rate_display')
                        ->label('Daily Rate')
                        ->content('TZS '.number_format((float) ($staff->daily_rate ?? 0), 2)),
                ]),

            // ------------------------------------------------------------
            // CONSULTANT
            // ------------------------------------------------------------
            Forms\Components\Section::make('Earnings - Consultant')
                ->columns(2)
                ->visible($staff->employment_type === 'consultant')
                ->schema([
                    Forms\Components\Placeholder::make('contract_amount_display')
                        ->label('Contract Amount')
                        ->content('TZS '.number_format((float) ($staff->contract_amount ?? 0), 2)),

                    Forms\Components\TextInput::make('withholding_tax')
                        ->label('Withholding Tax')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),
                ]),

            // ------------------------------------------------------------
            // OVERTIME
            // ------------------------------------------------------------
            Forms\Components\Section::make('Overtime')
                ->columns(3)
                ->visible(in_array($staff->employment_type, ['permanent', 'part_time', 'casual', 'contract']))
                ->schema([
                    Forms\Components\TextInput::make('overtime_hours')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateAll($get, $set, $staff)),

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
                            ->action(function (Get $get, Set $set) use ($staff) {
                                $period = $get('payroll_period');

                                if (! $period) {
                                    return;
                                }

                                $totals = Payroll::pullAttendanceTotals($staff->id, $period);

                                $set('hours_worked', $totals['hours_worked']);
                                $set('days_worked', $totals['days_worked']);
                                $set('overtime_hours', $totals['overtime_hours']);

                                self::recalculateAll($get, $set, $staff);
                            }),
                    ]),
                ]),

            Forms\Components\TextInput::make('gross_salary')
                ->numeric()
                ->prefix('TZS')
                ->readOnly()
                ->dehydrated(),

            // ------------------------------------------------------------
            // STATUTORY + OTHER DEDUCTIONS
            // ------------------------------------------------------------
            Forms\Components\Section::make('Deductions')
                ->columns(3)
                ->visible($staff->employment_type !== 'consultant')
                ->schema([
                    Forms\Components\TextInput::make('paye')
                        ->label('PAYE')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf')
                        ->label('NSSF')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nhif')
                        ->label('NHIF')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf_employer')
                        ->label('NSSF (Employer)')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0),

                    Forms\Components\TextInput::make('wcf')
                        ->label('WCF')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0),

                    Forms\Components\TextInput::make('loan_deduction')
                        ->label('Loan')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('advance_deduction')
                        ->label('Advance')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('other_deductions')
                        ->label('Other')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),
                ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('net_salary')
                    ->numeric()
                    ->prefix('TZS')
                    ->readOnly()
                    ->dehydrated(),

                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false)
                    ->live(),

                Forms\Components\DatePicker::make('payment_date')
                    ->native(false)
                    ->visible(fn (Get $get) => $get('payment_status') === 'paid'),

                Forms\Components\TextInput::make('approval_reference')
                    ->label('Approval Reference')
                    ->maxLength(255),
            ]),
        ]);
    }

    /**
     * Mirrors PayrollResource::recalculateAll() - see that class for the
     * full formula documentation. $staff supplies hourly_rate/daily_rate/
     * contract_amount directly since this relation manager is scoped to
     * one staff member.
     */
    protected static function recalculateAll(Get $get, Set $set, \App\Models\Staff $staff): void
    {
        $employmentType = $staff->employment_type;
        $hourlyRate = (float) ($staff->hourly_rate ?? 0);

        $overtimeHours = (float) ($get('overtime_hours') ?? 0);
        $overtimePay = 0.0;

        if ($employmentType !== 'consultant' && $overtimeHours > 0 && $hourlyRate > 0) {
            $overtimePay = AttendanceCalculationService::calculateOvertimePay($overtimeHours, $hourlyRate);
        }

        $set('overtime_pay', $overtimePay);

        $gross = Payroll::calculateGrossPay($employmentType, [
            'basic_salary' => (float) ($get('basic_salary') ?? 0),
            'allowances' => (float) ($get('allowances') ?? 0),
            'bonus' => (float) ($get('bonus') ?? 0),
            'overtime_pay' => $overtimePay,
            'hours_worked' => (float) ($get('hours_worked') ?? 0),
            'hourly_rate' => $hourlyRate,
            'days_worked' => (float) ($get('days_worked') ?? 0),
            'daily_rate' => (float) ($staff->daily_rate ?? 0),
            'contract_amount' => (float) ($staff->contract_amount ?? 0),
        ]);

        $set('gross_salary', $gross);

        if ($employmentType === 'consultant') {
            $set('paye', 0);
            $set('nssf', 0);
            $set('nssf_employer', 0);
            $set('wcf', 0);
            $set('nhif', 0);
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

    protected static function recalculateNet(Get $get, Set $set): void
    {
        $gross = (float) ($get('gross_salary') ?? 0);

        if ($get('employment_type') === 'consultant') {
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payroll_period')
                    ->date('F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('TZS'),

                Tables\Columns\TextColumn::make('overtime_pay')
                    ->label('OT')
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

                Tables\Columns\TextColumn::make('net_salary')
                    ->money('TZS')
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ]),
            ])
            ->defaultSort('payroll_period', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadPayslip')
                    ->label('Payslip')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->action(fn (Payroll $record) => PayslipService::download($record)),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('md') ?? false),
            ]);
    }
}
