<?php

namespace App\Filament\Widgets;

use App\Models\PermitToWork;
use App\Models\PtwInspection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PtwKpiWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $now       = Carbon::now();
        $thisMonth = Carbon::now()->startOfMonth();

        $activeStatuses = ['approved', 'active', 'suspended'];

        // Active permits
        $active = PermitToWork::whereIn('status', $activeStatuses)->count();

        // High-risk active
        $highRiskActive = PermitToWork::whereIn('status', $activeStatuses)
            ->where('risk_classification', 'high')
            ->count();

        // Pending approval
        $pendingApproval = PermitToWork::whereIn('status', ['submitted', 'under_review'])->count();

        // Due to expire today (valid_to = today and still active)
        $dueToday = PermitToWork::whereIn('status', $activeStatuses)
            ->whereDate('valid_to', $now->toDateString())
            ->count();

        // Overdue closures (past valid_to and still active)
        $overdueClosures = PermitToWork::whereIn('status', $activeStatuses)
            ->where('valid_to', '<', $now)
            ->count();

        // Issued this month
        $issuedThisMonth = PermitToWork::where('created_at', '>=', $thisMonth)->count();

        // Average compliance score from inspections this month
        $avgCompliance = PtwInspection::where('inspected_at', '>=', $thisMonth)
            ->avg('compliance_score');
        $avgComplianceLabel = $avgCompliance !== null
            ? number_format($avgCompliance, 1) . '%'
            : 'No data';

        // Top work type (this month)
        $topType = PermitToWork::where('created_at', '>=', $thisMonth)
            ->select('permit_type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('permit_type')
            ->orderByDesc('cnt')
            ->value('permit_type');
        $topTypeLabel = $topType
            ? (\App\Models\PermitToWork::PERMIT_TYPE_LABELS[$topType] ?? $topType)
            : 'None';
        // Shorten for display
        $topTypeShort = strlen($topTypeLabel) > 20
            ? substr($topTypeLabel, 0, 20) . '…'
            : $topTypeLabel;

        // Closed this month
        $closedThisMonth = PermitToWork::where('status', 'closed')
            ->where('closeout_at', '>=', $thisMonth)
            ->count();

        // High risk score (≥15) total active
        $highScoreActive = PermitToWork::whereIn('status', $activeStatuses)
            ->where('risk_score', '>=', 15)
            ->count();

        return [
            Stat::make('Active Permits', $active)
                ->description('Currently approved / in progress')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success'),

            Stat::make('High Risk Active', $highRiskActive)
                ->description('Active permits with HIGH classification')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($highRiskActive > 0 ? 'danger' : 'success'),

            Stat::make('Pending Approval', $pendingApproval)
                ->description('Awaiting supervisor / HSE / manager sign-off')
                ->icon('heroicon-o-clock')
                ->color($pendingApproval > 0 ? 'warning' : 'success'),

            Stat::make('Due to Expire Today', $dueToday)
                ->description('Permits expiring today (need closure or extension)')
                ->icon('heroicon-o-calendar-days')
                ->color($dueToday > 0 ? 'warning' : 'success'),

            Stat::make('Overdue Closures', $overdueClosures)
                ->description('Past valid_to and not yet closed')
                ->icon('heroicon-o-bell-alert')
                ->color($overdueClosures > 0 ? 'danger' : 'success'),

            Stat::make('Issued This Month', $issuedThisMonth)
                ->description(Carbon::now()->format('F Y'))
                ->icon('heroicon-o-document-plus')
                ->color('info'),

            Stat::make('Avg Compliance Score', $avgComplianceLabel)
                ->description('From site inspections this month')
                ->icon('heroicon-o-check-badge')
                ->color('primary'),

            Stat::make('Top Work Type', $topTypeShort)
                ->description('Most common permit type this month')
                ->icon('heroicon-o-fire')
                ->color('warning'),

            Stat::make('Closed This Month', $closedThisMonth)
                ->description('Formally closed in ' . Carbon::now()->format('F'))
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Risk Score ≥ 15 (Active)', $highScoreActive)
                ->description('Active permits with L×S score ≥ 15')
                ->icon('heroicon-o-shield-exclamation')
                ->color($highScoreActive > 0 ? 'danger' : 'success'),
        ];
    }
}
