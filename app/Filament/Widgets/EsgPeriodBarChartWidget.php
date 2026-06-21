<?php

namespace App\Filament\Widgets;

use App\Models\EsgMaturityAssessment;
use Filament\Widgets\ChartWidget;

class EsgPeriodBarChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'ESG Performance by Period (E / S / G / ESG-MI)';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 20;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']) ?? false;
    }

    protected function getType(): string { return 'bar'; }

    protected function getData(): array
    {
        try {
            $assessments = EsgMaturityAssessment::where('status', 'finalized')
                ->orderBy('period')
                ->limit(8)
                ->get(['period', 'e_score', 's_score', 'g_score', 'esg_mi']);

            if ($assessments->isEmpty()) {
                return [
                    'datasets' => [['label' => 'No finalized assessments yet', 'data' => []]],
                    'labels'   => [],
                ];
            }

            $periods = $assessments->pluck('period')->all();

            return [
                'datasets' => [
                    [
                        'label'           => 'Environmental (E)',
                        'data'            => $assessments->pluck('e_score')->map(fn ($v) => round((float) $v, 1))->all(),
                        'backgroundColor' => '#10B981',
                        'borderRadius'    => 4,
                    ],
                    [
                        'label'           => 'Social (S)',
                        'data'            => $assessments->pluck('s_score')->map(fn ($v) => round((float) $v, 1))->all(),
                        'backgroundColor' => '#3B82F6',
                        'borderRadius'    => 4,
                    ],
                    [
                        'label'           => 'Governance (G)',
                        'data'            => $assessments->pluck('g_score')->map(fn ($v) => round((float) $v, 1))->all(),
                        'backgroundColor' => '#8B5CF6',
                        'borderRadius'    => 4,
                    ],
                    [
                        'label'           => 'ESG-MI',
                        'data'            => $assessments->pluck('esg_mi')->map(fn ($v) => round((float) $v, 1))->all(),
                        'backgroundColor' => '#F59E0B',
                        'borderRadius'    => 4,
                        'type'            => 'line',
                        'borderColor'     => '#F59E0B',
                        'fill'            => false,
                        'tension'         => 0.3,
                    ],
                ],
                'labels' => $periods,
            ];
        } catch (\Throwable) {
            return ['datasets' => [['label' => 'No data', 'data' => []]], 'labels' => []];
        }
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'max' => 100, 'title' => ['display' => true, 'text' => '%']],
            ],
        ];
    }
}
