<?php

namespace App\Services;

use App\Models\EnvironmentalAspect;
use App\Models\EnvironmentalMonitoringRecord;
use App\Models\LegalRegisterItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmsKpiService
{
    /**
     * Sum of each metric_type for a given date range and optional project.
     *
     * Returns an associative array: ['water_consumption' => 123.45, ...]
     * Missing metric types are returned as 0.
     */
    public static function totalsByMetric(
        Carbon $from,
        Carbon $to,
        ?int $projectId = null
    ): array {
        $query = EnvironmentalMonitoringRecord::query()
            ->whereBetween('record_date', [$from->toDateString(), $to->toDateString()]);

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }

        $rows = $query
            ->select('metric_type', DB::raw('SUM(value) as total'))
            ->groupBy('metric_type')
            ->pluck('total', 'metric_type')
            ->toArray();

        // Fill in zeros for any missing types
        $defaults = array_fill_keys(
            array_keys(EnvironmentalMonitoringRecord::METRIC_TYPE_LABELS),
            0.0
        );

        return array_merge($defaults, array_map('floatval', $rows));
    }

    /**
     * Monthly totals for a given metric over the last N months.
     *
     * Returns ['labels' => [...], 'data' => [...]] suitable for a chart widget.
     */
    public static function trend(string $metricType, int $months = 12): array
    {
        $labels = [];
        $data   = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $labels[] = $month->format('M Y');

            $total = EnvironmentalMonitoringRecord::query()
                ->where('metric_type', $metricType)
                ->whereYear('record_date', $month->year)
                ->whereMonth('record_date', $month->month)
                ->sum('value');

            $data[] = (float) $total;
        }

        return compact('labels', 'data');
    }

    /**
     * Waste recycling rate (%) for a given period.
     *
     * Rate = recycled / (hazardous + non-hazardous + recycled) * 100
     */
    public static function wasteRecyclingRate(Carbon $from, Carbon $to): float
    {
        $totals = self::totalsByMetric($from, $to);

        $recycled    = $totals['waste_recycled'] ?? 0.0;
        $hazardous   = $totals['waste_generated_hazardous'] ?? 0.0;
        $nonHazardous = $totals['waste_generated_nonhazardous'] ?? 0.0;

        $denominator = $hazardous + $nonHazardous + $recycled;

        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($recycled / $denominator) * 100, 1);
    }

    /**
     * Count of environmental aspects with status = 'significant'.
     */
    public static function significantAspectsCount(): int
    {
        return EnvironmentalAspect::where('status', 'significant')->count();
    }

    /**
     * Count of legal register entries with expiry_date within $withinDays
     * days from today (future dates only).
     */
    public static function expiringLicensesCount(int $withinDays = 60): int
    {
        return LegalRegisterItem::query()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('expiry_date', '<=', now()->addDays($withinDays)->toDateString())
            ->count();
    }
}
