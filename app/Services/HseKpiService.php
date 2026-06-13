<?php

namespace App\Services;

class HseKpiService
{
    /**
     * Standard hours base used in frequency-rate formulas:
     *   Rate = (Incident Count x BASE) / Total Hours Worked
     *
     * 200,000 = OSHA convention (100 employees x 40 hrs/week x 50 weeks).
     * Some jurisdictions use 1,000,000 (ILO convention) - adjust if NOVAREX
     * needs to report against ILO-based client KPIs instead.
     */
    public const RATE_BASE_HOURS = 200_000;

    /**
     * Incident types counted as Lost Time Injuries (LTI) for LTIFR.
     */
    public const LTI_TYPES = ['lost_time', 'fatality'];

    /**
     * Incident types counted as "Recordable" for TRIR (Total Recordable
     * Incident Rate) - typically injuries requiring more than first aid.
     */
    public const RECORDABLE_TYPES = ['medical_treatment', 'lost_time', 'fatality'];

    /**
     * Incident types counted as Near Misses for KPI reporting.
     */
    public const NEAR_MISS_TYPES = ['near_miss'];

    /**
     * Incident types counted as Environmental Incidents for KPI reporting.
     */
    public const ENVIRONMENTAL_TYPES = ['environmental'];

    /**
     * LTIFR = (Number of LTIs x RATE_BASE_HOURS) / Total Hours Worked
     */
    public static function ltifr(int $ltiCount, float $totalHoursWorked): float
    {
        if ($totalHoursWorked <= 0) {
            return 0.0;
        }

        return round(($ltiCount * self::RATE_BASE_HOURS) / $totalHoursWorked, 2);
    }

    /**
     * TRIR = (Number of Recordable Incidents x RATE_BASE_HOURS) / Total Hours Worked
     */
    public static function trir(int $recordableCount, float $totalHoursWorked): float
    {
        if ($totalHoursWorked <= 0) {
            return 0.0;
        }

        return round(($recordableCount * self::RATE_BASE_HOURS) / $totalHoursWorked, 2);
    }

    /**
     * Number of days an open/investigating incident has been outstanding.
     * An incident is "overdue" if it has been open longer than
     * OVERDUE_THRESHOLD_DAYS without being closed.
     */
    public const OVERDUE_THRESHOLD_DAYS = 30;
}
