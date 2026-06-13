<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';

    protected $fillable = [
        'staff_id',
        'payroll_period',
        'basic_salary',
        'allowances',
        'gross_salary',
        'paye',
        'nssf',
        'nssf_employer',
        'wcf',
        'nhif',
        'other_deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_reference',
    ];

    protected $casts = [
        'payroll_period' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'paye' => 'decimal:2',
        'nssf' => 'decimal:2',
        'nssf_employer' => 'decimal:2',
        'wcf' => 'decimal:2',
        'nhif' => 'decimal:2',
        'other_deductions' => 'decimal:2',
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
     * Auto-calculate gross_salary and net_salary whenever a payroll
     * record is being saved.
     *
     * This is intentionally lightweight: it derives gross_salary from
     * basic_salary + allowances, and net_salary from gross_salary minus
     * all deduction columns. The actual PAYE/NSSF/WCF/NHIF figures can
     * either be:
     *   - left as entered manually on the Filament form, or
     *   - pre-filled via Payroll::calculateStatutoryDeductions() in the
     *     Filament form's afterStateUpdated()/mutateFormDataBeforeCreate()
     *     hooks, then saved as-is here.
     */
    protected static function booted(): void
    {
        static::saving(function (Payroll $payroll) {
            $payroll->gross_salary = round(
                (float) $payroll->basic_salary + (float) $payroll->allowances,
                2
            );

            $totalDeductions = (float) $payroll->paye
                + (float) $payroll->nssf
                + (float) $payroll->nhif
                + (float) $payroll->other_deductions;

            $payroll->net_salary = round($payroll->gross_salary - $totalDeductions, 2);
        });
    }
}
