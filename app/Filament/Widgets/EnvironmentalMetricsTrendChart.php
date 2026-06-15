<?php

namespace App\Filament\Widgets;

use App\Services\EmsKpiService;
use Filament\Widgets\ChartWidget;

class EnvironmentalMetricsTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Environmental Metrics Trend (Last 12 Months)';

    protected static ?string $maxHeight = '320px';

    protected static ?string $pollingInterval = '120s';

    protected static ?int $sort = 13;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $wasteHaz    = EmsKpiService::trend('waste_generated_hazardous', 12);
        $wasteNonHaz = EmsKpiService::trend('waste_generated_nonhazardous', 12);
        $recycled    = EmsKpiService::trend('waste_recycled', 12);
        $energy      = EmsKpiService::trend('energy_consumption', 12);
        $fuel        = EmsKpiService::trend('fuel_consumption', 12);

        return [
            'labels'   => $wasteHaz['labels'],
            'datasets' => [
                [
                    'label'           => 'Waste Hazardous (kg)',
                    'data'            => $wasteHaz['data'],
                    'borderColor'     => '#EF4444',
                    'backgroundColor' => 'rgba(239,68,68,0.08)',
                    'tension'         => 0.3,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Waste Non-Hazardous (kg)',
                    'data'            => $wasteNonHaz['data'],
                    'borderColor'     => '#F59E0B',
                    'backgroundColor' => 'rgba(245,158,11,0.08)',
                    'tension'         => 0.3,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Waste Recycled (kg)',
                    'data'            => $recycled['data'],
                    'borderColor'     => '#10B981',
                    'backgroundColor' => 'rgba(16,185,129,0.08)',
                    'tension'         => 0.3,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Energy (kWh)',
                    'data'            => $energy['data'],
                    'borderColor'     => '#3B82F6',
                    'backgroundColor' => 'rgba(59,130,246,0.08)',
                    'tension'         => 0.3,
                    'borderDash'      => [5, 5],
                    'yAxisID'         => 'y2',
                ],
                [
                    'label'           => 'Fuel (litres)',
                    'data'            => $fuel['data'],
                    'borderColor'     => '#8B5CF6',
                    'backgroundColor' => 'rgba(139,92,246,0.08)',
                    'tension'         => 0.3,
                    'borderDash'      => [5, 5],
                    'yAxisID'         => 'y2',
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y'  => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'title'    => ['display' => true, 'text' => 'Waste (kg)'],
                    'beginAtZero' => true,
                ],
                'y2' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'title'    => ['display' => true, 'text' => 'Energy / Fuel'],
                    'beginAtZero' => true,
                    'grid'     => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }
}
