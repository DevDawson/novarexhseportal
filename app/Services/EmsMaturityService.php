<?php

namespace App\Services;

use App\Models\CapaAction;
use App\Models\EmsImprovementAction;
use App\Models\EsgTarget;
use App\Models\EnvironmentalAudit;
use App\Models\EnvironmentalAuditChecklistItem;
use App\Models\Incident;
use App\Models\TrainingRecord;

class EmsMaturityService
{
    // ── EMI Component Weights (must sum to 100) ──────────────────────────
    public const WEIGHTS = [
        'cr'  => 25, // Compliance Rate
        'as'  => 20, // Audit Score
        'cac' => 20, // Corrective Action Closure Rate
        'oa'  => 20, // Objective Achievement Rate
        'tr'  => 15, // Training Completion Rate
    ];

    // ── Maturity levels per spec ─────────────────────────────────────────
    public static function emiToLevel(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Optimized',
            $score >= 80 => 'Managed',
            $score >= 70 => 'Defined',
            $score >= 60 => 'Developing',
            default      => 'Initial',
        };
    }

    public static function emiToStatus(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Good',
            $score >= 70 => 'Satisfactory',
            $score >= 60 => 'Needs Improvement',
            default      => 'Significant Improvement Required',
        };
    }

    public static function emiToColor(float $score): string
    {
        return match (true) {
            $score >= 90 => 'success',
            $score >= 80 => 'info',
            $score >= 70 => 'primary',
            $score >= 60 => 'warning',
            default      => 'danger',
        };
    }

    // ── KPI Component Calculations ────────────────────────────────────────

    // CR: Compliance Rate — compliant checklist items / total assessed (non-N/A)
    public static function complianceRate(): float
    {
        $total     = EnvironmentalAuditChecklistItem::where('compliance_status', '!=', 'not_applicable')->count();
        $compliant = EnvironmentalAuditChecklistItem::where('compliance_status', 'compliant')->count();
        return $total > 0 ? round(($compliant / $total) * 100, 2) : 0.0;
    }

    // AS: Audit Score — average compliance_score of completed/closed environmental audits
    public static function auditScore(): float
    {
        $avg = EnvironmentalAudit::whereIn('status', ['completed', 'closed'])
                   ->whereNotNull('compliance_score')
                   ->avg('compliance_score');
        return round((float) $avg, 2);
    }

    // CAC: Corrective Action Closure Rate — closed EMS CAPAs / total EMS CAPAs
    // Uses general CapaActions (category=environmental) + dedicated EMS improvement actions
    public static function capaClosureRate(): float
    {
        $totalCapa   = CapaAction::where('category', 'environmental')->count();
        $closedCapa  = CapaAction::where('category', 'environmental')->where('status', 'closed')->count();
        $totalEmsCI  = EmsImprovementAction::whereNotIn('status', ['cancelled'])->count();
        $closedEmsCI = EmsImprovementAction::where('status', 'closed')->count();
        $total  = $totalCapa + $totalEmsCI;
        $closed = $closedCapa + $closedEmsCI;
        return $total > 0 ? round(($closed / $total) * 100, 2) : 0.0;
    }

    // OA: Objective Achievement Rate — environmental targets achieved / total env targets
    public static function objectiveAchievementRate(): float
    {
        $total    = EsgTarget::where('category', 'environmental')->count();
        $achieved = EsgTarget::where('category', 'environmental')->where('status', 'achieved')->count();
        return $total > 0 ? round(($achieved / $total) * 100, 2) : 0.0;
    }

    // TR: Training Completion Rate — passed training records / total records
    public static function trainingCompletionRate(): float
    {
        $total  = TrainingRecord::count();
        $passed = TrainingRecord::where('result', 'passed')->count();
        return $total > 0 ? round(($passed / $total) * 100, 2) : 0.0;
    }

    // ── Full EMI Calculation ──────────────────────────────────────────────
    public static function calculate(): array
    {
        $cr  = self::complianceRate();
        $as  = self::auditScore();
        $cac = self::capaClosureRate();
        $oa  = self::objectiveAchievementRate();
        $tr  = self::trainingCompletionRate();

        // EMI = ((CR×25) + (AS×20) + (CAC×20) + (OA×20) + (TR×15)) / 100
        $emi = (($cr * 25) + ($as * 20) + ($cac * 20) + ($oa * 20) + ($tr * 15)) / 100;
        $emi = round($emi, 2);

        return [
            'emi'          => $emi,
            'level'        => self::emiToLevel($emi),
            'status'       => self::emiToStatus($emi),
            'color'        => self::emiToColor($emi),
            'components'   => [
                'cr'  => ['value' => $cr,  'weight' => 25, 'label' => 'Compliance Rate',               'weighted' => round($cr  * 25 / 100, 2)],
                'as'  => ['value' => $as,  'weight' => 20, 'label' => 'Audit Score',                   'weighted' => round($as  * 20 / 100, 2)],
                'cac' => ['value' => $cac, 'weight' => 20, 'label' => 'Corrective Action Closure Rate', 'weighted' => round($cac * 20 / 100, 2)],
                'oa'  => ['value' => $oa,  'weight' => 20, 'label' => 'Objective Achievement Rate',    'weighted' => round($oa  * 20 / 100, 2)],
                'tr'  => ['value' => $tr,  'weight' => 15, 'label' => 'Training Completion Rate',      'weighted' => round($tr  * 15 / 100, 2)],
            ],
        ];
    }

    // ── KPI 15.1–15.3 (Continual Improvement KPIs) ───────────────────────

    // KPI 15.1: Environmental Objective Achievement Rate
    public static function kpi151(): float
    {
        return self::objectiveAchievementRate();
    }

    // KPI 15.2: Improvement Action Closure Rate
    public static function kpi152(): float
    {
        $total  = EmsImprovementAction::whereNotIn('status', ['cancelled'])->count();
        $closed = EmsImprovementAction::where('status', 'closed')->count();
        return $total > 0 ? round(($closed / $total) * 100, 2) : 0.0;
    }

    // KPI 15.3: Repeat Environmental Incident Rate (lower = better)
    // Repeat = same incident_type='environmental' occurred more than once from same location
    public static function kpi153(): float
    {
        $totalEnvIncidents = Incident::where('incident_type', 'environmental')->count();
        if ($totalEnvIncidents === 0) return 0.0;

        // Count incidents from locations that have had 2+ environmental incidents
        $repeatCount = Incident::where('incident_type', 'environmental')
            ->selectRaw('location, COUNT(*) as cnt')
            ->groupBy('location')
            ->having('cnt', '>', 1)
            ->get()
            ->sum(fn ($row) => $row->cnt);

        return round(($repeatCount / $totalEnvIncidents) * 100, 2);
    }
}
