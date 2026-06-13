<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class AttendanceCalculationService
{
    /**
     * Standard_Daily_Hours = 8
     */
    public const STANDARD_DAILY_HOURS = 8.0;

    /**
     * Calculate Hours_Worked and Overtime_Hours from time_in / time_out.
     *
     * Hours_Worked = Time_Out - Time_In
     * IF Hours_Worked > Standard_Daily_Hours
     *     Overtime_Hours = Hours_Worked - Standard_Daily_Hours
     * ELSE
     *     Overtime_Hours = 0
     *
     * @param  string|null  $timeIn   "HH:MM" or "HH:MM:SS"
     * @param  string|null  $timeOut  "HH:MM" or "HH:MM:SS"
     * @return array{hours_worked: float, overtime_hours: float}
     */
    public static function calculate(?string $timeIn, ?string $timeOut): array
    {
        if (! $timeIn || ! $timeOut) {
            return ['hours_worked' => 0.0, 'overtime_hours' => 0.0];
        }

        $in = Carbon::parse($timeIn);
        $out = Carbon::parse($timeOut);

        // Handle overnight shifts (time_out earlier than time_in => next day).
        if ($out->lessThanOrEqualTo($in)) {
            $out->addDay();
        }

        $hoursWorked = round($in->diffInMinutes($out) / 60, 2);

        $overtimeHours = $hoursWorked > self::STANDARD_DAILY_HOURS
            ? round($hoursWorked - self::STANDARD_DAILY_HOURS, 2)
            : 0.0;

        return [
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtimeHours,
        ];
    }

    /**
     * Overtime_Pay = Overtime_Hours x Hourly_Rate x Overtime_Multiplier
     *
     * Overtime_Multiplier = 1.5
     */
    public const OVERTIME_MULTIPLIER = 1.5;

    public static function calculateOvertimePay(float $overtimeHours, float $hourlyRate): float
    {
        return round($overtimeHours * $hourlyRate * self::OVERTIME_MULTIPLIER, 2);
    }
}
