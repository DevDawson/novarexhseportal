<?php

namespace App\Services;

use App\Models\InternalAudit;
use App\Models\AuditNonConformity;

class AuditManagementService
{
    // ----------------------------------------------------------------
    // Risk scoring: Likelihood × Severity (1–25)
    // ----------------------------------------------------------------

    public static function riskLevel(int $score): string
    {
        return match (true) {
            $score >= 13 => 'high',
            $score >= 6  => 'medium',
            default      => 'low',
        };
    }

    public static function riskLevelLabel(string $level): string
    {
        return match ($level) {
            'high'   => 'High (13–25) — Unacceptable, immediate action required',
            'medium' => 'Medium (6–12) — Manageable, corrective actions required',
            default  => 'Low (1–5) — Acceptable, monitor and maintain controls',
        };
    }

    public static function riskColor(string $level): string
    {
        return match ($level) {
            'high'   => 'danger',
            'medium' => 'warning',
            default  => 'success',
        };
    }

    // ----------------------------------------------------------------
    // Auto-reference generators
    // ----------------------------------------------------------------

    public static function nextNcNumber(int $auditId): string
    {
        $count = \App\Models\AuditNonConformity::withTrashed()
            ->where('internal_audit_id', $auditId)
            ->count();
        return 'NC-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
    }

