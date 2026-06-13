<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class IncidentTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Incident Trends by Type (Last 6 Months)';

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '120s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hr_director']) ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect();

        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->startOfMonth());
        }

        // Group incident types into the same buckets used for HSE KPIs.
        $typeGroups = [
            'Near Miss' => ['near_miss'],
            'First Aid' => ['first_aid'],
            'Medical/LTI/Fatality' => ['medical_treatment', 'lost_time', 'fatality'],
            'Environmental' => ['environmental'],
            'Other' => ['property_damage'],
        ];

        $colors = [
            'Near Miss' => '#6B7280',
            'First Aid' => '#3B82F6',
            'Medical/LTI/Fatality' => '#EF4444',
            'Environmental' => '#F59E0B',
            'Other' => '#9CA3AF',
        ];

        $datasets = [];

        foreach ($typeGroups as $label => $types) {
            $datasets[] = [
                'label' => $label,
                'data' => $months->map(function (Carbon $month) use ($types) {
                    return Incident::query()
                        ->whereYear('incident_date', $month->year)
                        ->whereMonth('incident_date', $month->month)
                        ->whereIn('incident_type', $types)
                        ->count();
                })->values(),
                'backgroundColor' => $colors[$label],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $months->map(fn (Carbon $m) => $m->format('M Y'))->values(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
        ];
    }
}
