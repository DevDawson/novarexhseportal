<?php

namespace App\Filament\Widgets;

use App\Models\EnvironmentalAudit;
use App\Models\EnvironmentalAuditChecklistItem;
use App\Models\EnvironmentalAuditFinding;
use App\Models\WasteTrackingRecord;
use App\Services\EmsMaturityService;
use Filament\Widgets\ChartWidget;

class EmsKpiBarChartWidget extends ChartWidget
{
    protected static ?string $heading    = 'EMS KPIs — Current Year Performance (%)';
    protected static ?string $maxHeight  = '280px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort       = 12;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director']) ?? false;
    }

    protected function getType(): string { return 'bar'; }

    protected function getData(): array
    {
        try {
            $thisYear = now()->year;

            $totalAssessed  = EnvironmentalAuditChecklistItem::where('compliance_status', '!=', 'not_applicable')->count();
            $compliant      = EnvironmentalAuditChecklistItem::where('compliance_status', 'compliant')->count();
            $complianceRate = $totalAssessed > 0 ? round(($compliant / $totalAssessed) * 100, 1) : 0;

            $totalWaste   = (float) WasteTrackingRecord::whereYear('generation_date', $thisYear)->sum('quantity');
            $recycled     = (float) WasteTrackingRecord::whereYear('generation_date', $thisYear)
                ->whereIn('disposal_method', ['recycling', 'recovery', 'composting', 'reuse'])->sum('quantity');
            $wasteDiversion = $totalWaste > 0 ? round(($recycled / $totalWaste) * 100, 1) : 0;

            $totalAudits    = EnvironmentalAudit::whereYear('audit_date', $thisYear)->count();
            $completed      = EnvironmentalAudit::whereYear('audit_date', $thisYear)->whereIn('status', ['completed', 'closed'])->count();
            $auditCompletion = $totalAudits > 0 ? round(($completed / $totalAudits) * 100, 1) : 0;

            $totalFindings = EnvironmentalAuditFinding::count();
            $closedFindings = EnvironmentalAuditFinding::where('action_status', 'closed')->count();
            $capaRate = $totalFindings > 0 ? round(($closedFindings / $totalFindings) * 100, 1) : 0;

            $emi = EmsMaturityService::calculate();

            return [
                'datasets' => [[
                    'label'           => 'Score (%)',
                    'data'            => [$complianceRate, $wasteDiversion, $auditCompletion, $capaRate, round($emi['emi'], 1)],
                    'backgroundColor' => ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#06B6D4'],
                    'borderRadius'    => 6,
                ]],
                'labels' => ['Compliance Rate', 'Waste Diversion', 'Audit Completion', 'CAPA Closure', 'EMS Maturity (EMI)'],
            ];
        } catch (\Throwable) {
            return ['datasets' => [['label' => 'No data', 'data' => []]], 'labels' => []];
        }
    }

    protected function getOptions(): array
    {
        return [
            'scales'  => ['y' => ['beginAtZero' => true, 'max' => 100, 'title' => ['display' => true, 'text' => '%']]],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
