<?php

namespace App\Services;

use App\Models\EmsImprovementAction;
use App\Models\EnvironmentalAspect;
use App\Models\EnvironmentalAudit;
use App\Models\EnvironmentalMonitoringRecord;
use App\Models\EnvironmentalPermit;
use App\Models\EsiaReport;
use App\Models\EsgTarget;
use App\Models\Grievance;
use App\Models\GovernancePolicy;
use App\Models\HazardRegister;
use App\Models\HazopStudy;
use App\Models\Incident;
use App\Models\InternalAudit;
use App\Models\Invoice;
use App\Models\LegalRegisterItem;
use App\Models\MaturityAssessment;
use App\Models\PermitToWork;
use App\Models\Setting;
use App\Models\SocialIndicator;
use App\Models\SpillReport;
use App\Models\WasteTrackingRecord;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class DocxBuilderService
{
    // ── Page width in twips (A4 portrait, 1080-twip margins each side)
    private const PAGE_W = 9360;

    // ── Shared colour palette
    private const BLUE   = '1d4ed8';
    private const NAVY   = '1e3a5f';
    private const GRAY   = '6b7280';
    private const BLACK  = '1a1a2e';
    private const WHITE  = 'FFFFFF';

    // =================================================================
    // PUBLIC REPORT BUILDERS
    // =================================================================

    public static function hira(HazardRegister $hazard, string $initLevel, string $residLevel): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'HAZARD IDENTIFICATION & RISK ASSESSMENT', "HIRA-{$hazard->id}");

        self::h2($sec, '1. Hazard Details');
        self::fieldTable($sec, [
            'Project / Location'   => $hazard->project?->title ?? 'Company-wide',
            'Location'             => $hazard->location ?? '—',
            'Activity / Task'      => $hazard->activity_task ?? '—',
            'Hazard Category'      => $hazard->hazard_category ?? '—',
            'Hazard Description'   => $hazard->hazard_description ?? '—',
            'Who Might Be Harmed'  => $hazard->who_might_be_harmed ?? '—',
            'Responsible Person'   => $hazard->responsiblePerson?->name ?? $hazard->responsiblePerson?->full_name ?? '—',
            'Date Identified'      => $hazard->date_identified?->format('d M Y') ?? '—',
            'Status'               => ucfirst($hazard->status ?? '—'),
        ]);

        self::h2($sec, '2. Initial Risk Assessment (Before Controls)');
        self::fieldTable($sec, [
            'Likelihood'       => ($hazard->initial_likelihood ?? '—') . ' / 5',
            'Severity'         => ($hazard->initial_severity ?? '—') . ' / 5',
            'Risk Score (L×S)' => ($hazard->initial_risk_score ?? '—') . ' — ' . strtoupper($initLevel),
            'Existing Controls'=> $hazard->existing_controls ?? '—',
        ]);

        self::h2($sec, '3. Additional Controls');
        $desc = $hazard->additional_controls_description ?? '—';
        if (is_array($hazard->additional_controls) && count($hazard->additional_controls)) {
            $labels = \App\Models\HazardRegister::CONTROL_HIERARCHY_OPTIONS ?? [];
            $desc   = implode('; ', array_map(fn ($k) => $labels[$k] ?? $k, $hazard->additional_controls));
            if ($hazard->additional_controls_description) {
                $desc .= "\n" . $hazard->additional_controls_description;
            }
        }
        self::fieldTable($sec, ['Controls Applied' => $desc]);

        self::h2($sec, '4. Residual Risk Assessment (After Controls)');
        self::fieldTable($sec, [
            'Likelihood'           => ($hazard->residual_likelihood ?? '—') . ' / 5',
            'Severity'             => ($hazard->residual_severity ?? '—') . ' / 5',
            'Residual Risk Score'  => ($hazard->residual_risk_score ?? '—') . ' — ' . strtoupper($residLevel),
            'Review Date'          => $hazard->review_date?->format('d M Y') ?? '—',
        ]);

        self::footer($sec);
        return self::stream($word, "HIRA-{$hazard->id}-" . now()->format('Ymd'));
    }

    public static function incidentReport(Incident $incident, string $riskLevel): Response
    {
        [$word, $sec] = self::doc();
        $ref = 'INC-' . str_pad($incident->id, 5, '0', STR_PAD_LEFT);
        self::header($sec, 'INCIDENT INVESTIGATION REPORT', $ref);

        self::h2($sec, '1. Incident Details');
        self::fieldTable($sec, [
            'Incident Date'        => $incident->incident_date?->format('d M Y H:i') ?? '—',
            'Reported By'          => $incident->reportedBy?->name ?? '—',
            'Project / Location'   => ($incident->project?->title ?? 'Company-wide') . ($incident->location ? ' — ' . $incident->location : ''),
            'Incident Type'        => ucwords(str_replace('_', ' ', $incident->incident_type ?? '—')),
            'Severity'             => ucwords(str_replace('_', ' ', $incident->severity ?? '—')),
            'Risk Score'           => ($incident->risk_score ?? '—') . ' — ' . strtoupper($riskLevel),
            'Description'          => $incident->description ?? '—',
            'Immediate Action'     => $incident->immediate_action ?? '—',
        ]);

        self::h2($sec, '2. Root Cause Analysis');
        $rcaFields = ['Root Cause' => $incident->root_cause ?? '—'];
        foreach (range(1, 5) as $n) {
            $val = $incident->{"why_{$n}"} ?? null;
            if ($val) {
                $rcaFields["Why {$n}"] = $val;
            }
        }
        self::fieldTable($sec, $rcaFields);

        self::h2($sec, '3. Corrective Actions');
        $sec->addText($incident->corrective_actions ?? '— No corrective actions recorded —', self::font(9), self::para());

        self::h2($sec, '4. Investigation Status');
        self::fieldTable($sec, [
            'Status'              => ucwords(str_replace('_', ' ', $incident->investigation_status ?? '—')),
            'Investigation Date'  => $incident->investigation_date?->format('d M Y') ?? '—',
            'Investigated By'     => $incident->investigated_by ?? '—',
        ]);

        self::footer($sec);
        return self::stream($word, "{$ref}-Report-" . now()->format('Ymd'));
    }

    public static function auditReport(InternalAudit $audit): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'INTERNAL AUDIT REPORT', $audit->audit_reference);

        self::h2($sec, '1. Audit Information');
        self::fieldTable($sec, [
            'Audit Reference'  => $audit->audit_reference,
            'Audit Date'       => $audit->audit_date?->format('d M Y') ?? '—',
            'Audit Type'       => \App\Models\InternalAudit::AUDIT_TYPE_LABELS[$audit->audit_type] ?? $audit->audit_type ?? '—',
            'Standard'         => (\App\Models\InternalAudit::STANDARD_LABELS[$audit->standard] ?? $audit->standard ?? '—') . ($audit->standard_other ? ' (' . $audit->standard_other . ')' : ''),
            'Lead Auditor'     => $audit->leadAuditor?->name ?? '—',
            'Team Members'     => $audit->teamMembers?->pluck('name')->implode(', ') ?: '—',
            'Project / Area'   => $audit->project?->title ?? $audit->department?->name ?? 'Company-wide',
            'Location'         => $audit->audit_location ?? '—',
            'Status'           => \App\Models\InternalAudit::STATUS_LABELS[$audit->status] ?? $audit->status ?? '—',
            'Scope'            => $audit->scope ?? '—',
            'Objectives'       => $audit->objectives ?? '—',
        ]);

        if ($audit->summary) {
            self::h2($sec, '2. Executive Summary');
            $sec->addText($audit->summary, self::font(9), self::para());
        }

        self::h2($sec, '3. Audit Findings');
        if ($audit->findings && $audit->findings->isNotEmpty()) {
            $table = self::dataTable($sec, ['#', 'Clause', 'Type', 'Description', 'Corrective Action', 'Status'],
                [800, 1000, 1800, 3000, 2000, 1200]);
            foreach ($audit->findings as $i => $f) {
                $row = $table->addRow();
                self::td($row, (string)($i + 1), 800);
                self::td($row, $f->clause_reference ?? '—', 1000);
                self::td($row, \App\Models\AuditFinding::FINDING_TYPE_LABELS[$f->finding_type] ?? $f->finding_type ?? '—', 1800);
                self::td($row, $f->description ?? '—', 3000);
                self::td($row, $f->corrective_action ?? '—', 2000);
                self::td($row, \App\Models\AuditFinding::STATUS_LABELS[$f->status] ?? $f->status ?? '—', 1200);
            }
        } else {
            $sec->addText('No findings recorded.', self::font(9, self::GRAY), self::para());
        }

        self::footer($sec);
        return self::stream($word, "{$audit->audit_reference}-Report-" . now()->format('Ymd'));
    }

    public static function environmentalAspect(EnvironmentalAspect $aspect, string $sigLevel): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'ENVIRONMENTAL ASPECT & IMPACT ASSESSMENT', "EMS-{$aspect->id}");

        self::h2($sec, '1. Aspect Details');
        self::fieldTable($sec, [
            'Project'              => $aspect->project?->title ?? 'Company-wide',
            'Activity / Process'   => $aspect->activity_process ?? '—',
            'Impact Category'      => ucwords(str_replace('_', ' ', $aspect->impact_category ?? '—')),
            'Environmental Aspect' => $aspect->environmental_aspect ?? '—',
            'Environmental Impact' => $aspect->environmental_impact ?? '—',
            'Condition'            => ucfirst($aspect->condition ?? '—'),
            'Responsible Person'   => $aspect->responsiblePerson?->name ?? $aspect->responsiblePerson?->full_name ?? '—',
            'Target Completion'    => $aspect->target_completion_date?->format('d M Y') ?? '—',
            'Status'               => ucfirst($aspect->status ?? '—'),
        ]);

        self::h2($sec, '2. Significance Assessment');
        self::fieldTable($sec, [
            'Likelihood'         => ($aspect->likelihood ?? '—') . ' / 5',
            'Severity'           => ($aspect->severity ?? '—') . ' / 5',
            'Significance Score' => ($aspect->significance_score ?? '—') . ' — ' . strtoupper($sigLevel),
        ]);

        self::h2($sec, '3. Controls');
        self::fieldTable($sec, [
            'Existing Controls' => $aspect->existing_controls ?? '—',
            'Proposed Controls' => $aspect->proposed_controls ?? '—',
        ]);

        self::footer($sec);
        return self::stream($word, "EMS-Aspect-{$aspect->id}-" . now()->format('Ymd'));
    }

    public static function esiaReport(EsiaReport $report, $screening, $scopingIssues, $baselineData, $impacts, $mitigations, $submissions): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'ENVIRONMENTAL & SOCIAL IMPACT ASSESSMENT REPORT', "ESIA-{$report->project_id}-v{$report->version}");

        self::h2($sec, '1. Report Information');
        self::fieldTable($sec, [
            'Project'        => $report->project?->title ?? '—',
            'Report Title'   => $report->title ?? '—',
            'Version'        => $report->version ?? '—',
            'Status'         => ucfirst($report->status ?? '—'),
            'Date Prepared'  => $report->date_prepared?->format('d M Y') ?? '—',
            'Prepared By'    => $report->author?->name ?? '—',
            'Reviewed By'    => $report->reviewedBy?->name ?? '—',
        ]);

        if ($screening) {
            self::h2($sec, '2. Screening');
            self::fieldTable($sec, [
                'Category'       => $screening->category ?? '—',
                'Scale Score'    => $screening->scale_score ?? '—',
                'Sensitivity'    => $screening->sensitivity_score ?? '—',
                'Pollution Score'=> $screening->pollution_score ?? '—',
                'Total Score'    => $screening->total_score ?? '—',
                'Decision'       => $screening->decision ?? '—',
            ]);
        }

        if ($scopingIssues && $scopingIssues->isNotEmpty()) {
            self::h2($sec, '3. Scoping Issues (' . $scopingIssues->count() . ')');
            $t = self::dataTable($sec, ['#', 'Issue', 'Category', 'In Scope'], [600, 5000, 2000, 1400]);
            foreach ($scopingIssues as $i => $s) {
                $r = $t->addRow();
                self::td($r, (string)($i + 1), 600);
                self::td($r, $s->issue_description ?? '—', 5000);
                self::td($r, $s->category ?? '—', 2000);
                self::td($r, $s->in_scope ? 'Yes' : 'No', 1400);
            }
        }

        if ($impacts && $impacts->isNotEmpty()) {
            self::h2($sec, '4. Impact Assessment (' . $impacts->count() . ' impacts)');
            $t = self::dataTable($sec, ['Activity', 'Receptor', 'Phase', 'Significance'], [2500, 2500, 1500, 2500]);
            foreach ($impacts as $imp) {
                $r = $t->addRow();
                self::td($r, $imp->project_activity ?? '—', 2500);
                self::td($r, $imp->environmental_receptor ?? '—', 2500);
                self::td($r, ucfirst($imp->project_phase ?? '—'), 1500);
                self::td($r, $imp->significance_rating ?? '—', 2500);
            }
        }

        if ($mitigations && $mitigations->isNotEmpty()) {
            self::h2($sec, '5. Mitigation Actions (' . $mitigations->count() . ')');
            $t = self::dataTable($sec, ['Measure', 'Impact', 'Responsibility', 'Timeline'], [3000, 2500, 2000, 1500]);
            foreach ($mitigations as $m) {
                $r = $t->addRow();
                self::td($r, $m->mitigation_measure ?? '—', 3000);
                self::td($r, $m->impact_addressed ?? '—', 2500);
                self::td($r, $m->responsibility ?? '—', 2000);
                self::td($r, ($m->timeline_start?->format('M Y') ?? '—') . ' – ' . ($m->timeline_end?->format('M Y') ?? '—'), 1500);
            }
        }

        self::footer($sec);
        return self::stream($word, "ESIA-{$report->project_id}-Report-v{$report->version}-" . now()->format('Ymd'));
    }

    public static function esgSummary(Collection $targets, Collection $grievances, Collection $policies, Collection $social): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'ESG MANAGEMENT SUMMARY REPORT', 'ESG-' . now()->format('Y-m'));

        // ESG Targets
        self::h2($sec, '1. ESG Targets (' . $targets->count() . ')');
        if ($targets->isNotEmpty()) {
            $t = self::dataTable($sec, ['Category', 'Target', 'Period', 'Baseline', 'Target Val.', 'Current', 'Status'],
                [1200, 2500, 800, 900, 900, 900, 1000]);
            foreach ($targets as $tgt) {
                $r = $t->addRow();
                self::td($r, ucfirst($tgt->category ?? '—'), 1200);
                self::td($r, $tgt->target_description ?? '—', 2500);
                self::td($r, $tgt->period ?? '—', 800);
                self::td($r, (string)($tgt->baseline_value ?? '—'), 900);
                self::td($r, (string)($tgt->target_value ?? '—'), 900);
                self::td($r, (string)($tgt->current_value ?? '—'), 900);
                self::td($r, ucfirst($tgt->status ?? '—'), 1000);
            }
        } else {
            $sec->addText('No targets recorded.', self::font(9, self::GRAY), self::para());
        }

        // Open Grievances
        self::h2($sec, '2. Open Grievances (' . $grievances->count() . ')');
        if ($grievances->isNotEmpty()) {
            $t = self::dataTable($sec, ['#', 'Category', 'Description', 'Status', 'Deadline'], [600, 1500, 4000, 1200, 1200]);
            foreach ($grievances as $i => $g) {
                $r = $t->addRow();
                self::td($r, (string)($i + 1), 600);
                self::td($r, ucfirst($g->category ?? '—'), 1500);
                self::td($r, $g->description ?? '—', 4000);
                self::td($r, ucfirst($g->status ?? '—'), 1200);
                self::td($r, $g->response_deadline?->format('d M Y') ?? '—', 1200);
            }
        } else {
            $sec->addText('No open grievances.', self::font(9, self::GRAY), self::para());
        }

        // Active Governance Policies
        self::h2($sec, '3. Active Governance Policies (' . $policies->count() . ')');
        if ($policies->isNotEmpty()) {
            $t = self::dataTable($sec, ['Policy', 'Category', 'Version', 'Effective', 'Next Review'], [3000, 1500, 800, 1500, 1500]);
            foreach ($policies as $p) {
                $r = $t->addRow();
                self::td($r, $p->policy_name ?? '—', 3000);
                self::td($r, ucfirst($p->category ?? '—'), 1500);
                self::td($r, $p->version ?? '—', 800);
                self::td($r, $p->effective_date?->format('d M Y') ?? '—', 1500);
                self::td($r, $p->review_date?->format('d M Y') ?? '—', 1500);
            }
        }

        self::footer($sec);
        return self::stream($word, 'ESG-Summary-' . now()->format('Y-m-d'));
    }

    public static function hazopStudy(HazopStudy $study, Collection $nodes): Response
    {
        [$word, $sec] = self::doc('landscape');
        self::header($sec, 'HAZOP STUDY REPORT', $study->study_ref);

        self::h2($sec, '1. Study Information');
        self::fieldTable($sec, [
            'Study Reference'  => $study->study_ref,
            'Title'            => $study->title ?? '—',
            'Project'          => $study->project?->title ?? '—',
            'Department'       => $study->department?->name ?? '—',
            'Facility Area'    => $study->facility_area ?? '—',
            'P&ID Reference'   => $study->pid_reference ?? '—',
            'Facilitator'      => $study->facilitator?->name ?? '—',
            'Reviewed By'      => $study->reviewedBy?->name ?? '—',
            'Approved By'      => $study->approvedBy?->name ?? '—',
            'Status'           => ucfirst($study->status ?? '—'),
            'Scope'            => $study->scope ?? '—',
            'Objectives'       => $study->objectives ?? '—',
        ]);

        self::h2($sec, '2. Study Nodes (' . $nodes->count() . ')');
        if ($nodes->isNotEmpty()) {
            // Landscape page width: ~13680 twips (A4 landscape, margins 1080 each)
            $lw = 11520;
            $t = self::dataTable($sec,
                ['Node #', 'Node Name', 'Guide Word', 'Deviation', 'Causes', 'Consequences', 'Safeguards', 'Risk Score', 'Status'],
                [600, 1200, 900, 1200, 1500, 1500, 1500, 700, 700], $lw);
            foreach ($nodes as $n) {
                $r = $t->addRow();
                self::td($r, $n->node_number ?? '—', 600);
                self::td($r, $n->node_name ?? '—', 1200);
                self::td($r, $n->guide_word ?? '—', 900);
                self::td($r, $n->deviation ?? '—', 1200);
                self::td($r, $n->causes ?? '—', 1500);
                self::td($r, $n->consequences ?? '—', 1500);
                self::td($r, $n->safeguards ?? '—', 1500);
                self::td($r, (string)($n->risk_score ?? '—'), 700);
                self::td($r, ucfirst($n->status ?? '—'), 700);
            }
        }

        self::footer($sec);
        return self::stream($word, "{$study->study_ref}-Report-" . now()->format('Ymd'));
    }

    public static function ptwPermit(PermitToWork $permit, $checklistItems, $approvals): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'PERMIT TO WORK CERTIFICATE', $permit->permit_number);

        self::h2($sec, '1. Permit Details');
        self::fieldTable($sec, [
            'Permit Number'    => $permit->permit_number,
            'Permit Type'      => ucwords(str_replace('_', ' ', $permit->permit_type ?? '—')),
            'Status'           => ucwords(str_replace('_', ' ', $permit->status ?? '—')),
            'Project'          => $permit->project?->title ?? '—',
            'Department'       => $permit->department?->name ?? '—',
            'Location'         => $permit->location ?? '—',
            'Site Area'        => $permit->site_area ?? '—',
            'Valid From'       => $permit->valid_from?->format('d M Y H:i') ?? '—',
            'Valid To'         => $permit->valid_to?->format('d M Y H:i') ?? '—',
        ]);

        self::h2($sec, '2. Personnel');
        self::fieldTable($sec, [
            'Requested By'     => $permit->requestedBy?->name ?? '—',
            'Issued By'        => $permit->issuedBy?->name ?? '—',
            'Area Authority'   => $permit->areaAuthority?->name ?? '—',
            'Supervisor'       => $permit->supervisor?->name ?? '—',
            'Contractor Name'  => $permit->contractor_name ?? '—',
            'No. of Workers'   => (string)($permit->number_of_workers ?? '—'),
        ]);

        self::h2($sec, '3. Work Description & Hazards');
        self::fieldTable($sec, [
            'Work Description'     => $permit->work_description ?? '—',
            'Hazards Identified'   => $permit->hazards_identified ?? '—',
            'Precautions Required' => $permit->precautions ?? '—',
            'PPE Required'         => $permit->ppe_required ?? '—',
            'Emergency Procedures' => $permit->emergency_procedures ?? '—',
        ]);

        self::h2($sec, '4. Risk Assessment');
        self::fieldTable($sec, [
            'Likelihood'       => ($permit->likelihood ?? '—') . ' / 5',
            'Severity'         => ($permit->severity ?? '—') . ' / 5',
            'Risk Score'       => $permit->risk_score ?? '—',
            'Risk Level'       => ucfirst($permit->risk_classification ?? '—'),
        ]);

        if ($checklistItems && $checklistItems->isNotEmpty()) {
            self::h2($sec, '5. Safety Checklist');
            $t = self::dataTable($sec, ['#', 'Checklist Item', 'Response', 'Notes'], [600, 5000, 1500, 2300]);
            foreach ($checklistItems as $i => $item) {
                $r = $t->addRow();
                self::td($r, (string)($i + 1), 600);
                self::td($r, $item->item_description ?? '—', 5000);
                self::td($r, ucfirst($item->response ?? '—'), 1500);
                self::td($r, $item->notes ?? '—', 2300);
            }
        }

        if ($approvals && $approvals->isNotEmpty()) {
            self::h2($sec, '6. Approval Chain');
            $t = self::dataTable($sec, ['Approver', 'Role', 'Decision', 'Date', 'Comments'], [2000, 2000, 1200, 1500, 2660]);
            foreach ($approvals as $ap) {
                $r = $t->addRow();
                self::td($r, $ap->approver?->name ?? '—', 2000);
                self::td($r, $ap->role ?? '—', 2000);
                self::td($r, ucfirst($ap->decision ?? '—'), 1200);
                self::td($r, $ap->decided_at?->format('d M Y H:i') ?? '—', 1500);
                self::td($r, $ap->comments ?? '—', 2660);
            }
        }

        if ($permit->closeout_notes) {
            self::h2($sec, '7. Closeout');
            self::fieldTable($sec, [
                'Closeout Notes'   => $permit->closeout_notes,
                'Closed By'        => $permit->closeoutBy?->name ?? '—',
                'Completion Date'  => $permit->completion_date?->format('d M Y') ?? '—',
            ]);
        }

        self::footer($sec);
        return self::stream($word, "{$permit->permit_number}-Certificate-" . now()->format('Ymd'));
    }

    public static function amsAuditReport(InternalAudit $audit): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'AMS AUDIT REPORT', $audit->audit_reference);

        self::h2($sec, '1. Audit Information');
        self::fieldTable($sec, [
            'Audit Reference'   => $audit->audit_reference,
            'Standard'          => (\App\Models\InternalAudit::STANDARD_LABELS[$audit->standard] ?? $audit->standard ?? '—') . ($audit->standard_other ? ' (' . $audit->standard_other . ')' : ''),
            'Audit Type'        => \App\Models\InternalAudit::AUDIT_TYPE_LABELS[$audit->audit_type] ?? $audit->audit_type ?? '—',
            'Audit Date'        => $audit->audit_date?->format('d M Y') ?? '—',
            'Lead Auditor'      => $audit->leadAuditor?->name ?? '—',
            'Project / Area'    => $audit->project?->title ?? $audit->department?->name ?? 'Company-wide',
            'Compliance Score'  => ($audit->compliance_score ?? '—') . ($audit->compliance_score ? '%' : ''),
            'Status'            => \App\Models\InternalAudit::STATUS_LABELS[$audit->status] ?? $audit->status ?? '—',
            'Scope'             => $audit->scope ?? '—',
        ]);

        if ($audit->checklistItems && $audit->checklistItems->isNotEmpty()) {
            self::h2($sec, '2. ISO Checklist Results');
            $t = self::dataTable($sec, ['#', 'ISO Clause', 'Requirement', 'Response', 'Score', 'Evidence'], [500, 1200, 2500, 1200, 600, 2800]);
            foreach ($audit->checklistItems as $i => $item) {
                $r = $t->addRow();
                self::td($r, (string)($i + 1), 500);
                self::td($r, $item->clause_reference ?? '—', 1200);
                self::td($r, $item->requirement ?? '—', 2500);
                self::td($r, ucfirst(str_replace('_', ' ', $item->response ?? '—')), 1200);
                self::td($r, (string)($item->score ?? '—'), 600);
                self::td($r, $item->evidence_notes ?? '—', 2800);
            }
        }

        if ($audit->nonConformities && $audit->nonConformities->isNotEmpty()) {
            self::h2($sec, '3. Non-Conformities (' . $audit->nonConformities->count() . ')');
            $t = self::dataTable($sec, ['NC #', 'Type', 'ISO Clause', 'Description', 'Risk', 'Assigned To', 'Due Date'],
                [700, 1200, 1000, 2600, 800, 1500, 1000]);
            foreach ($audit->nonConformities as $nc) {
                $r = $t->addRow();
                self::td($r, $nc->nc_number ?? '—', 700);
                self::td($r, ucfirst(str_replace('_', ' ', $nc->nc_type ?? '—')), 1200);
                self::td($r, $nc->iso_clause_reference ?? '—', 1000);
                self::td($r, $nc->description ?? '—', 2600);
                self::td($r, ucfirst($nc->risk_level ?? '—'), 800);
                self::td($r, $nc->assignedTo?->name ?? '—', 1500);
                self::td($r, $nc->due_date?->format('d M Y') ?? '—', 1000);
            }
        }

        if ($audit->amsCapaActions && $audit->amsCapaActions->isNotEmpty()) {
            self::h2($sec, '4. CAPA Actions (' . $audit->amsCapaActions->count() . ')');
            $t = self::dataTable($sec, ['CAPA #', 'Action', 'Addresses NC', 'Responsible', 'Target Date', 'Status'],
                [800, 3000, 1000, 1500, 1200, 1200]);
            foreach ($audit->amsCapaActions as $ca) {
                $r = $t->addRow();
                self::td($r, $ca->capa_number ?? '—', 800);
                self::td($r, $ca->action_description ?? '—', 3000);
                self::td($r, $ca->nc?->nc_number ?? '—', 1000);
                self::td($r, $ca->responsiblePerson?->name ?? '—', 1500);
                self::td($r, $ca->target_date?->format('d M Y') ?? '—', 1200);
                self::td($r, ucfirst(str_replace('_', ' ', $ca->status ?? '—')), 1200);
            }
        }

        self::footer($sec);
        return self::stream($word, $audit->audit_reference . '-AMS-Report-' . now()->format('Ymd'));
    }

    public static function environmentalAudit(EnvironmentalAudit $audit): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'ENVIRONMENTAL AUDIT REPORT', $audit->audit_number);

        self::h2($sec, '1. Audit Information');
        self::fieldTable($sec, [
            'Audit Number'      => $audit->audit_number,
            'Audit Title'       => $audit->audit_title ?? '—',
            'Audit Type'        => ucwords(str_replace('_', ' ', $audit->audit_type ?? '—')),
            'Audit Method'      => ucwords(str_replace('_', ' ', $audit->audit_method ?? '—')),
            'Status'            => ucwords(str_replace('_', ' ', $audit->status ?? '—')),
            'Project'           => $audit->project?->title ?? '—',
            'Department'        => $audit->department?->name ?? '—',
            'Team Leader'       => $audit->teamLeader?->name ?? '—',
            'Lead Auditor'      => $audit->leadAuditor?->name ?? '—',
            'Approved By'       => $audit->approvedBy?->name ?? '—',
            'Compliance Score'  => ($audit->compliance_score ?? '—') . ($audit->compliance_score ? '%' : ''),
            'Scope'             => $audit->scope ?? '—',
            'Objectives'        => $audit->objectives ?? '—',
        ]);

        if ($audit->checklistItems && $audit->checklistItems->isNotEmpty()) {
            self::h2($sec, '2. Audit Checklist (' . $audit->checklistItems->count() . ' items)');
            $t = self::dataTable($sec, ['#', 'Category', 'Requirement', 'Response', 'Evidence'], [500, 1200, 3000, 1200, 2900]);
            foreach ($audit->checklistItems as $i => $item) {
                $r = $t->addRow();
                self::td($r, (string)($i + 1), 500);
                self::td($r, $item->category ?? '—', 1200);
                self::td($r, $item->requirement ?? '—', 3000);
                self::td($r, ucfirst(str_replace('_', ' ', $item->response ?? '—')), 1200);
                self::td($r, $item->evidence_notes ?? '—', 2900);
            }
        }

        if ($audit->findings && $audit->findings->isNotEmpty()) {
            self::h2($sec, '3. Findings (' . $audit->findings->count() . ')');
            $t = self::dataTable($sec, ['#', 'Type', 'Finding', 'ISO Clause', 'Due Date', 'Status'], [500, 1500, 3500, 1200, 1200, 1000]);
            foreach ($audit->findings as $i => $f) {
                $r = $t->addRow();
                self::td($r, (string)($i + 1), 500);
                self::td($r, ucfirst(str_replace('_', ' ', $f->finding_type ?? '—')), 1500);
                self::td($r, $f->finding_description ?? $f->description ?? '—', 3500);
                self::td($r, $f->iso_clause ?? '—', 1200);
                self::td($r, $f->due_date?->format('d M Y') ?? '—', 1200);
                self::td($r, ucfirst($f->status ?? '—'), 1000);
            }
        }

        self::footer($sec);
        return self::stream($word, $audit->audit_number . '-Environmental-Audit-Report-' . now()->format('Ymd'));
    }

    public static function invoicePdf(Invoice $invoice, array $company, array $bank): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'INVOICE', $invoice->invoice_number);

        // Company & client
        self::h2($sec, '1. Parties');
        $t = $sec->addTable(['borderSize' => 6, 'borderColor' => 'e5e7eb', 'cellMargin' => 100, 'width' => self::PAGE_W, 'unit' => TblWidth::TWIP]);
        $r = $t->addRow();
        $cl = $t->addCell(4680, ['bgColor' => 'f8faff']);
        $cl->addText('BILL FROM', ['bold' => true, 'size' => 8, 'color' => self::GRAY]);
        $cl->addText($company['name'], ['bold' => true, 'size' => 11, 'color' => self::NAVY]);
        if ($company['tagline']) {
            $cl->addText($company['tagline'], ['size' => 9, 'color' => self::GRAY]);
        }
        if ($company['address']) {
            $cl->addText($company['address'], ['size' => 9]);
        }
        if ($company['tin']) {
            $cl->addText('TIN: ' . $company['tin'], ['size' => 9]);
        }
        if ($company['phone']) {
            $cl->addText('Tel: ' . $company['phone'], ['size' => 9]);
        }
        if ($company['email']) {
            $cl->addText('Email: ' . $company['email'], ['size' => 9]);
        }

        $cr = $t->addCell(4680);
        $cr->addText('BILL TO', ['bold' => true, 'size' => 8, 'color' => self::GRAY]);
        $cr->addText($invoice->client?->company_name ?? '—', ['bold' => true, 'size' => 11, 'color' => self::NAVY]);
        if ($invoice->client?->contact_person) {
            $cr->addText('Attn: ' . $invoice->client->contact_person, ['size' => 9]);
        }
        if ($invoice->client?->address) {
            $cr->addText($invoice->client->address, ['size' => 9]);
        }
        if ($invoice->client?->tin_number) {
            $cr->addText('TIN: ' . $invoice->client->tin_number, ['size' => 9]);
        }
        if ($invoice->client?->email) {
            $cr->addText('Email: ' . $invoice->client->email, ['size' => 9]);
        }
        if ($invoice->client?->phone) {
            $cr->addText('Tel: ' . $invoice->client->phone, ['size' => 9]);
        }

        self::h2($sec, '2. Invoice Details');
        self::fieldTable($sec, [
            'Invoice Number' => $invoice->invoice_number,
            'Invoice Date'   => $invoice->invoice_date?->format('d M Y') ?? '—',
            'Due Date'       => $invoice->due_date?->format('d M Y') ?? '—',
            'Project'        => $invoice->project?->title ?? '—',
            'Status'         => strtoupper(str_replace('_', ' ', $invoice->status ?? '—')),
            'Prepared By'    => $invoice->createdBy?->name ?? '—',
        ]);

        // Line items
        self::h2($sec, '3. Line Items');
        $t = self::dataTable($sec, ['#', 'Description', 'Qty', 'Unit Price (TZS)', 'Subtotal (TZS)'], [600, 5000, 800, 1400, 1400]);
        foreach ($invoice->items as $i => $item) {
            $r = $t->addRow();
            self::td($r, (string)($i + 1), 600);
            self::td($r, $item->description ?? '—', 5000);
            self::td($r, (string)($item->quantity ?? 0), 800);
            self::td($r, number_format((float)($item->unit_price ?? 0), 2), 1400, true);
            self::td($r, number_format((float)($item->subtotal ?? 0), 2), 1400, true);
        }

        // Totals
        self::h2($sec, '4. Totals');
        self::fieldTable($sec, [
            'Subtotal (before VAT)' => 'TZS ' . number_format((float)($invoice->subtotal ?? 0), 2),
            'VAT (18%)'             => 'TZS ' . number_format((float)($invoice->tax_amount ?? 0), 2),
            'TOTAL'                 => 'TZS ' . number_format((float)($invoice->total_amount ?? 0), 2),
            'Amount Paid'           => 'TZS ' . number_format((float)($invoice->amount_paid ?? 0), 2),
            'Balance Due'           => 'TZS ' . number_format((float)($invoice->balance ?? 0), 2),
        ]);

        if ($invoice->notes) {
            self::h2($sec, '5. Notes / Payment Terms');
            $sec->addText($invoice->notes, self::font(9), self::para());
        }

        if (! empty($bank) && ! empty($bank['account_number'])) {
            self::h2($sec, '6. Payment Instructions');
            self::fieldTable($sec, [
                'Bank Name'      => $bank['name'] ?? '—',
                'Branch'         => $bank['branch'] ?? '—',
                'Account Name'   => $bank['account_name'] ?? '—',
                'Account Number' => $bank['account_number'] ?? '—',
                'SWIFT / BIC'    => $bank['swift'] ?? '—',
            ]);
        }

        self::footer($sec);
        return self::stream($word, "{$invoice->invoice_number}-" . now()->format('Ymd'));
    }

    public static function maturityScorecard(MaturityAssessment $assessment, array $breakdown, array $indicatorDetail, array $trend, string $levelDescription): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'HSE MATURITY SCORECARD', 'Assessment Period: ' . $assessment->period);

        self::h2($sec, '1. Assessment Summary');
        self::fieldTable($sec, [
            'Period'            => $assessment->period ?? '—',
            'Project'           => $assessment->project?->title ?? 'Company-wide',
            'Assessed By'       => $assessment->assessedBy?->name ?? '—',
            'Assessment Date'   => $assessment->assessed_at?->format('d M Y') ?? '—',
            'Overall Score'     => number_format((float)($assessment->overall_score ?? 0), 2) . ' / 5',
            'Maturity Level'    => ucfirst($assessment->maturity_level ?? '—'),
            'Description'       => $levelDescription,
            'Status'            => ucfirst($assessment->status ?? '—'),
        ]);

        if (! empty($breakdown)) {
            self::h2($sec, '2. Dimension Scores');
            $t = self::dataTable($sec, ['Dimension', 'Score', 'Max', 'Level'], [4000, 1500, 1500, 2500]);
            foreach ($breakdown as $dim) {
                $r = $t->addRow();
                self::td($r, $dim['name'] ?? '—', 4000);
                self::td($r, number_format((float)($dim['score'] ?? 0), 2), 1500, true);
                self::td($r, '5.00', 1500, true);
                self::td($r, ucfirst($dim['level'] ?? '—'), 2500);
            }
        }

        if (! empty($trend)) {
            self::h2($sec, '3. Historical Trend');
            $t = self::dataTable($sec, ['Period', 'Score', 'Level'], [4000, 2500, 2500]);
            foreach ($trend as $tp) {
                $r = $t->addRow();
                self::td($r, $tp['period'] ?? '—', 4000);
                self::td($r, number_format((float)($tp['score'] ?? 0), 2), 2500, true);
                self::td($r, ucfirst($tp['level'] ?? '—'), 2500);
            }
        }

        self::footer($sec);
        return self::stream($word, 'HSE-Maturity-Scorecard-' . $assessment->period . '-' . now()->format('Ymd'));
    }

    public static function hazopProcedure(): Response
    {
        [$word, $sec] = self::doc();
        self::header($sec, 'HAZOP PROCEDURE', 'PRO-HSE-HAZOP-001');

        $company = Setting::companyName();
        self::h2($sec, '1. Purpose');
        $sec->addText('This procedure establishes the methodology for conducting Hazard and Operability (HAZOP) Studies at ' . $company . ' in accordance with IEC 61882 and BS EN 61882 standards.', self::font(10), self::para());

        self::h2($sec, '2. Scope');
        $sec->addText('Applies to all process operations, modifications, and new installations where process hazards may exist, including: pipelines, pressure vessels, process equipment, utility systems, and storage facilities.', self::font(10), self::para());

        self::h2($sec, '3. Guide Words');
        self::fieldTable($sec, [
            'No / None'  => 'Complete negation of design intent. No part of the intention is achieved.',
            'Less'       => 'Quantitative decrease of a parameter.',
            'More'       => 'Quantitative increase of a parameter.',
            'Part Of'    => 'Only part of the design intent is achieved.',
            'Reverse'    => 'Logical opposite of the intent occurs.',
            'Other Than' => 'Complete substitution. Something other than the design intent occurs.',
        ]);

        self::h2($sec, '4. HAZOP Process Steps');
        $steps = ['Define scope and boundaries of study', 'Identify nodes (sections of system)', 'For each node, apply guide words to parameters', 'Identify deviations and causes', 'Assess consequences and existing safeguards', 'Rate risk and recommend actions', 'Document findings in HAZOP register', 'Review and approve study', 'Close out all action items'];
        foreach ($steps as $i => $step) {
            $sec->addListItem($step, 0, self::font(10));
        }

        self::footer($sec);
        return self::stream($word, 'NOVAREX-HAZOP-Procedure-PRO-HSE-HAZOP-001-' . now()->format('Ymd'));
    }

    // =================================================================
    // EMS SECTION & FULL REPORT
    // =================================================================

    public static function emsFullReport(
        Collection $aspects,
        Collection $legalItems,
        Collection $permits,
        Collection $monitoringRecords,
        Collection $wasteRecords,
        Collection $spillReports,
        Collection $ciActions
    ): Response {
        [$word, $sec] = self::doc('landscape');
        self::header($sec, 'ENVIRONMENTAL MANAGEMENT SYSTEM — FULL REPORT', 'EMS-FULL-' . now()->format('Y-m-d'));

        // Summary overview table
        self::h2($sec, 'Overview');
        $lw = 11520;
        $t = self::dataTable($sec,
            ['Aspects & Impacts', 'Legal Items', 'Active Permits', 'Monitoring Records', 'Waste Records', 'Spill Reports', 'CI Actions'],
            [1646, 1646, 1646, 1646, 1646, 1646, 1644], $lw);
        $r = $t->addRow();
        foreach ([
            $aspects->count(),
            $legalItems->count(),
            $permits->where('status', 'active')->count() . ' / ' . $permits->count(),
            $monitoringRecords->count(),
            $wasteRecords->count(),
            $spillReports->count(),
            $ciActions->count(),
        ] as $val) {
            self::td($r, (string)$val, 1646, true);
        }

        // 1. Aspects
        self::h2($sec, '1. Environmental Aspects & Impacts (' . $aspects->count() . ')');
        if ($aspects->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Aspect', 'Impact', 'Category', 'Significance', 'Score', 'Project'],
                [2400, 2400, 1500, 1400, 800, 3020], $lw);
            foreach ($aspects as $a) {
                $r = $t->addRow();
                self::td($r, $a->environmental_aspect ?? '—', 2400);
                self::td($r, $a->environmental_impact ?? '—', 2400);
                self::td($r, ucwords(str_replace('_', ' ', $a->impact_category ?? '—')), 1500);
                self::td($r, ucfirst($a->significance_level ?? '—'), 1400);
                self::td($r, (string)($a->significance_score ?? '—'), 800, true);
                self::td($r, $a->project?->title ?? 'Company-wide', 3020);
            }
        } else {
            $sec->addText('No aspects recorded.', self::font(9, self::GRAY), self::para());
        }

        // 2. Legal Register
        self::h2($sec, '2. Legal & Compliance Register (' . $legalItems->count() . ')');
        if ($legalItems->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Requirement', 'Type', 'Authority', 'Compliance Status', 'Expiry', 'Review Due'],
                [3200, 1400, 1800, 1700, 1200, 1220], $lw);
            foreach ($legalItems as $l) {
                $r = $t->addRow();
                self::td($r, $l->requirement_title ?? '—', 3200);
                self::td($r, ucwords(str_replace('_', ' ', $l->requirement_type ?? '—')), 1400);
                self::td($r, $l->issuing_authority ?? '—', 1800);
                self::td($r, ucwords(str_replace('_', ' ', $l->compliance_status ?? '—')), 1700);
                self::td($r, $l->expiry_date?->format('d M Y') ?? '—', 1200);
                self::td($r, $l->next_review_date?->format('d M Y') ?? '—', 1220);
            }
        } else {
            $sec->addText('No legal requirements recorded.', self::font(9, self::GRAY), self::para());
        }

        // 3. Permits
        self::h2($sec, '3. Environmental Permits & Licences (' . $permits->count() . ')');
        if ($permits->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Permit No.', 'Type', 'Authority', 'Issue Date', 'Expiry', 'Status', 'Project'],
                [1200, 1800, 2000, 1100, 1100, 1100, 2220], $lw);
            foreach ($permits as $p) {
                $r = $t->addRow();
                self::td($r, $p->permit_number, 1200);
                self::td($r, ucwords(str_replace('_', ' ', $p->permit_type ?? '—')), 1800);
                self::td($r, $p->issuing_authority ?? '—', 2000);
                self::td($r, $p->issue_date?->format('d M Y') ?? '—', 1100);
                self::td($r, $p->expiry_date?->format('d M Y') ?? 'Indefinite', 1100);
                self::td($r, ucwords(str_replace('_', ' ', $p->status)), 1100);
                self::td($r, $p->project?->title ?? '—', 2220);
            }
        } else {
            $sec->addText('No permits recorded.', self::font(9, self::GRAY), self::para());
        }

        // 4. Monitoring
        self::h2($sec, '4. Environmental Monitoring Records (' . $monitoringRecords->count() . ')');
        if ($monitoringRecords->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Date', 'Metric Type', 'Value', 'Status', 'Project', 'Recorded By'],
                [1200, 2800, 1200, 1500, 2520, 2300], $lw);
            foreach ($monitoringRecords as $m) {
                $r = $t->addRow();
                self::td($r, $m->record_date?->format('d M Y') ?? '—', 1200);
                self::td($r, EnvironmentalMonitoringRecord::METRIC_TYPE_LABELS[$m->metric_type] ?? ucwords(str_replace('_', ' ', $m->metric_type)), 2800);
                self::td($r, ($m->value ?? '—') . ' ' . ($m->unit ?? ''), 1200, true);
                self::td($r, ucwords(str_replace('_', ' ', $m->status ?? '—')), 1500);
                self::td($r, $m->project?->title ?? 'Company-wide', 2520);
                self::td($r, $m->recordedBy?->name ?? '—', 2300);
            }
        } else {
            $sec->addText('No monitoring records.', self::font(9, self::GRAY), self::para());
        }

        // 5. Waste
        self::h2($sec, '5. Waste Tracking Records (' . $wasteRecords->count() . ')');
        if ($wasteRecords->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Type', 'Description', 'Quantity', 'Disposal Method', 'Date', 'Status', 'Project'],
                [1300, 2800, 900, 1700, 1100, 1000, 1720], $lw);
            foreach ($wasteRecords as $w) {
                $r = $t->addRow();
                self::td($r, ucwords(str_replace('_', ' ', $w->waste_type)), 1300);
                self::td($r, $w->waste_description ?? '—', 2800);
                self::td($r, ($w->quantity ?? '—') . ' ' . ($w->unit ?? ''), 900, true);
                self::td($r, ucwords(str_replace('_', ' ', $w->disposal_method ?? '—')), 1700);
                self::td($r, $w->generation_date?->format('d M Y') ?? '—', 1100);
                self::td($r, ucwords(str_replace('_', ' ', $w->status)), 1000);
                self::td($r, $w->project?->title ?? '—', 1720);
            }
        } else {
            $sec->addText('No waste records.', self::font(9, self::GRAY), self::para());
        }

        // 6. Spills
        self::h2($sec, '6. Chemical & Oil Spill Reports (' . $spillReports->count() . ')');
        if ($spillReports->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Reference', 'Date', 'Substance', 'Volume', 'Media Affected', 'Status'],
                [1400, 1100, 2200, 1000, 2000, 900], $lw);
            foreach ($spillReports as $s) {
                $r = $t->addRow();
                self::td($r, $s->spill_reference ?? '—', 1400);
                self::td($r, $s->spill_date?->format('d M Y') ?? '—', 1100);
                self::td($r, ($s->substance_spilled ?? '—') . ' (' . ucfirst($s->substance_type ?? '—') . ')', 2200);
                self::td($r, $s->estimated_volume ? $s->estimated_volume . ' ' . $s->volume_unit : '—', 1000, true);
                self::td($r, ucwords(str_replace('_', ' ', $s->environmental_media_affected ?? '—')), 2000);
                self::td($r, ucwords(str_replace('_', ' ', $s->status)), 900);
            }
        } else {
            $sec->addText('No spill reports recorded.', self::font(9, self::GRAY), self::para());
        }

        // 7. CI Actions
        self::h2($sec, '7. Continual Improvement Actions (' . $ciActions->count() . ')');
        if ($ciActions->isNotEmpty()) {
            $t = self::dataTable($sec,
                ['Reference', 'Title', 'PDCA', 'Priority', 'Assigned To', 'Target Date', 'Status'],
                [1000, 3200, 700, 800, 1500, 1100, 1220], $lw);
            foreach ($ciActions as $c) {
                $r = $t->addRow();
                self::td($r, $c->reference ?? '—', 1000);
                self::td($r, $c->title ?? '—', 3200);
                self::td($r, strtoupper($c->pdca_phase ?? '—'), 700, true);
                self::td($r, ucfirst($c->priority ?? '—'), 800);
                self::td($r, $c->assignedTo?->name ?? '—', 1500);
                self::td($r, $c->target_date?->format('d M Y') ?? '—', 1100);
                self::td($r, EmsImprovementAction::STATUS_LABELS[$c->status] ?? ucfirst($c->status ?? '—'), 1220);
            }
        } else {
            $sec->addText('No CI actions recorded.', self::font(9, self::GRAY), self::para());
        }

        self::footer($sec);
        return self::stream($word, 'EMS-Full-Report-' . now()->format('Ymd'));
    }

    // =================================================================
    // PRIVATE HELPERS
    // =================================================================

    private static function doc(string $orientation = 'portrait'): array
    {
        $word = new PhpWord();
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(10);

        $section = $word->addSection([
            'orientation'  => $orientation,
            'marginLeft'   => 1080,
            'marginRight'  => 1080,
            'marginTop'    => 1080,
            'marginBottom' => 1080,
            'pageSizeW'    => $orientation === 'landscape' ? 15840 : 12240,
            'pageSizeH'    => $orientation === 'landscape' ? 12240 : 15840,
        ]);

        return [$word, $section];
    }

    private static function header($section, string $title, string $ref): void
    {
        $section->addText(
            Setting::companyName(),
            ['bold' => true, 'size' => 9, 'color' => self::GRAY],
            ['spaceAfter' => 0]
        );
        $section->addText(
            $title,
            ['bold' => true, 'size' => 18, 'color' => self::NAVY],
            ['spaceAfter' => 20]
        );
        $section->addText(
            $ref . '   |   Generated: ' . now()->format('d M Y H:i'),
            ['size' => 9, 'color' => self::GRAY],
            ['spaceAfter' => 200]
        );
        $section->addLine(['weight' => 1, 'color' => self::BLUE, 'width' => self::PAGE_W]);
        $section->addTextBreak(1);
    }

    private static function h2($section, string $text): void
    {
        $section->addText(
            $text,
            ['bold' => true, 'size' => 12, 'color' => self::NAVY],
            ['spaceBefore' => 160, 'spaceAfter' => 80]
        );
    }

    private static function fieldTable($section, array $fields): void
    {
        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => 'e5e7eb',
            'cellMargin'  => 80,
            'width'       => self::PAGE_W,
            'unit'        => TblWidth::TWIP,
        ]);

        foreach ($fields as $label => $value) {
            $row = $table->addRow();

            $lCell = $table->addCell(3000, ['bgColor' => 'f8faff', 'valign' => 'top']);
            $lCell->addText((string)$label, ['bold' => true, 'size' => 9, 'color' => self::NAVY]);

            $vCell = $table->addCell(self::PAGE_W - 3000, ['valign' => 'top']);
            $vCell->addText((string)($value ?? '—'), ['size' => 9, 'color' => self::BLACK]);
        }

        $section->addTextBreak(1);
    }

    private static function dataTable($section, array $headers, array $widths, ?int $totalWidth = null): \PhpOffice\PhpWord\Element\Table
    {
        $table = $section->addTable([
            'borderSize'   => 6,
            'borderColor'  => 'e5e7eb',
            'cellMargin'   => 60,
            'width'        => $totalWidth ?? self::PAGE_W,
            'unit'         => TblWidth::TWIP,
        ]);

        $hRow = $table->addRow();
        foreach ($headers as $i => $h) {
            $cell = $table->addCell($widths[$i] ?? 1200, ['bgColor' => self::NAVY, 'valign' => 'center']);
            $cell->addText((string)$h, ['bold' => true, 'size' => 8, 'color' => self::WHITE]);
        }

        return $table;
    }

    private static function td($table, string $text, int $width, bool $right = false): void
    {
        $cell = $table->addCell($width, ['valign' => 'top']);
        $paraStyle = $right ? ['alignment' => 'right'] : [];
        $cell->addText($text, ['size' => 8, 'color' => self::BLACK], $paraStyle);
    }

    private static function font(int $size = 10, string $color = self::BLACK): array
    {
        return ['size' => $size, 'color' => $color];
    }

    private static function para(int $after = 80): array
    {
        return ['spaceAfter' => $after];
    }

    private static function footer($section): void
    {
        $section->addTextBreak(1);
        $section->addLine(['weight' => 1, 'color' => 'e5e7eb', 'width' => self::PAGE_W]);
        $section->addText(
            Setting::companyName() . '   |   PortalHSE   |   ' . now()->format('d M Y H:i'),
            ['size' => 7, 'color' => self::GRAY],
            ['spaceAfter' => 0]
        );
    }

    private static function stream(PhpWord $word, string $filename): Response
    {
        $tmp = tempnam(sys_get_temp_dir(), 'phpdocx_');
        IOFactory::createWriter($word, 'Word2007')->save($tmp);
        $content = file_get_contents($tmp);
        @unlink($tmp);

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.docx"',
            'Content-Length'      => strlen($content),
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
