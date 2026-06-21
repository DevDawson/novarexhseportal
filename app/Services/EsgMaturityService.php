<?php

namespace App\Services;

use App\Models\AuditNonConformity;
use App\Models\CapaAction;
use App\Models\CorporateDocument;
use App\Models\EnvironmentalAuditChecklistItem;
use App\Models\EnvironmentalAuditFinding;
use App\Models\EsgMaturityAssessment;
use App\Models\GovernancePolicy;
use App\Models\Grievance;
use App\Models\Incident;
use App\Models\Staff;
use App\Models\TrainingRecord;
use App\Models\WasteTrackingRecord;

class EsgMaturityService
{
    // ── Component weights (must sum to 100) ──────────────────────────────
    public const WEIGHTS = ['E' => 40, 'S' => 30, 'G' => 30];

    // ─────────────────────────────────────────────────────────────────────
    // AUTO-CALCULABLE INDICATORS
    // ─────────────────────────────────────────────────────────────────────

    // E: CR — Compliance Rate (from environmental audit checklist)
    public static function calcCR(): array
    {
        $total     = EnvironmentalAuditChecklistItem::where('compliance_status', '!=', 'not_applicable')->count();
        $compliant = EnvironmentalAuditChecklistItem::where('compliance_status', 'compliant')->count();
        $score = $total > 0 ? round(($compliant / $total) * 100, 2) : 0.0;
        return ['score' => $score, 'basis' => "$compliant compliant / $total assessed checklist items", 'source' => 'auto'];
    }

    // E: WR — Waste Diversion/Recycling Rate
    public static function calcWR(): array
    {
        $thisYear = now()->year;
        $total    = (float) WasteTrackingRecord::whereYear('generation_date', $thisYear)->sum('quantity');
        $recycled = (float) WasteTrackingRecord::whereYear('generation_date', $thisYear)
                        ->whereIn('disposal_method', ['recycling', 'recovery', 'composting', 'reuse'])->sum('quantity');
        $score = $total > 0 ? round(($recycled / $total) * 100, 2) : 0.0;
        $basis = $total > 0
            ? number_format($recycled, 1) . ' diverted / ' . number_format($total, 1) . ' total (' . $thisYear . ')'
            : "No waste records for $thisYear — enter manually";
        return ['score' => $score, 'basis' => $basis, 'source' => $total > 0 ? 'auto' : 'manual_required'];
    }

    // E: ER — Emissions Reduction Rate (GHG, year-on-year from monitoring)
    // metric_type patterns: ghg_emissions, emissions, co2
    public static function calcER(): array
    {
        $thisYear = now()->year;
        $current  = (float) \DB::table('environmental_monitoring_records')
                        ->whereYear('record_date', $thisYear)
                        ->where(fn($q) => $q->where('metric_type', 'like', '%ghg%')
                            ->orWhere('metric_type', 'like', '%emission%')
                            ->orWhere('metric_type', 'like', '%co2%'))
                        ->sum('value');
        $baseline = (float) \DB::table('environmental_monitoring_records')
                        ->whereYear('record_date', $thisYear - 1)
                        ->where(fn($q) => $q->where('metric_type', 'like', '%ghg%')
                            ->orWhere('metric_type', 'like', '%emission%')
                            ->orWhere('metric_type', 'like', '%co2%'))
                        ->sum('value');

        if ($baseline > 0 && $current >= 0) {
            $reduction = (($baseline - $current) / $baseline) * 100;
            // Convert reduction % to performance score: 0% reduction = 50, 100% reduction = 100, increase = lower
            $score = max(0, min(100, round(50 + $reduction, 2)));
            $basis = "Baseline (" . ($thisYear - 1) . "): " . number_format($baseline, 2) . " → Current: " . number_format($current, 2) . " (reduction: " . number_format($reduction, 1) . "%)";
            return ['score' => $score, 'basis' => $basis, 'source' => 'semi_auto'];
        }
        return ['score' => null, 'basis' => 'No GHG monitoring data — enter score manually (0–100%)', 'source' => 'manual_required'];
    }

