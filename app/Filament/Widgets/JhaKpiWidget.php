<?php

namespace App\Filament\Widgets;

use App\Models\JhaAnalysis;
use App\Models\JhaHazard;
use App\Models\JhaControlMeasure;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JhaKpiWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static ?int $sort = 15;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor']) ?? false;
    }

    protected function getStats(): array
    {
        try {
            return $this->buildStats();
        } catch (\Throwable $e) {
            return [
                Stat::make('JHA KPIs', '—')
                    ->description('Pending: php artisan migrate --force on production')
                    ->color('gray'),
            ];
        }
    }

    private function buildStats(): array
    {
        $total       = JhaAnalysis::count();
        $authorized  = JhaAnalysis::where('status', 'authorized')->count();
        $pending     = JhaAnalysis::whereIn('status', ['submitted', 'supervisor_approved', 'hse_approved', 'pm_approved'])->count();
        $rejected    = JhaAnalysis::where('status', 'rejected')->count();

        // JHA Completion Rate = Authorized ÷ Total × 100
        $completionRate = $total > 0 ? round($authorized / $total * 100, 1) : 0;

        // High-Risk JHAs — those with at least one Critical/High hazard
        $highRiskJhas = JhaAnalysis::whereHas(
            'tasks.hazards',
            fn ($q) => $q->whereIn('initial_risk_level', ['high', 'critical'])
        )->count();
        $highRiskRate = $total > 0 ? round($highRiskJhas / $total * 100, 1) : 0;

        // Total hazards & those with residual risk NOT accepted (residual >= initial)
        $totalHazards   = JhaHazard::count();
        $rejectedHazards = JhaHazard::where('residual_accepted', false)->count();

        // Control implementation compliance
        $totalControls       = JhaControlMeasure::count();
        $implementedControls = JhaControlMeasure::whereIn('status', ['implemented', 'verified'])->count();
        $controlCompliance   = $totalControls > 0 ? round($implementedControls / $totalControls * 100, 1) : 0;

        // Average competency compliance across all JHAs that have data
        $avgCompetency = JhaAnalysis::whereNotNull('competency_compliance_pct')
            ->avg('competency_compliance_pct');
        $avgCompetency = $avgCompetency ? round($avgCompetency, 1) : 0;

        // Average residual risk reduction
        $avgReduction = JhaAnalysis::whereNotNull('residual_risk_reduction_pct')
            ->avg('residual_risk_reduction_pct');
        $avgReduction = $avgReduction ? round($avgReduction, 1) : 0;

        return [
            Stat::make('JHA Completion Rate', "{$completionRate}%")
                ->description("Authorized: {$authorized} / Total: {$total}")
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger')),

            Stat::make('High-Risk Activity Rate', "{$highRiskRate}%")
                ->description("{$highRiskJhas} of {$total} JHAs have High/Critical hazards")
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($highRiskRate <= 20 ? 'success' : ($highRiskRate <= 40 ? 'warning' : 'danger')),

            Stat::make('Control Implementation', "{$controlCompliance}%")
                ->description("{$implementedControls} of {$totalControls} controls implemented/verified")
                ->descriptionIcon('heroicon-o-shield-check')
                ->color($controlCompliance >= 90 ? 'success' : ($controlCompliance >= 70 ? 'warning' : 'danger')),

            Stat::make('Worker Competency Compliance', "{$avgCompetency}%")
                ->description('Average across all JHAs with competency data')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color($avgCompetency >= 90 ? 'success' : ($avgCompetency >= 70 ? 'warning' : 'danger')),

            Stat::make('Residual Risk Reduction', "{$avgReduction}%")
                ->description('Average initial→residual risk reduction')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color($avgReduction >= 50 ? 'success' : ($avgReduction >= 25 ? 'warning' : 'danger')),

            Stat::make('Pending Approval', $pending)
                ->description("{$rejected} rejected | {$total} total JHAs")
                ->descriptionIcon('heroicon-o-clock')
                ->color($pending > 5 ? 'warning' : 'success'),
        ];
    }
}
