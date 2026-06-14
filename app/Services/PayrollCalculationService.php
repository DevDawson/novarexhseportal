<?php

namespace App\Services;

class PayrollCalculationService
{
    /** Employee NSSF contribution rate (10%) */
    public const NSSF_RATE = 0.10;

    /** NHIF contribution rate (3%) */
    public const NHIF_RATE = 0.03;

    /** WCF rate - private sector employer contribution (e.g. 0.5%) */
    public const WCF_RATE_DEFAULT = 0.005;

    /**
     * Skills Development Levy (SDL) - employer contribution paid to VETA.
     * 4.5% of gross payroll. Not deducted from employee's net salary.
     */
    public const SDL_RATE = 0.045;

    /**
     * TRA PAYE monthly bands (TZS), effective as per the latest Finance Act.
     * Each row: [lower_bound, upper_bound (null = and above), base_tax, rate_on_excess]
     *
     * IMPORTANT: Verify against the current TRA tax table before relying on
     * this in production - rates/bands are adjusted via the annual Finance Act.
     */
    public const PAYE_BANDS = [
        [0,         270_000,   0,       0.00],
        [270_000,   520_000,   0,       0.08],
        [520_000,   760_000,   20_000,  0.20],
        [760_000,   1_000_000, 68_000,  0.25],
        [1_000_000, null,      128_000, 0.30],
    ];

    /**
     * Calculate the full statutory deduction breakdown for a given
     * gross salary.
     *
     * @param  float  $grossSalary  Total gross salary (basic + allowances)
     * @param  float  $wcfRate      Employer WCF rate (default 0.5%). Pass
     *                               0.01 for the public-sector 1% rate.
     * @return array{
     *     gross_salary: float,
     *     nssf: float,
     *     nssf_employer: float,
     *     wcf: float,
     *     nhif: float,
     *     paye: float,
     *     net_salary: float,
     * }
     */
    public static function calculate(float $grossSalary, float $wcfRate = self::WCF_RATE_DEFAULT): array
    {
        $nssfEmployee = round($grossSalary * self::NSSF_RATE, 2);
        $nssfEmployer = round($grossSalary * self::NSSF_RATE, 2);
        $nhif = round($grossSalary * self::NHIF_RATE, 2);
        $wcf = round($grossSalary * $wcfRate, 2);
        $sdl = round($grossSalary * self::SDL_RATE, 2);

        // Taxable income = gross less the employee's NSSF contribution
        // (standard Tanzanian practice - NSSF is deducted before PAYE).
        $taxableIncome = max(0, $grossSalary - $nssfEmployee);
        $paye = self::calculatePaye($taxableIncome);

        $netSalary = round($grossSalary - $nssfEmployee - $paye - $nhif, 2);

        return [
            'gross_salary' => round($grossSalary, 2),
            'nssf' => $nssfEmployee,
            'nssf_employer' => $nssfEmployer,
            'wcf' => $wcf,
            'sdl' => $sdl,
            'nhif' => $nhif,
            'paye' => $paye,
            'net_salary' => $netSalary,
        ];
    }

    /**
     * Calculate PAYE for a given taxable income using PAYE_BANDS.
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
}