    // E: WTR — Water Reduction Efficiency (year-on-year)
    public static function calcWTR(): array
    {
        $thisYear = now()->year;
        $current  = (float) \DB::table('environmental_monitoring_records')
                        ->whereYear('record_date', $thisYear)
                        ->where(fn($q) => $q->where('metric_type', 'like', '%water%'))
                        ->sum('value');
        $baseline = (float) \DB::table('environmental_monitoring_records')
                        ->whereYear('record_date', $thisYear - 1)
                        ->where(fn($q) => $q->where('metric_type', 'like', '%water%'))
                        ->sum('value');

        if ($baseline > 0) {
            $reduction = (($baseline - $current) / $baseline) * 100;
            $score     = max(0, min(100, round(50 + $reduction, 2)));
            $basis = "Baseline (" . ($thisYear - 1) . "): " . number_format($baseline, 2)
                   . " → Current: " . number_format($current, 2)
                   . " (" . number_format($reduction, 1) . "% reduction)";
            return ['score' => $score, 'basis' => $basis, 'source' => 'semi_auto'];
        }
        return ['score' => null, 'basis' => 'No water monitoring data — enter score manually (0–100%)', 'source' => 'manual_required'];
    }

    // E: EMS — EMS Maturity Index (live from EmsMaturityService)
    public static function calcEMS(): array
    {
        $emi = EmsMaturityService::calculate();
        return [
            'score'  => $emi['emi'],
            'basis'  => "Live EMI: {$emi['emi']}% ({$emi['level']})",
            'source' => 'auto',
        ];
    }

    // S: TR — Training Completion Rate
    public static function calcTR(): array
    {
        $total  = TrainingRecord::count();
        $passed = TrainingRecord::where('result', 'passed')->count();
        $score  = $total > 0 ? round(($passed / $total) * 100, 2) : 0.0;
        return ['score' => $score, 'basis' => "$passed passed / $total training records", 'source' => 'auto'];
    }

    // S: LTIFR — Lost Time Injury Performance Score
    // No explicit LTI field; use severity='high' incidents as LTI proxy.
    // Score: 100 = zero high-severity incidents, decreasing with count.
    public static function calcLTIFR(): array
    {
        $thisYear = now()->year;
        $lti    = Incident::whereYear('incident_date', $thisYear)->where('severity', 'high')->count();
        $total  = Incident::whereYear('incident_date', $thisYear)->count();

        if ($total === 0) {
            return ['score' => 100.0, 'basis' => 'No incidents recorded this year — score = 100', 'source' => 'auto'];
        }
        // LTIFR proxy: score = (1 - lti/total) * 100, minimum 20
        $score = max(20.0, round((1 - ($lti / $total)) * 100, 2));
        return [
            'score'  => $score,
            'basis'  => "$lti high-severity incidents out of $total total ($thisYear); proxy score",
            'source' => 'semi_auto',
        ];
    }

    // S: CSR — Community Engagement Score (grievance resolution rate)
    public static function calcCSR(): array
    {
        $total    = \DB::table('grievances')->count();
        $resolved = \DB::table('grievances')->whereIn('status', ['resolved', 'closed'])->count();
        $engagements = \DB::table('stakeholder_engagements')->count();

        if ($total > 0 || $engagements > 0) {
            // Weight grievance resolution (70%) + engagement activity (30%)
            $griScore = $total > 0 ? round(($resolved / $total) * 100, 2) : 100.0;
            // Engagement score: ≥12/year = 100, ≥6 = 75, ≥3 = 50, <3 = 25
            $engScore = match (true) {
                $engagements >= 12 => 100,
                $engagements >= 6  => 75,
                $engagements >= 3  => 50,
                $engagements >= 1  => 30,
                default            => 0,
            };
            $score = round($griScore * 0.7 + $engScore * 0.3, 2);
            $basis = "Grievances: $resolved resolved/$total · Stakeholder engagements: $engagements";
            return ['score' => $score, 'basis' => $basis, 'source' => 'semi_auto'];
        }
        return ['score' => null, 'basis' => 'No grievance or engagement records — enter manually', 'source' => 'manual_required'];
    }

