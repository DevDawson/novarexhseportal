<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Incident;
use App\Services\HseKpiService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HseKpiOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '120s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hr_director']) ?? false;
    }

    protected function getStats(): array
    {
        $yearStart = now()->startOfYear();
        $yearEnd = now();

        $incidents = Incident::query()
            ->whereBetween('incident_date', [$yearStart, $yearEnd]);

        $totalIncidents = $incidents->count();

        $nearMisses = Incident::query()
            ->whereBetween('incident_date', [$yearStart, $yearEnd])
            ->whereIn('incident_type', HseKpiService::NEAR_MISS_TYPES)
            ->count();

        $ltiCount = Incident::query()
            ->whereBetween('incident_date', [$yearStart, $yearEnd])
            ->whereIn('incident_type', HseKpiService::LTI_TYPES)
            ->count();

        $recordableCount = Incident::query()
            ->whereBetween('incident_date', [$yearStart, $yearEnd])
            ->whereIn('incident_type', HseKpiService::RECORDABLE_TYPES)
            ->count();

        $environmentalCount = Incident::query()
            ->whereBetween('incident_date', [$yearStart, $yearEnd])
            ->whereIn('incident_type', HseKpiService::ENVIRONMENTAL_TYPES)
            ->count();

        // Total hours worked (YTD) - from the Attendance log, all staff.
        $totalHoursWorked = (float) Attendance::query()
            ->whereBetween('attendance_date', [$yearStart, $yearEnd])
            ->sum('hours_worked');

        $ltifr = HseKpiService::ltifr($ltiCount, $totalHoursWorked);
        $trir = HseKpiService::trir($recordableCount, $totalHoursWorked);

        return [
            Stat::make('Total Incidents (YTD)', $totalIncidents)
                ->description(now()->format('Y').' year to date')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Near Misses', $nearMisses)
                ->description('Early-warning indicator')
                ->descriptionIcon('heroicon-m-eye')
                ->color('gray'),

            Stat::make('Lost Time Injuries', $ltiCount)
                ->description('Includes fatalities')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($ltiCount > 0 ? 'danger' : 'success'),

            Stat::make('Environmental Incidents', $environmentalCount)
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color($environmentalCount > 0 ? 'warning' : 'success'),

            Stat::make('LTIFR', number_format($ltifr, 2))
                ->description('per 200,000 hours worked')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($ltifr > 0 ? 'warning' : 'success'),

            Stat::make('TRIR', number_format($trir, 2))
                ->description('per 200,000 hours worked')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($trir > 0 ? 'warning' : 'success'),
        ];
    }
}
