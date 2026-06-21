<?php

namespace App\Services;

use App\Models\MaturityAssessment;
use App\Models\MaturityDimension;
use App\Models\MaturityScore;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MaturityScoringService
{
    // ── Auto-calculate a score (1–5) for indicators that have a source ──

    public static function autoScore(string $source, ?int $projectId): int
    {
        return match ($source) {
            'hazard_count'        => self::hazardCount($projectId),
            'risk_register_updates' => self::riskRegisterUpdates($projectId),
            'legal_register'      => self::legalRegister($projectId),
            'env_audits'          => self::envAudits($projectId),
            'ptw_usage'           => self::ptwUsage($projectId),
            'incident_reporting'  => self::incidentReporting($projectId),
            'rca_completion'      => self::rcaCompletion($projectId),
            'capa_closure'        => self::capaClosureRate($projectId),
            'training_coverage'   => self::trainingCoverage($projectId),
            'certifications'      => self::certifications(),
            'waste_tracking'      => self::wasteTracking($projectId),
            'env_monitoring'      => self::envMonitoring($projectId),
            'ams_audits'          => self::amsAudits($projectId),
            'nc_closure'          => self::ncClosure($projectId),
            default               => 1,
        };
    }

    // ── Dimension score (weighted average of its indicator scores) ────

    public static function dimensionScore(MaturityAssessment $assessment, MaturityDimension $dimension): float
    {
        $scores = $assessment->scores()
            ->whereHas('indicator', fn ($q) => $q->where('dimension_id', $dimension->id))
            ->pluck('score');

        if ($scores->isEmpty()) {
            return 0;
        }

        return round($scores->average(), 2);
    }

    // ── Overall HSE Maturity Index ────────────────────────────────────

    public static function overallScore(MaturityAssessment $assessment): float
    {
        $dimensions   = MaturityDimension::all();
        $totalWeight  = $dimensions->sum('weight');
        $weightedSum  = 0;

        foreach ($dimensions as $dim) {
            $dimScore     = self::dimensionScore($assessment, $dim);
            $weightedSum += $dimScore * $dim->weight;
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    // ── Recalculate and persist overall score on assessment ──────────

    public static function recalculate(MaturityAssessment $assessment): void
    {
        $score = self::overallScore($assessment);
        $assessment->update([
            'overall_score'  => $score,
            'maturity_level' => MaturityAssessment::scoreToLevel($score),
        ]);
    }

    // ── Dimension breakdown (for dashboard) ──────────────────────────

    public static function dimensionBreakdown(MaturityAssessment $assessment): Collection
    {
        return MaturityDimension::orderBy('sort_order')->get()->map(function ($dim) use ($assessment) {
            return [
                'code'   => $dim->code,
                'name'   => $dim->name,
                'weight' => $dim->weight,
                'score'  => self::dimensionScore($assessment, $dim),
                'level'  => MaturityAssessment::scoreToLevel(self::dimensionScore($assessment, $dim)),
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // Auto-calculation methods
    // ─────────────────────────────────────────────────────────────────

    private static function hazardCount(?int $projectId): int
    {
        $q = \App\Models\HazardRegister::query();
        if ($projectId) $q->where('project_id', $projectId);
        $count = $q->count();
        return match (true) {
            $count >= 20 => 5,
            $count >= 10 => 4,
            $count >= 5  => 3,
            $count >= 1  => 2,
            default      => 1,
        };
    }

    private static function riskRegisterUpdates(?int $projectId): int
    {
        $q = \App\Models\HazardRegister::query()->where('updated_at', '>=', now()->subDays(30));
        if ($projectId) $q->where('project_id', $projectId);
        $count = $q->count();
        return match (true) {
            $count >= 5 => 5,
            $count >= 3 => 4,
            $count >= 1 => 3,
            default     => 1,
        };
    }

    private static function legalRegister(?int $projectId): int
    {
        $count = \App\Models\LegalRegisterItem::count();
        return match (true) {
            $count >= 10 => 5,
            $count >= 5  => 4,
            $count >= 2  => 3,
            $count >= 1  => 2,
            default      => 1,
        };
    }

    private static function envAudits(?int $projectId): int
    {
        $q = \App\Models\EnvironmentalAudit::query()->where('created_at', '>=', now()->subYear());
        if ($projectId) $q->where('project_id', $projectId);
        $count = $q->count();
        return match (true) {
            $count >= 4 => 5,
            $count >= 2 => 4,
            $count >= 1 => 3,
            default     => 1,
        };
    }

    private static function ptwUsage(?int $projectId): int
    {
        $q = \App\Models\PermitToWork::query();
        if ($projectId) $q->where('project_id', $projectId);
        $total  = $q->count();
        $active = (clone $q)->whereIn('status', ['active', 'issued', 'closed'])->count();
        if ($total === 0) return 1;
        $ratio = $active / $total;
        return match (true) {
            $ratio >= 0.9 => 5,
            $ratio >= 0.7 => 4,
            $ratio >= 0.5 => 3,
            $ratio >= 0.2 => 2,
            default       => 1,
        };
    }

    private static function incidentReporting(?int $projectId): int
    {
        $q = \App\Models\Incident::query()->where('created_at', '>=', now()->subYear());
        if ($projectId) $q->where('project_id', $projectId);
        $total = $q->count();
        // Score based on volume of incidents recorded (proxy for reporting culture)
        return match (true) {
            $total >= 10 => 5,
            $total >= 5  => 4,
            $total >= 2  => 3,
            $total >= 1  => 2,
            default      => 1,
        };
    }

    private static function rcaCompletion(?int $projectId): int
    {
        $q = \App\Models\Incident::query()->where('created_at', '>=', now()->subYear());
        if ($projectId) $q->where('project_id', $projectId);
        $total = $q->count();
        $withRca = (clone $q)->whereNotNull('root_cause')->count();
        if ($total === 0) return 2;
        $ratio = $withRca / $total;
        return match (true) {
            $ratio >= 0.9 => 5,
            $ratio >= 0.7 => 4,
            $ratio >= 0.5 => 3,
            $ratio >= 0.3 => 2,
            default       => 1,
        };
    }

    private static function capaClosureRate(?int $projectId): int
    {
        $total  = \App\Models\CapaAction::count();
        $closed = \App\Models\CapaAction::where('status', 'closed')->count();
        if ($total === 0) return 2;
        $ratio = $closed / $total;
        return match (true) {
            $ratio >= 0.9 => 5,
            $ratio >= 0.7 => 4,
            $ratio >= 0.5 => 3,
            $ratio >= 0.3 => 2,
            default       => 1,
        };
    }

    private static function trainingCoverage(?int $projectId): int
    {
        $trained = \App\Models\TrainingRecord::distinct('staff_id')->count('staff_id');
        $total   = \App\Models\Staff::count();
        if ($total === 0) return 1;
        $ratio = $trained / $total;
        return match (true) {
            $ratio >= 0.9 => 5,
            $ratio >= 0.7 => 4,
            $ratio >= 0.5 => 3,
            $ratio >= 0.3 => 2,
            default       => 1,
        };
    }

    private static function certifications(): int
    {
        $active  = \App\Models\Certification::where('status', 'active')->count();
        $expired = \App\Models\Certification::where('status', 'expired')->count();
        if (($active + $expired) === 0) return 2;
        $ratio = $active / ($active + $expired);
        return match (true) {
            $ratio >= 0.95 => 5,
            $ratio >= 0.8  => 4,
            $ratio >= 0.6  => 3,
            $ratio >= 0.4  => 2,
            default        => 1,
        };
    }

    private static function wasteTracking(?int $projectId): int
    {
        $q = \App\Models\WasteTrackingRecord::query()->where('created_at', '>=', now()->subYear());
        if ($projectId) $q->where('project_id', $projectId);
        $count = $q->count();
        return match (true) {
            $count >= 12 => 5,
            $count >= 6  => 4,
            $count >= 3  => 3,
            $count >= 1  => 2,
            default      => 1,
        };
    }

    private static function envMonitoring(?int $projectId): int
    {
        $q = \App\Models\EnvironmentalMonitoringRecord::query()->where('created_at', '>=', now()->subYear());
        if ($projectId) $q->where('project_id', $projectId);
        $count = $q->count();
        return match (true) {
            $count >= 12 => 5,
            $count >= 6  => 4,
            $count >= 3  => 3,
            $count >= 1  => 2,
            default      => 1,
        };
    }

    private static function amsAudits(?int $projectId): int
    {
        $q = \App\Models\InternalAudit::query()->where('created_at', '>=', now()->subYear());
        if ($projectId) $q->where('project_id', $projectId);
        $count = $q->count();
        return match (true) {
            $count >= 4 => 5,
            $count >= 2 => 4,
            $count >= 1 => 3,
            default     => 1,
        };
    }

    private static function ncClosure(?int $projectId): int
    {
        // Use AmsNonConformity if it exists, otherwise fall back to a safe default
        if (!class_exists(\App\Models\AmsNonConformity::class)) {
            return 3;
        }
        $total  = \App\Models\AmsNonConformity::count();
        $closed = \App\Models\AmsNonConformity::where('status', 'closed')->count();
        if ($total === 0) return 2;
        $ratio = $closed / $total;
        return match (true) {
            $ratio >= 0.9 => 5,
            $ratio >= 0.7 => 4,
            $ratio >= 0.5 => 3,
            $ratio >= 0.3 => 2,
            default       => 1,
        };
    }
}
