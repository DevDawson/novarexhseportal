<?php

namespace App\Filament\Widgets;

use App\Models\EnvironmentalAudit;
use App\Models\EnvironmentalAuditFinding;
use App\Services\EnvironmentalAuditService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnvironmentalAuditKpiWidget extends BaseWidget
{
    protected static ?int $sort = 19;

    public function getHeading(): string
    {
        return 'Environmental Audit Dashboard (ISO 14001)';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    protected function getStats(): array
    {
        $thisYear = now()->year;

        $totalAudits    = EnvironmentalAudit::whereYear('audit_date', $thisYear)->count();
        $completed      = EnvironmentalAudit::whereYear('audit_date', $thisYear)
                            ->whereIn('status', ['completed', 'closed'])->count();
        $inProgress     = EnvironmentalAudit::where('status', 'in_progress')->count();

        $avgScore = EnvironmentalAudit::whereNotNull('compliance_score')
                        ->whereIn('status', ['completed', 'closed'])
                        ->avg('compliance_score') ?? 0;

        $poorAudits = EnvironmentalAudit::where('rating', 'poor')
                        ->whereIn('status', ['completed', 'closed'])->count();

        // Findings stats
        $openFindings    = EnvironmentalAuditFinding::where('action_status', '!=', 'closed')->count();
        $majorNCs        = EnvironmentalAuditFinding::where('finding_type', 'major_nc')
                            ->where('action_status', '!=', 'closed')->count();
        $overdueFindings = EnvironmentalAuditFinding::where('action_status', '!=', 'closed')
                            ->whereNotNull('target_completion_date')
                            ->where('target_completion_date', '<', now())->count();
        $regulatoryOpen  = EnvironmentalAuditFinding::where('regulatory_impact', true)
                            ->where('action_status', '!=', 'closed')->count();
        $closedThisMonth = EnvironmentalAuditFinding::where('action_status', 'closed')
                            ->whereMonth('closed_at', now()->month)
                            ->whereYear('closed_at', now()->year)->count();

        $ratingLabel = $avgScore > 0
            ? EnvironmentalAuditService::ratingLabel(EnvironmentalAuditService::scoreToRating($avgScore))
            : 'No data';

        return [
            Stat::make('Audits This Year', $totalAudits)
                ->description("{$completed} completed / {$inProgress} in progress")
                ->icon('heroicon-o-magnifying-glass-circle')
                ->color('primary'),

            Stat::make('Avg. Compliance Score', round($avgScore, 1) . '%')
                ->description($ratingLabel)
                ->icon('heroicon-o-chart-bar')
                ->color($avgScore >= 90 ? 'success' : ($avgScore >= 75 ? 'info' : ($avgScore >= 50 ? 'warning' : 'danger'))),

            Stat::make('Poor-Rated Audits', $poorAudits)
                ->description('Score < 50% — urgent action required')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($poorAudits > 0 ? 'danger' : 'success'),

            Stat::make('Open Findings', $openFindings)
                ->description("{$majorNCs} major non-conformance(s) open")
                ->icon('heroicon-o-flag')
                ->color($openFindings > 0 ? 'warning' : 'success'),

            Stat::make('Overdue Corrective Actions', $overdueFindings)
                ->description('Past target completion date — not yet closed')
                ->icon('heroicon-o-clock')
                ->color($overdueFindings > 0 ? 'danger' : 'success'),

            Stat::make('Regulatory Impact Findings', $regulatoryOpen)
                ->description('Open findings with regulatory implications')
                ->icon('heroicon-o-building-library')
                ->color($regulatoryOpen > 0 ? 'danger' : 'success'),

            Stat::make('Findings Closed This Month', $closedThisMonth)
                ->description(now()->format('F Y'))
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
