<?php

namespace App\Filament\Widgets;

use App\Models\HazardAction;
use App\Models\HazardRegister;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class HazidKpiWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $total = HazardRegister::count();

        $open = HazardRegister::whereNotIn('status', ['controlled', 'closed'])->count();

        $closed = HazardRegister::where('status', 'closed')->count();

        $highRisk = HazardRegister::where('residual_risk_score', '>=', 10)
            ->whereNotIn('status', ['controlled', 'closed'])
            ->count();

        $criticalRisk = HazardRegister::where('residual_risk_score', '>=', 16)
            ->whereNotIn('status', ['controlled', 'closed'])
            ->count();

        $overdueActions = HazardAction::where('closure_status', 'open')
            ->where('due_date', '<', now())
            ->count();

        // Average closure time in days (only for closed hazards with both date_identified and closure_date)
        $avgClosureDays = HazardRegister::where('status', 'closed')
            ->whereNotNull('date_identified')
            ->whereNotNull('closure_date')
            ->select(DB::raw('AVG(DATEDIFF(closure_date, date_identified)) as avg_days'))
            ->value('avg_days');
        $avgClosureDays = $avgClosureDays ? round((float) $avgClosureDays, 1) : null;

        // Verification compliance rate: verified / (controlled + closed) * 100
        $verifiableCount = HazardRegister::whereIn('status', ['controlled', 'closed'])->count();
        $verifiedCount = HazardRegister::whereIn('status', ['controlled', 'closed'])
            ->whereNotNull('verified_by_id')
            ->whereNotNull('verification_date')
            ->count();
        $complianceRate = $verifiableCount > 0
            ? round(($verifiedCount / $verifiableCount) * 100, 1)
            : null;

        // Monthly hazard reporting rate (current month)
        $thisMonthCount = HazardRegister::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Top hazard category (for description)
        $topCategory = HazardRegister::select('hazard_category', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('hazard_category')
            ->groupBy('hazard_category')
            ->orderByDesc('cnt')
            ->first();
        $topCategoryLabel = $topCategory
            ? (HazardRegister::HAZARD_CATEGORY_LABELS[$topCategory->hazard_category] ?? $topCategory->hazard_category)
              . ' (' . $topCategory->cnt . ')'
            : 'N/A';

        return [
            Stat::make('Total Hazards Reported', $total)
                ->description('All time — all statuses')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray'),

            Stat::make('Open Hazards', $open)
                ->description('Excludes controlled & closed')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($open > 0 ? 'danger' : 'success'),

            Stat::make('Closed Hazards', $closed)
                ->description(
                    $total > 0
                        ? round(($closed / $total) * 100, 1) . '% closure rate'
                        : 'No hazards yet'
                )
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('High-Risk Hazards (Open)', $highRisk)
                ->description('Residual risk score ≥ 10 and not closed')
                ->icon('heroicon-o-fire')
                ->color($highRisk > 0 ? 'warning' : 'success'),

            Stat::make('Critical-Risk Hazards (Open)', $criticalRisk)
                ->description('Residual risk score ≥ 16 — immediate action required')
                ->icon('heroicon-o-shield-exclamation')
                ->color($criticalRisk > 0 ? 'danger' : 'success'),

            Stat::make('Overdue Actions', $overdueActions)
                ->description('Open actions past due date')
                ->icon('heroicon-o-clock')
                ->color($overdueActions > 0 ? 'danger' : 'success'),

            Stat::make('Top Hazard Category', $topCategoryLabel)
                ->description('Most frequently reported hazard type')
                ->icon('heroicon-o-tag')
                ->color('primary'),

            Stat::make('Avg. Closure Time', $avgClosureDays !== null ? "{$avgClosureDays} days" : 'N/A')
                ->description('Average days from identification to closure')
                ->icon('heroicon-o-calendar-days')
                ->color('gray'),

            Stat::make('Verification Compliance', $complianceRate !== null ? "{$complianceRate}%" : 'N/A')
                ->description("Controlled/closed hazards with verified evidence ({$verifiedCount}/{$verifiableCount})")
                ->icon('heroicon-o-check-circle')
                ->color(
                    $complianceRate === null ? 'gray'
                    : ($complianceRate >= 80 ? 'success' : ($complianceRate >= 50 ? 'warning' : 'danger'))
                ),

            Stat::make('This Month Reported', $thisMonthCount)
                ->description(now()->format('F Y') . ' — monthly reporting rate')
                ->icon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
