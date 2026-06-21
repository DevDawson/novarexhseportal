<?php

namespace App\Filament\Widgets;

use App\Models\HazardRegister;
use Filament\Widgets\ChartWidget;

class RiskTrendLineChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'Risk Reduction Trend — High & Critical Hazards (12 Months)';
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 10;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director', 'lead_auditor']) ?? false;
    }

    protected function getType(): string { return 'line'; }

    protected function getData(): array
    {
        $labels   = [];
        $initial  = [];
        $residual = [];

        for ($i = 11; $i >= 0; $i--) {
            $month    = now()->subMonths($i)->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $labels[] = $month->format('M Y');

            // High/Critical initial = score >= 10
            $initial[] = HazardRegister::where('date_identified', '<=', $monthEnd->toDateString())
                ->whereNotIn('status', ['closed', 'controlled'])
                ->where('initial_risk_score', '>=', 10)
                ->count();

            // High/Critical residual = score >= 10
            $residual[] = HazardRegister::where('date_identified', '<=', $monthEnd->toDateString())
                ->whereNotIn('status', ['closed', 'controlled'])
                ->where('residual_risk_score', '>=', 10)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'High/Critical (Initial Risk)',
                    'data'            => $initial,
                    'borderColor'     => '#EF4444',
                    'backgroundColor' => 'rgba(239,68,68,0.1)',
                    'tension'         => 0.3,
                    'fill'            => true,
                ],
                [
                    'label'           => 'High/Critical (Residual Risk)',
                    'data'            => $residual,
                    'borderColor'     => '#10B981',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                    'tension'         => 0.3,
                    'fill'            => true,
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