    // S: DEI — Gender diversity + employment inclusivity proxy
    public static function calcDEI(): array
    {
        $total  = Staff::where('status', 'active')->count();
        $female = Staff::where('status', 'active')->where('gender', 'female')->count();

        if ($total > 0) {
            $femaleRatio = $female / $total;
            // Score: 50% female = 100, 30%+ = 80, 20%+ = 60, 10%+ = 40, <10% = 20
            $score = match (true) {
                $femaleRatio >= 0.50 => 100.0,
                $femaleRatio >= 0.35 => 85.0,
                $femaleRatio >= 0.25 => 70.0,
                $femaleRatio >= 0.15 => 55.0,
                default              => 35.0,
            };
            $pct = round($femaleRatio * 100, 1);
            return [
                'score'  => $score,
                'basis'  => "$female female / $total active staff ({$pct}% female); gender proxy DEI score",
                'source' => 'semi_auto',
            ];
        }
        return ['score' => null, 'basis' => 'No active staff records — enter DEI score manually', 'source' => 'manual_required'];
    }

    // G: CCR — Compliance & Ethics Score (governance policies + legal compliance)
    public static function calcCCR(): array
    {
        $total  = \DB::table('governance_policies')->count();
        $active = \DB::table('governance_policies')->where('status', 'active')->count();

        if ($total > 0) {
            $score = round(($active / $total) * 100, 2);
            return ['score' => $score, 'basis' => "$active active / $total governance policies", 'source' => 'semi_auto'];
        }
        return ['score' => null, 'basis' => 'No governance policies recorded — enter CCR score manually', 'source' => 'manual_required'];
    }

    // G: ACR — Audit Closure Rate (AMS NCs + Environmental Audit Findings)
    public static function calcACR(): array
    {
        $amsTotal  = \DB::table('audit_non_conformities')->count();
        $amsClosed = \DB::table('audit_non_conformities')->where('status', 'closed')->count();
        $envTotal  = EnvironmentalAuditFinding::count();
        $envClosed = EnvironmentalAuditFinding::where('action_status', 'closed')->count();
        $total  = $amsTotal + $envTotal;
        $closed = $amsClosed + $envClosed;
        $score  = $total > 0 ? round(($closed / $total) * 100, 2) : 0.0;
        return [
            'score'  => $score,
            'basis'  => "AMS: $amsClosed/$amsTotal closed · Env Audit: $envClosed/$envTotal closed",
            'source' => 'auto',
        ];
    }

    // G: DCR — Document Control Compliance Rate (active/non-expired docs)
    public static function calcDCR(): array
    {
        $total  = \DB::table('corporate_documents')->count();
        $active = \DB::table('corporate_documents')->where('status', 'active')->count();
        $score  = $total > 0 ? round(($active / $total) * 100, 2) : 0.0;
        return [
            'score'  => $score,
            'basis'  => "$active active / $total corporate documents (not expired)",
            'source' => 'auto',
        ];
    }

    // G: ECR — Corrective Action Closure Rate
    public static function calcECR(): array
    {
        $total  = CapaAction::count();
        $closed = CapaAction::where('status', 'closed')->count();
        $score  = $total > 0 ? round(($closed / $total) * 100, 2) : 0.0;
        return ['score' => $score, 'basis' => "$closed closed / $total CAPA actions", 'source' => 'auto'];
    }

    // ─────────────────────────────────────────────────────────────────────
    // COMPOSITE CALCULATION
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Auto-fill all calculable indicators and return their raw values + bases.
     * Manual-required indicators return null score.
     */
    public static function autoFill(): array
    {
        return [
            'cr'    => self::calcCR(),
            'wr'    => self::calcWR(),
            'er'    => self::calcER(),
            'wtr'   => self::calcWTR(),
            'ems'   => self::calcEMS(),
            'tr'    => self::calcTR(),
            'ltifr' => self::calcLTIFR(),
            'ewr'   => ['score' => null, 'basis' => 'Manual: Employee Well-being surveys, HR wellness data',   'source' => 'manual'],
            'csr'   => self::calcCSR(),
            'dei'   => self::calcDEI(),
            'ccr'   => self::calcCCR(),
            'acr'   => self::calcACR(),
            'dcr'   => self::calcDCR(),
            'ecr'   => self::calcECR(),
            'mrr'   => ['score' => null, 'basis' => 'Manual: Management review meetings held / planned this year', 'source' => 'manual'],
        ];
    }

