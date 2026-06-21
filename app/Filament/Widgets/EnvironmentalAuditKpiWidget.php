<?php

namespace App\Filament\Widgets;

use App\Models\EnvironmentalAudit;
use App\Models\EnvironmentalAuditChecklistItem;
use App\Models\EnvironmentalAuditFinding;
use App\Models\EnvironmentalMonitoringRecord;
use App\Models\Incident;
use App\Models\WasteTrackingRecord;
use App\Services\EnvironmentalAuditService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnvironmentalAuditKpiWidget extends BaseWidget
{
    protected static ?int $sort = 19;
    protected static ?string $pollingInterval = '60s';

    public function getHeading(): string
    {
        return 'Environmental Audit & Performance KPIs (Steps 15–17)';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director', 'lead_auditor']) ?? false;
    }

    protected function getStats(): array
    {
        try {
            return $this->buildStats();
        } catch (\Throwable) {
            return [
                Stat::make('Environmental Audit KPIs', '—')
                    ->description('Pending: php artisan migrate --force on production')
                    ->color('gray'),
            ];
        }
    }

    private function buildStats(): array
    {
        $thisYear = now()->year;

        $totalAudits = EnvironmentalAudit::whereYear('audit_date', $thisYear)->count();
        $completed   = EnvironmentalAudit::whereYear('audit_date', $thisYear)
                          ->whereIn('status', ['completed', 'closed'])->count();
        $inProgress  = EnvironmentalAudit::where('status', 'in_progress')->count();

        $avgScore = round(
            EnvironmentalAudit::whereNotNull('compliance_score')
                ->whereIn('status', ['completed', 'closed'])
                ->avg('compliance_score') ?? 0,
            1
        );

        // ── KPI 1: Compliance Rate ───────────────────────────────────
        $totalAssessed  = EnvironmentalAuditChecklistItem::where('compliance_status', '!=', 'not_applicable')->count();
        $compliantItems = EnvironmentalAuditChecklistItem::where('compliance_status', 'compliant')->count();
        $complianceRate = $totalAssessed > 0 ? round(($compliantItems / $totalAssessed) * 100, 1) : 0;

        // ── KPI 2: Audit Completion Rate ─────────────────────────────
        $auditCompletionRate = $totalAudits > 0 ? round(($completed / $totalAudits) * 100, 1) : 0;

        // ── KPI 3: NCR Rate ──────────────────────────────────────────
        $totalFindings = EnvironmentalAuditFinding::count();
        $ncrFindings   = EnvironmentalAuditFinding::whereIn('finding_type', ['major_nc', 'minor_nc'])->count();
        $ncrRate       = $totalFindings > 0 ? round(($ncrFindings / $totalFindings) * 100, 1) : 0;

        // ── KPI 4: Corrective Action Closure Rate ────────────────────
        $closedFindings  = EnvironmentalAuditFinding::where('action_status', 'closed')->count();
        $capaClosureRate = $totalFindings > 0 ? round(($closedFindings / $totalFindings) * 100, 1) : 0;

        // ── KPI 5: Waste Diversion Rate ──────────────────────────────
        $totalWaste    = (float) WasteTrackingRecord::whereYear('generation_date', $thisYear)->sum('quantity');
        $recycledWaste = (float) WasteTrackingRecord::whereYear('generation_date', $thisYear)
                          ->whereIn('disposal_method', ['recycling', 'recovery', 'composting', 'reuse'])
                          ->sum('quantity');
        $wasteDiversionRate = $totalWaste > 0 ? round(($recycledWaste / $totalWaste) * 100, 1) : 0;

        // ── KPI 6: Water Reduction Rate ──────────────────────────────
        $currentWater  = (float) EnvironmentalMonitoringRecord::where('metric_type', 'like', '%water%')
                          ->whereYear('record_date', $thisYear)->sum('value');
        $baselineWater = (float) EnvironmentalMonitoringRecord::where('metric_type', 'like', '%water%')
                          ->whereYear('record_date', $thisYear - 1)->sum('value');
        $waterReductionRate = $baselineWater > 0
            ? round((($baselineWater - $currentWater) / $baselineWater) * 100, 1) : 0;

        // ── KPI 7: Environmental Incident Rate ───────────────────────
        $envIncidents = Incident::where('incident_type', 'environmental')
                          ->whereYear('incident_date', $thisYear)->count();
        $totalOpHours = (float) EnvironmentalAudit::whereYear('audit_date', $thisYear)
                          ->whereNotNull('total_operating_hours')
                          ->sum('total_operating_hours');
        $envIncidentRate = $totalOpHours > 0
            ? round(($envIncidents / $totalOpHours) * 100000, 2) : null;

        // ── Supporting stats ─────────────────────────────────────────
        $openFindings    = EnvironmentalAuditFinding::where('action_status', '!=', 'closed')->count();
        $majorNCs        = EnvironmentalAuditFinding::where('finding_type', 'major_nc')
                            ->where('action_status', '!=', 'closed')->count();
        $overdueFindings = EnvironmentalAuditFinding::where('action_status', '!=', 'closed')
                            ->whereNotNull('target_completion_date')
                            ->where('target_completion_date', '<', now())->count();
        $ratingLabel = $avgScore > 0
            ? EnvironmentalAuditService::ratingLabel(EnvironmentalAuditService::scoreToRating($avgScore))
            : 'No completed audits';

        return [
            Stat::make('KPI 1 — Compliance Rate', "{$complianceRate}%")
                ->description("Compliant: {$compliantItems} / Assessed: {$totalAssessed} checklist items")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color(match (true) {
                    $complianceRate >= 90 => 'success',
                    $complianceRate >= 80 => 'info',
                    $complianceRate >= 70 => 'warning',
                    default               => 'danger',
                }),

            Stat::make('KPI 2 — Audit Completion Rate', "{$auditCompletionRate}%")
                ->description("{$completed} completed / {$totalAudits} total · {$inProgress} in progress")
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color($auditCompletionRate >= 90 ? 'success' : ($auditCompletionRate >= 70 ? 'warning' : 'danger')),

            Stat::make('KPI 3 — NCR Rate', "{$ncrRate}%")
                ->description("{$ncrFindings} NCRs (major+minor) out of {$totalFindings} total findings")
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($ncrRate <= 10 ? 'success' : ($ncrRate <= 25 ? 'warning' : 'danger')),

            Stat::make('KPI 4 — CAPA Closure Rate', "{$capaClosureRate}%")
                ->description("{$closedFindings} closed · {$openFindings} open · {$overdueFindings} overdue")
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color($capaClosureRate >= 90 ? 'success' : ($capaClosureRate >= 70 ? 'warning' : 'danger')),

            Stat::make('KPI 5 — Waste Diversion Rate', "{$wasteDiversionRate}%")
                ->description(
                    $totalWaste > 0
                        ? number_format($recycledWaste, 1) . ' recycled / ' . number_format($totalWaste, 1) . ' total generated'
                        : 'No waste records for ' . $thisYear
                )
                ->descriptionIcon('heroicon-o-arrow-uturn-left')
                ->color($wasteDiversionRate >= 70 ? 'success' : ($wasteDiversionRate >= 40 ? 'warning' : 'danger')),

            Stat::make('KPI 6 — Water Reduction Rate', "{$waterReductionRate}%")
                ->description(
                    $baselineWater > 0
                        ? 'Baseline (' . ($thisYear - 1) . '): ' . number_format($baselineWater, 1)
                          . ' m³ → Current: ' . number_format($currentWater, 1) . ' m³'
                        : 'No prior-year water consumption records for baseline'
                )
                ->descriptionIcon('heroicon-o-beaker')
                ->color($waterReductionRate >= 10 ? 'success' : ($waterReductionRate >= 0 ? 'warning' : 'danger')),

            Stat::make('KPI 7 — Environmental Incident Rate', $envIncidentRate !== null ? number_format($envIncidentRate, 2) : '—')
                ->description(
                    $envIncidentRate !== null
                        ? "{$envIncidents} incidents / " . number_format($totalOpHours, 0) . ' op. hrs × 100,000'
                        : "{$envIncidents} env. incidents · enter operating hours in audit records to calculate"
                )
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color(
                    $envIncidentRate === null ? 'gray'
                        : ($envIncidentRate < 1 ? 'success' : ($envIncidentRate < 5 ? 'warning' : 'danger'))
                ),

            Stat::make('Avg Compliance Score', "{$avgScore}%")
                ->description($ratingLabel)
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($avgScore >= 90 ? 'success' : ($avgScore >= 80 ? 'info' : ($avgScore >= 70 ? 'warning' : 'danger'))),

            Stat::make('Open Major NCRs', $majorNCs)
                ->description("{$overdueFindings} overdue corrective actions")
                ->descriptionIcon('heroicon-o-flag')
                ->color($majorNCs > 0 ? 'danger' : 'success'),
        ];
    }
}

