<?php

namespace App\Filament\Widgets;

use App\Services\EsgMaturityService;
use Filament\Widgets\ChartWidget;

class EsgRadarChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'ESG Radar — Environmental · Social · Governance';
    protected static ?string $maxHeight = '320px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 18;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']) ?? false;
    }

    protected function getType(): string { return 'radar'; }

    protected function getData(): array
    {
        try {
            $data   = EsgMaturityService::latestOrLive();
            $scores = $data['scores'];

            $eScores = [
                (float) ($scores['cr']  ?? 0),
                (float) ($scores['wr']  ?? 0),
                (float) ($scores['er']  ?? 0),
                (float) ($scores['wtr'] ?? 0),
                (float) ($scores['ems'] ?? 0),
            ];
            $sScores = [
                (float) ($scores['tr']    ?? 0),
                (float) ($scores['ltifr'] ?? 0),
                (float) ($scores['ewr']   ?? 0),
                (float) ($scores['csr']   ?? 0),
                (float) ($scores['dei']   ?? 0),
            ];
            $gScores = [
                (float) ($scores['ccr'] ?? 0),
                (float) ($scores['acr'] ?? 0),
                (float) ($scores['dcr'] ?? 0),
                (float) ($scores['ecr'] ?? 0),
                (float) ($scores['mrr'] ?? 0),
            ];

            // Interleave E/S/G into a single 15-point radar for visual richness
            $composite = $data['composite'];

            return [
                'datasets' => [
                    [
                        'label'           => 'Environmental (E)',
                        'data'            => $eScores,
                        'borderColor'     => '#10B981',
                        'backgroundColor' => 'rgba(16,185,129,0.15)',
                        'pointBackgroundColor' => '#10B981',
                    ],
                    [
                        'label'           => 'Social (S)',
                        'data'            => $sScores,
                        'borderColor'     => '#3B82F6',
                        'backgroundColor' => 'rgba(59,130,246,0.15)',
                        'pointBackgroundColor' => '#3B82F6',
                    ],
                    [
                        'label'           => 'Governance (G)',
                        'data'            => $gScores,
                        'borderColor'     => '#8B5CF6',
                        'backgroundColor' => 'rgba(139,92,246,0.15)',
                        'pointBackgroundColor' => '#8B5CF6',
                    ],
                ],
                'labels' => ['KPI-1', 'KPI-2', 'KPI-3', 'KPI-4', 'KPI-5'],
            ];
        } catch (\Throwable) {
            return ['datasets' => [], 'labels' => []];
        }
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'beginAtZero' => true,
                    'max'         => 100,
                    'ticks'       => ['stepSize' => 20],
                ],
            ],
        ];
    }
}