    /**
     * Compute E, S, G and ESG-MI from a flat array of 15 scores.
     * Any null score is treated as 0 for formula purposes.
     */
    public static function computeFromScores(array $scores): array
    {
        $get = fn(string $key) => (float) ($scores[$key] ?? 0);

        $e = round(($get('cr') + $get('wr') + $get('er') + $get('wtr') + $get('ems')) / 5, 2);
        $s = round(($get('tr') + $get('ltifr') + $get('ewr') + $get('csr') + $get('dei')) / 5, 2);
        $g = round(($get('ccr') + $get('acr') + $get('dcr') + $get('ecr') + $get('mrr')) / 5, 2);

        // ESG-MI = ((E×40) + (S×30) + (G×30)) / 100
        $esgMi = round(($e * 40 + $s * 30 + $g * 30) / 100, 2);

        $level = EsgMaturityAssessment::emiToLevel($esgMi);

        return [
            'e'      => $e,
            's'      => $s,
            'g'      => $g,
            'esg_mi' => $esgMi,
            'level'  => $level,
            'color'  => EsgMaturityAssessment::emiToColor($level),
        ];
    }

    /**
     * Compute and save composites on a given assessment model.
     */
    public static function recalculate(EsgMaturityAssessment $a): void
    {
        $scores = [
            'cr'    => (float) $a->cr_score,
            'wr'    => (float) $a->wr_score,
            'er'    => (float) $a->er_score,
            'wtr'   => (float) $a->wtr_score,
            'ems'   => (float) $a->ems_score,
            'tr'    => (float) $a->tr_score,
            'ltifr' => (float) $a->ltifr_score,
            'ewr'   => (float) $a->ewr_score,
            'csr'   => (float) $a->csr_score,
            'dei'   => (float) $a->dei_score,
            'ccr'   => (float) $a->ccr_score,
            'acr'   => (float) $a->acr_score,
            'dcr'   => (float) $a->dcr_score,
            'ecr'   => (float) $a->ecr_score,
            'mrr'   => (float) $a->mrr_score,
        ];

        $result = self::computeFromScores($scores);
        $a->timestamps = false;
        $a->update([
            'e_score'  => $result['e'],
            's_score'  => $result['s'],
            'g_score'  => $result['g'],
            'esg_mi'   => $result['esg_mi'],
        ]);
        $a->timestamps = true;
    }

    /**
     * Return the latest finalized assessment's computed breakdown, or live auto-fill if none.
     */
    public static function latestOrLive(): array
    {
        $latest = EsgMaturityAssessment::where('status', 'finalized')
                      ->orderByDesc('period')
                      ->first();

        if ($latest) {
            $scores = [
                'cr' => $latest->cr_score, 'wr' => $latest->wr_score,
                'er' => $latest->er_score, 'wtr' => $latest->wtr_score,
                'ems' => $latest->ems_score,
                'tr' => $latest->tr_score, 'ltifr' => $latest->ltifr_score,
                'ewr' => $latest->ewr_score, 'csr' => $latest->csr_score,
                'dei' => $latest->dei_score,
                'ccr' => $latest->ccr_score, 'acr' => $latest->acr_score,
                'dcr' => $latest->dcr_score, 'ecr' => $latest->ecr_score,
                'mrr' => $latest->mrr_score,
            ];
            $composite = [
                'e' => $latest->e_score, 's' => $latest->s_score,
                'g' => $latest->g_score, 'esg_mi' => $latest->esg_mi,
                'level' => EsgMaturityAssessment::emiToLevel((float) $latest->esg_mi),
                'color' => EsgMaturityAssessment::emiToColor(EsgMaturityAssessment::emiToLevel((float) $latest->esg_mi)),
            ];
            return compact('scores', 'composite', 'latest');
        }

        // No finalized assessment — compute live from available data
        $auto   = self::autoFill();
        $scores = array_map(fn($v) => $v['score'] ?? 0, $auto);
        $composite = self::computeFromScores($scores);

        return ['scores' => $scores, 'composite' => $composite, 'latest' => null, 'auto' => $auto];
    }
}
