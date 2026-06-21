<?php

namespace App\Filament\Widgets;

use App\Models\HazardRegister;
use App\Services\RiskScoringService;
use Filament\Widgets\ChartWidget;

class RiskDistributionBarChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'HIRA Risk Distribution — Initial vs Residual Risk Level';
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 9;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director', 'lead_auditor']) ?? false;
    }

    protected function getType(): string { return 'bar'; }

    protected function getData(): array
    {
        $levels  = ['low', 'medium', 'high', 'critical'];
        $labels  = ['Low (0–4)', 'Medium (5–9)', 'High (10–15)', 'Critical (16–25)'];
        $initial  = array_fill_keys($levels, 0);
        $residual = array_fill_keys($levels, 0);

        $rows = HazardRegister::selectRaw(
            'initial_risk_score, residual_risk_score'
        )->whereNotNull('initial_risk_score')->get();

        foreach ($rows as $row) {
            $iLevel = RiskScoringService::level((int) $row->initial_risk_score);
            $rLevel = RiskScoringService::level((int) $row->residual_risk_score);
            $initial[$iLevel]++;
            $residual[$rLevel]++;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Initial Risk',
                    'data'            => array_values($initial),
                    'backgroundColor' => ['#22C55E', '#F59E0B', '#F97316', '#EF4444'],
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Residual Risk (after controls)',
                    'data'            => array_values($residual),
                    'backgroundColor' => ['rgba(34,197,94,0.4)', 'rgba(245,158,11,0.4)', 'rgba(249,115,22,0.4)', 'rgba(239,68,68,0.4)'],
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]],
        ];
    }
}