    public static function nextCapaNumber(int $auditId): string
    {
        $count = \App\Models\AuditCapaAction::withTrashed()
            ->where('internal_audit_id', $auditId)
            ->count();
        return 'CAPA-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // Recompute audit-level stats: total_findings, open_ncs, compliance_score
    // ----------------------------------------------------------------

    public static function recomputeStats(InternalAudit $audit): void
    {
        $items = $audit->checklistItems()->get();

        $applicable = $items->where('response', '!=', 'not_applicable')
                            ->where('response', '!=', 'not_assessed');

        $compliant = $applicable->where('response', 'compliant')->count();
        $total     = $applicable->count();

        $score = $total > 0 ? round(($compliant / $total) * 100, 2) : null;

        $openNcs = $audit->nonConformities()
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $totalFindings = $audit->nonConformities()->count()
            + $audit->findings()->count();

        $audit->timestamps = false;
        $audit->update([
            'compliance_score' => $score,
            'open_ncs'         => $openNcs,
            'total_findings'   => $totalFindings,
        ]);
        $audit->timestamps = true;
    }

    // ----------------------------------------------------------------
    // ISO Checklist Templates
    // ----------------------------------------------------------------

    public static function standardLabels(): array
    {
        return [
            'iso9001'  => 'ISO 9001:2015 — Quality Management',
            'iso14001' => 'ISO 14001:2015 — Environmental Management',
            'iso45001' => 'ISO 45001:2018 — OH&S Management',
            'iso50001' => 'ISO 50001:2018 — Energy Management',
            'other'    => 'Other / Custom',
        ];
    }

    public static function checklistTemplate(string $standard): array
    {
        return match ($standard) {
            'iso9001'  => self::iso9001Checklist(),
            'iso14001' => self::iso14001Checklist(),
            'iso45001' => self::iso45001Checklist(),
            'iso50001' => self::iso50001Checklist(),
            default    => [],
        };
    }

    // ----------------------------------------------------------------
    // ISO 9001:2015 — Quality Management
    // ----------------------------------------------------------------

    private static function iso9001Checklist(): array
    {
        $items = [
            ['4.1',     'Has the organization determined the external and internal issues relevant to its purpose and that affect its ability to achieve the intended results of the QMS?'],
            ['4.2',     'Has the organization determined the interested parties relevant to the QMS and their relevant requirements?'],
            ['4.4',     'Has the organization established, implemented, maintained, and continually improved a QMS including the processes needed and their interactions?'],
            ['5.1',     'Does top management demonstrate leadership and commitment with respect to the QMS?'],
            ['5.2',     'Has top management established, implemented, and maintained a quality policy appropriate to the organization\'s purpose?'],
            ['5.3',     'Have organizational roles, responsibilities, and authorities relevant to the QMS been assigned and communicated?'],
            ['6.1',     'Has the organization determined the risks and opportunities that need to be addressed to give assurance that the QMS can achieve its intended results?'],
            ['6.2',     'Has the organization established quality objectives at relevant functions, levels, and processes? Are they monitored and updated as appropriate?'],
            ['7.1',     'Has the organization determined and provided the resources needed for the establishment, implementation, maintenance, and continual improvement of the QMS?'],
            ['7.2',     'Has the organization determined the necessary competence of persons under its control performing work that affects QMS performance?'],
            ['7.3',     'Are persons doing work under the organization\'s control aware of the quality policy, relevant quality objectives, and their contribution to QMS effectiveness?'],
            ['7.4',     'Has the organization determined the internal and external communications relevant to the QMS, including on what, when, with whom, how, and who communicates?'],
            ['7.5',     'Does the organization\'s QMS include documented information required by ISO 9001 and determined as necessary for effectiveness?'],
            ['8.1',     'Has the organization planned, implemented, controlled, monitored, and reviewed the processes needed to meet requirements for the provision of products and services?'],
            ['8.5.1',   'Has the organization implemented production and service provision under controlled conditions (instructions, monitoring, equipment validation)?'],
            ['9.1.1',   'Has the organization determined what needs to be monitored and measured to evaluate QMS performance and effectiveness?'],
            ['9.2',     'Does the organization conduct internal audits at planned intervals to provide information on whether the QMS conforms to requirements?'],
            ['9.3',     'Does top management review the QMS at planned intervals to ensure its continued suitability, adequacy, effectiveness, and alignment with strategic direction?'],
            ['10.2',    'When a nonconformity occurs, does the organization react, evaluate, and implement corrective action, and retain documented information?'],
            ['10.3',    'Does the organization continually improve the suitability, adequacy, and effectiveness of the QMS?'],
        ];

        return self::mapToRows($items, 'iso9001');
    }

    // ----------------------------------------------------------------
    // ISO 14001:2015 — Environmental Management
    // ----------------------------------------------------------------

    private static function iso14001Checklist(): array
    {
        $items = [
            ['4.1',   'Has the organization determined external and internal issues relevant to its purpose and that affect its ability to achieve the intended outcomes of its EMS?'],
            ['4.2',   'Has the organization determined the interested parties relevant to the EMS and their relevant needs and expectations?'],
            ['5.1',   'Does top management demonstrate leadership and commitment with respect to the EMS?'],
            ['5.2',   'Has top management established, implemented, and maintained an environmental policy?'],
            ['6.1.1', 'Has the organization established, implemented, and maintained processes needed to meet the requirements for considering risks and opportunities?'],
            ['6.1.2', 'Has the organization identified its environmental aspects and determined those that have significant environmental impacts?'],
            ['6.1.3', 'Has the organization determined and had access to the compliance obligations related to its environmental aspects?'],
            ['6.2',   'Has the organization established environmental objectives at relevant functions and levels? Are they measured and evaluated?'],
            ['7.2',   'Has the organization determined the competence of persons performing work under its control that affects its environmental performance?'],
            ['7.4',   'Has the organization established internal and external communication processes relevant to the EMS?'],
            ['8.1',   'Has the organization established, implemented, controlled, and maintained the processes needed to meet EMS requirements and implement the actions?'],
            ['8.2',   'Has the organization established, implemented, and maintained processes needed to prepare for and respond to potential emergency situations?'],
            ['9.1.1', 'Has the organization established, implemented, and maintained processes to monitor, measure, analyze, and evaluate its environmental performance?'],
            ['9.2',   'Does the organization conduct internal EMS audits at planned intervals?'],
            ['9.3',   'Does top management review the EMS at planned intervals?'],
            ['10.2',  'When a nonconformity occurs, does the organization react and take action to control and correct it, and implement corrective action?'],
        ];

        return self::mapToRows($items, 'iso14001');
    }

    // ----------------------------------------------------------------
    // ISO 45001:2018 — OH&S Management
    // ----------------------------------------------------------------

    private static function iso45001Checklist(): array
    {
        $items = [
            ['5.1',   'Does top management demonstrate leadership and commitment with respect to the OH&S MS?'],
            ['5.2',   'Has top management established, implemented, and maintained an OH&S policy?'],
            ['5.4',   'Has the organization established, implemented, and maintained processes for consultation and participation of workers?'],
            ['6.1.1', 'Has the organization established processes for determining risks and opportunities considering hazards, OH&S risks, and other risks?'],
            ['6.1.2', 'Has the organization established, implemented, and maintained processes for ongoing hazard identification and assessment of OH&S risks?'],
            ['6.1.3', 'Has the organization determined and had access to up-to-date legal requirements and other requirements applicable to its hazards?'],
            ['6.2',   'Has the organization established OH&S objectives at relevant functions and levels? Are they measurable and monitored?'],
            ['7.2',   'Has the organization determined and provided the competence necessary for workers to perform work that affects OH&S performance?'],
            ['7.4',   'Has the organization established processes needed for internal and external communications relevant to the OH&S MS?'],
            ['8.1.1', 'Has the organization planned, implemented, controlled, and maintained the processes needed to meet requirements of the OH&S MS?'],
            ['8.1.2', 'Has the organization established a process for the elimination of hazards and reduction of OH&S risks using the hierarchy of controls?'],
            ['8.2',   'Has the organization established, implemented, and maintained processes to manage temporary and permanent changes that impact OH&S performance?'],
            ['9.1.1', 'Has the organization established processes to monitor, measure, analyze, and evaluate OH&S performance?'],
            ['9.2',   'Does the organization conduct internal OH&S MS audits at planned intervals?'],
            ['9.3',   'Does top management review the OH&S MS at planned intervals?'],
            ['10.2',  'When an incident or nonconformity occurs, does the organization react, investigate, and implement corrective action?'],
        ];

        return self::mapToRows($items, 'iso45001');
    }

    // ----------------------------------------------------------------
    // ISO 50001:2018 — Energy Management
    // ----------------------------------------------------------------

    private static function iso50001Checklist(): array
    {
        $items = [
            ['5.1',   'Does top management demonstrate leadership and commitment with respect to the EnMS and continual improvement of energy performance?'],
            ['5.2',   'Has top management established, implemented, and maintained an energy policy?'],
            ['6.3',   'Has the organization conducted an energy review based on data and other information leading to identification of significant energy uses (SEUs)?'],
            ['6.4',   'Has the organization determined appropriate energy performance indicators (EnPIs) for monitoring energy performance?'],
            ['6.5',   'Has the organization established one or more energy baselines using the information from the energy review?'],
            ['6.6',   'Has the organization identified and planned the energy data collection requirements for monitoring of energy performance?'],
            ['6.7',   'Has the organization established energy objectives at relevant functions, levels, processes, or facilities?'],
            ['7.2',   'Has the organization determined the competence of persons performing work that affects energy performance and the effectiveness of the EnMS?'],
            ['7.4',   'Has the organization established communication processes with respect to energy performance and the EnMS?'],
            ['8.1',   'Has the organization planned, implemented, controlled, and maintained processes to ensure they are carried out under controlled conditions?'],
            ['8.2',   'Has the organization ensured energy performance requirements are considered when designing new, modified, or renovated facilities?'],
            ['8.3',   'Has the organization established and implemented criteria for evaluating energy performance over the expected operating lifetime when procuring energy-using products?'],
            ['9.1',   'Has the organization established and implemented a measurement plan to ensure key characteristics determining energy performance are monitored and measured?'],
            ['9.2',   'Does the organization conduct internal EnMS audits at planned intervals?'],
            ['9.3',   'Does top management review the EnMS at planned intervals?'],
            ['10.2',  'When a nonconformity occurs in the EnMS, does the organization react and implement appropriate corrective action?'],
        ];

        return self::mapToRows($items, 'iso50001');
    }

    // ----------------------------------------------------------------
    // Helper: map [clause, question] pairs into insert-ready rows
    // ----------------------------------------------------------------

    private static function mapToRows(array $items, string $standard): array
    {
        $rows = [];
        foreach ($items as $i => [$clause, $question]) {
            $rows[] = [
                'iso_standard'     => $standard,
                'clause_reference' => $standard === 'iso9001'  ? 'ISO 9001:'  . $clause :
                                     ($standard === 'iso14001' ? 'ISO 14001:' . $clause :
                                     ($standard === 'iso45001' ? 'ISO 45001:' . $clause :
                                                                 'ISO 50001:' . $clause)),
                'question'         => $question,
                'requirement_type' => 'mandatory',
                'response'         => 'not_assessed',
                'score'            => null,
                'evidence_notes'   => null,
                'auditor_notes'    => null,
                'sort_order'       => $i + 1,
            ];
        }
        return $rows;
    }
}
