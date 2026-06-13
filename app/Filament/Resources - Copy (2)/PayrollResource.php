<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\Staff;
use App\Services\PayrollCalculationService;
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
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('staff_id')
                        ->label('Staff Member')
                        ->relationship('staff', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Staff $record) => "{$record->first_name} {$record->last_name} ({$record->staff_no})")
                        ->searchable(['first_name', 'last_name', 'staff_no'])
                        ->preload()
                        ->required()
                        // Pre-fill basic salary from the staff record when selected.
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if (! $state) {
                                return;
                            }

                            $staff = Staff::find($state);

                            if ($staff) {
                                $set('basic_salary', $staff->basic_salary);
                            }
                        })
                        ->live(),

                    Forms\Components\DatePicker::make('payroll_period')
                        ->label('Payroll Period (Month)')
                        ->displayFormat('F Y')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Earnings')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('basic_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),

                    Forms\Components\TextInput::make('allowances')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),

                    Forms\Components\TextInput::make('gross_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->helperText('Defaults to basic + allowances, but can be overridden manually.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::runStatutoryCalculation($get, $set)),

                    Forms\Components\Select::make('wcf_rate')
                        ->label('WCF Rate (Employer)')
                        ->options([
                            '0.005' => '0.5% (Private Sector)',
                            '0.01' => '1% (Public Sector)',
                        ])
                        ->default('0.005')
                        ->native(false)
                        ->dehydrated(false) // not a DB column - used only for calculation
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::runStatutoryCalculation($get, $set)),
                ]),

            Forms\Components\Section::make('Statutory Deductions (Tanzania)')
                ->description('Auto-calculated from gross salary. Adjust rates in App\Models\Payroll if TRA/NSSF/WCF/NHIF tables change.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('paye')
                        ->label('PAYE (TRA)')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf')
                        ->label('NSSF (Employee)')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf_employer')
                        ->label('NSSF (Employer Contribution)')
                        ->numeric()
                        ->prefix('TZS')
                        ->helperText('For statutory remittance reporting only - not deducted from staff.')
                        ->required(),

                    Forms\Components\TextInput::make('wcf')
                        ->label('WCF (Employer)')
                        ->numeric()
                        ->prefix('TZS')
                        ->helperText('For statutory remittance reporting only - not deducted from staff.')
                        ->required(),

                    Forms\Components\TextInput::make('nhif')
                        ->label('NHIF / Health Insurance')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('other_deductions')
                        ->label('Other Deductions')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\Placeholder::make('auto_note')
                        ->label('')
                        ->content('PAYE, NSSF, WCF and NHIF are calculated automatically when Gross Salary or the WCF Rate change. You can still override any value manually - Net Salary will update accordingly.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Net Pay & Payment')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('net_salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Gross salary - PAYE - NSSF - NHIF - other deductions'),

                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                        ])
                        ->required()
                        ->default('pending')
                        ->native(false),

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

                Tables\Columns\TextColumn::make('payroll_period')
                    ->date('F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paye')
                    ->label('PAYE')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nssf')
                    ->label('NSSF')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('wcf')
                    ->label('WCF')
                    ->money('TZS')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nhif')
                    ->label('NHIF')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('net_salary')
                    ->money('TZS')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ]),
            ])
            ->filters([
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Triggered when basic_salary or allowances change: recompute
     * gross_salary, then re-run the full statutory calculation.
     */
    protected static function recalculate(Get $get, Set $set): void
    {
        $gross = (float) ($get('basic_salary') ?? 0) + (float) ($get('allowances') ?? 0);
        $set('gross_salary', round($gross, 2));

        self::runStatutoryCalculation($get, $set);
    }

    /**
     * Triggered when gross_salary or wcf_rate change: recompute the
     * full statutory breakdown (NSSF, PAYE, WCF, NHIF, net_salary)
     * using PayrollCalculationService.
     */
    protected static function runStatutoryCalculation(Get $get, Set $set): void
    {
        $gross = (float) ($get('gross_salary') ?? 0);
        $wcfRate = (float) ($get('wcf_rate') ?? PayrollCalculationService::WCF_RATE_DEFAULT);

        $result = PayrollCalculationService::calculate($gross, $wcfRate);

        $set('nssf', $result['nssf']);
        $set('nssf_employer', $result['nssf_employer']);
        $set('wcf', $result['wcf']);
        $set('nhif', $result['nhif']);
        $set('paye', $result['paye']);

        self::recalculateNet($get, $set);
    }

    /**
     * Recalculate net salary = gross - (paye + nssf + nhif + other_deductions).
     * Runs after any manual edit to a deduction field too, so overrides
     * are still reflected in net_salary.
     */
    protected static function recalculateNet(Get $get, Set $set): void
    {
        $gross = (float) ($get('gross_salary') ?? 0);
        $deductions = (float) ($get('paye') ?? 0)
            + (float) ($get('nssf') ?? 0)
            + (float) ($get('nhif') ?? 0)
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
