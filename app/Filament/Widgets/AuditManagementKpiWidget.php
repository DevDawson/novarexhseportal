<?php

namespace App\Filament\Widgets;

use App\Models\AuditCapaAction;
use App\Models\AuditNonConformity;
use App\Models\InternalAudit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditManagementKpiWidget extends BaseWidget
{
    protected static ?int $sort = 18;

    public function getHeading(): string
    {
        return 'Audit Management System (AMS) — ISO 9001 / 14001 / 45001 / 50001';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    protected function getStats(): array
    {
        $thisYear = now()->year;

        $totalAudits  = InternalAudit::whereYear('audit_date', $thisYear)->count();
        $completed    = InternalAudit::whereYear('audit_date', $thisYear)
                            ->whereIn('status', ['completed', 'closed'])->count();
        $inProgress   = InternalAudit::where('status', 'in_progress')->count();

        $openNcs      = AuditNonConformity::whereIn('status', ['open', 'in_progress'])->count();
        $criticalNcs  = AuditNonConformity::whereIn('status', ['open', 'in_progress'])
                            ->where('nc_type', 'critical')->count();
        $majorNcs     = AuditNonConformity::whereIn('status', ['open', 'in_progress'])
                            ->where('nc_type', 'major')->count();

        $overdueNcs   = AuditNonConformity::whereIn('status', ['open', 'in_progress'])
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now())->count();

        $openCapa     = AuditCapaAction::whereIn('status', ['open', 'in_progress'])->count();
        $overdueCapa  = AuditCapaAction::whereIn('status', ['open', 'in_progress'])
                            ->where('target_date', '<', now())->count();
        $verifiedCapa = AuditCapaAction::where('status', 'verified')
                            ->whereMonth('updated_at', now()->month)
                            ->whereYear('updated_at', now()->year)->count();

        $avgScore = InternalAudit::whereNotNull('compliance_score')
                        ->whereIn('status', ['completed', 'closed'])
                        ->avg('compliance_score') ?? 0;

        return [
            Stat::make('Audits This Year', $totalAudits)
                ->description("{$completed} completed / {$inProgress} in progress")
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary'),

            Stat::make('Avg. Checklist Compliance', round($avgScore, 1) . '%')
                ->description('Across completed audits with assessed checklists')
                ->icon('heroicon-o-chart-bar')
                ->color($avgScore >= 80 ? 'success' : ($avgScore >= 60 ? 'warning' : ($avgScore > 0 ? 'danger' : 'gray'))),

            Stat::make('Open Non-Conformities', $openNcs)
                ->description("{$criticalNcs} critical / {$majorNcs} major")
                ->icon('heroicon-o-exclamation-triangle')
                ->color($criticalNcs > 0 ? 'danger' : ($openNcs > 0 ? 'warning' : 'success')),

            Stat::make('Overdue NCs', $overdueNcs)
                ->description('Past due date — not yet closed')
                ->icon('heroicon-o-clock')
                ->color($overdueNcs > 0 ? 'danger' : 'success'),

            Stat::make('Open CAPA Actions', $openCapa)
                ->description("{$overdueCapa} overdue — past target date")
                ->icon('heroicon-o-wrench-screwdriver')
                ->color($overdueCapa > 0 ? 'danger' : ($openCapa > 0 ? 'warning' : 'success')),

            Stat::make('CAPA Verified This Month', $verifiedCapa)
                ->description(now()->format('F Y') . ' — effectiveness confirmed')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
