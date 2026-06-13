<?php

namespace App\Models;

use App\Services\AttendanceCalculationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';

    protected $fillable = [
        'staff_id',
        'employment_type',
        'payroll_period',
        'basic_salary',
        'allowances',
        'hours_worked',
        'days_worked',
        'overtime_hours',
        'overtime_pay',
        'bonus',
        'gross_salary',
        'paye',
        'nssf',
        'nssf_employer',
        'wcf',
        'nhif',
        'other_deductions',
        'loan_deduction',
        'advance_deduction',
        'withholding_tax',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_reference',
    ];

    protected $casts = [
        'payroll_period' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'days_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'paye' => 'decimal:2',
        'nssf' => 'decimal:2',
        'nssf_employer' => 'decimal:2',
        'wcf' => 'decimal:2',
        'nhif' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------
    | Statutory rates (Tanzania) - placeholders
    |--------------------------------------------------------------------
    | NOTE: These rates/bands change from time to time via the Finance Act
    | and TRA/NSSF/WCF circulars. Keep them centralised here (or move to
    | config/payroll.php) so they can be updated in one place without
    | touching the model logic. Verify current rates before going live.
    */

    /** Employee NSSF contribution rate (e.g. 10%) */
    public const NSSF_EMPLOYEE_RATE = 0.10;

    /** Employer NSSF contribution rate (e.g. 10%) */
    public const NSSF_EMPLOYER_RATE = 0.10;

    /** WCF rate - private sector employer contribution (e.g. 0.5%) */
    public const WCF_RATE = 0.005;

    /** NHIF employee contribution rate (e.g. 3%) */
    public const NHIF_RATE = 0.03;

    /**
     * PAYE bands (monthly, TZS) - placeholder values.
     * Each band: [lower_bound, upper_bound, base_tax, rate_on_excess]
     * upper_bound = null means "and above".
     */
    public const PAYE_BANDS = [
        [0,         270_000,   0,      0.00],
        [270_000,   520_000,   0,      0.08],
        [520_000,   760_000,   20_000, 0.20],
        [760_000,   1_000_000, 68_000, 0.25],
        [1_000_000, null,      128_000, 0.30],
    ];

    /**
     * The staff member this payroll record belongs to.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * The user who recorded/processed the payment (optional, if tracked).
     */
    // public function processedBy(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'processed_by');
    // }

    /**
     * Calculate PAYE for a given gross/taxable income using the
     * configured PAYE_BANDS. Adjust bands/constants above to match
     * the current TRA tax table.
     */
    public static function calculatePaye(float $taxableIncome): float
    {
        foreach (self::PAYE_BANDS as [$lower, $upper, $base, $rate]) {
            if ($upper === null || $taxableIncome <= $upper) {
                $excess = max(0, $taxableIncome - $lower);

                return round($base + ($excess * $rate), 2);
            }
        }

        return 0.0;
    }

    /**
     * Calculate the full set of statutory deductions for a given
     * basic salary + allowances. Returns an array ready to be
     * merged into the model's attributes (e.g. from a Filament form
     * or a PayrollService).
     */
    public static function calculateStatutoryDeductions(float $basicSalary, float $allowances = 0): array
    {
        $gross = $basicSalary + $allowances;

        $nssfEmployee = round($gross * self::NSSF_EMPLOYEE_RATE, 2);
        $nssfEmployer = round($gross * self::NSSF_EMPLOYER_RATE, 2);
        $wcf = round($gross * self::WCF_RATE, 2);
        $nhif = round($gross * self::NHIF_RATE, 2);

        // Taxable income = gross less employee NSSF (standard TZ practice)
        $taxableIncome = $gross - $nssfEmployee;
        $paye = self::calculatePaye($taxableIncome);

        return [
            'gross_salary' => round($gross, 2),
            'nssf' => $nssfEmployee,
            'nssf_employer' => $nssfEmployer,
            'wcf' => $wcf,
            'nhif' => $nhif,
            'paye' => $paye,
        ];
    }

    /**
     * Pull total Hours Worked, Days Worked (present days), and Overtime
     * Hours from the Attendance log for the given staff member and
     * payroll period (month). Used to pre-fill hours_worked/days_worked/
     * overtime_hours - the Accountant can still override these manually
     * before saving.
     *
     * @return array{hours_worked: float, days_worked: float, overtime_hours: float}
     */
    public static function pullAttendanceTotals(int $staffId, \Illuminate\Support\Carbon|string $payrollPeriod): array
    {
        $period = \Illuminate\Support\Carbon::parse($payrollPeriod);

        $totals = Attendance::query()
            ->where('staff_id', $staffId)
            ->whereYear('attendance_date', $period->year)
            ->whereMonth('attendance_date', $period->month)
            ->selectRaw("
                COALESCE(SUM(hours_worked), 0) as total_hours,
                COALESCE(SUM(overtime_hours), 0) as total_overtime,
                COALESCE(SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END), 0) as days_present
            ")
            ->first();

        return [
            'hours_worked' => round((float) $totals->total_hours, 2),
            'days_worked' => round((float) $totals->days_present, 2),
            'overtime_hours' => round((float) $totals->total_overtime, 2),
        ];
    }

    /**
     * Calculate gross pay based on employment type, following the
     * formulas in the Payroll & HR Module specification:
     *
     *   Permanent / Contract / Intern:
     *     Gross = Basic Salary + Allowances + Bonus + Overtime Pay
     *
     *   Part-Time:
     *     Gross = (Hours Worked x Hourly Rate) + Overtime Pay
     *
     *   Casual:
     *     Gross = (Days Worked x Daily Rate) + Overtime Pay
     *
     *   Consultant:
     *     Gross = Contract Amount (Overtime not applicable)
     *
     * @param  array{
     *     basic_salary?: float, allowances?: float, bonus?: float, overtime_pay?: float,
     *     hours_worked?: float, hourly_rate?: float,
     *     days_worked?: float, daily_rate?: float,
     *     contract_amount?: float,
     * } $inputs
     */
    public static function calculateGrossPay(string $employmentType, array $inputs): float
    {
        $overtimePay = (float) ($inputs['overtime_pay'] ?? 0);

        return match ($employmentType) {
            'part_time' => round(
                ((float) ($inputs['hours_worked'] ?? 0) * (float) ($inputs['hourly_rate'] ?? 0)) + $overtimePay,
                2
            ),
            'casual' => round(
                ((float) ($inputs['days_worked'] ?? 0) * (float) ($inputs['daily_rate'] ?? 0)) + $overtimePay,
                2
            ),
            'consultant' => round((float) ($inputs['contract_amount'] ?? 0), 2),
            default => round(
                (float) ($inputs['basic_salary'] ?? 0)
                + (float) ($inputs['allowances'] ?? 0)
                + (float) ($inputs['bonus'] ?? 0)
                + $overtimePay,
                2
            ),
        };
    }

    /**
     * Auto-calculate gross_salary and net_salary whenever a payroll
     * record is being saved. The formula branches on employment_type:
     *
     *   - Consultant: net = gross - withholding_tax (no PAYE/NSSF/NHIF)
     *   - All others: net = gross - (PAYE + NSSF + NHIF + loan + advance + other)
     *
     * overtime_pay is recalculated here too (Overtime_Hours x Hourly_Rate x 1.5),
     * using the rate captured at save time via the 'hourly_rate' attribute
     * if it was set on the model (see PayrollResource/RelationManager forms,
     * which pass it through as a non-persisted form value before saving).
     */
    protected static function booted(): void
    {
        static::saving(function (Payroll $payroll) {
            $staff = $payroll->staff;

            $hourlyRate = (float) ($staff?->hourly_rate ?? 0);
            $dailyRate = (float) ($staff?->daily_rate ?? 0);
            $contractAmount = (float) ($staff?->contract_amount ?? 0);

            // Overtime pay applies to Permanent, Part-Time, and Casual staff
            // who have an hourly_rate recorded (Consultants are excluded).
            if ($payroll->employment_type !== 'consultant' && (float) $payroll->overtime_hours > 0 && $hourlyRate > 0) {
                $payroll->overtime_pay = AttendanceCalculationService::calculateOvertimePay(
                    (float) $payroll->overtime_hours,
                    $hourlyRate,
                );
            }

            $payroll->gross_salary = self::calculateGrossPay($payroll->employment_type, [
                'basic_salary' => $payroll->basic_salary,
                'allowances' => $payroll->allowances,
                'bonus' => $payroll->bonus,
                'overtime_pay' => $payroll->overtime_pay,
                'hours_worked' => $payroll->hours_worked,
                'hourly_rate' => $hourlyRate,
                'days_worked' => $payroll->days_worked,
                'daily_rate' => $dailyRate,
                'contract_amount' => $contractAmount,
            ]);

            if ($payroll->employment_type === 'consultant') {
                // Net_Payment = Contract_Amount - Withholding_Tax
                $payroll->net_salary = round($payroll->gross_salary - (float) $payroll->withholding_tax, 2);

                return;
            }

            $totalDeductions = (float) $payroll->paye
                + (float) $payroll->nssf
                + (float) $payroll->nhif
                + (float) $payroll->loan_deduction
                + (float) $payroll->advance_deduction
                + (float) $payroll->other_deductions;

            $payroll->net_salary = round($payroll->gross_salary - $totalDeductions, 2);
        });
    }
}
