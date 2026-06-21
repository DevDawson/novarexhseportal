<?php

namespace App\Http\Controllers;

use App\Models\EmsImprovementAction;
use App\Models\EnvironmentalAspect;
use App\Models\EnvironmentalMonitoringRecord;
use App\Models\EnvironmentalPermit;
use App\Models\EsgTarget;
use App\Models\Grievance;
use App\Models\GovernancePolicy;
use App\Models\LegalRegisterItem;
use App\Models\SpillReport;
use App\Models\WasteTrackingRecord;
use App\Models\HazardRegister;
use App\Models\HazopStudy;
use App\Models\Incident;
use App\Models\EnvironmentalAudit;
use App\Models\Invoice;
use App\Models\PermitToWork;
use App\Models\InternalAudit;
use App\Models\MaturityAssessment;
use App\Models\MaturityDimension;
use App\Models\Setting;
use App\Models\SocialIndicator;
use App\Services\DocxBuilderService;
use App\Services\MaturityScoringService;
use App\Services\RiskScoringService;
use App\Models\EsiaBaselineData;
use App\Models\EsiaImpactAssessment;
use App\Models\EsiaMitigationAction;
use App\Models\EsiaReport;
use App\Models\EsiaRegulatorySubmission;
use App\Models\EsiaScreening;
use App\Models\EsiaScopingIssue;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PdfExportController extends Controller
{
    // ----------------------------------------------------------------
    // HIRA - Hazard Risk Assessment Sheet
    // ----------------------------------------------------------------

    public function hira(HazardRegister $hazard): Response
    {
        abort_unless(auth()->user()?->can('manage hazards'), 403);

        $hazard->load('project', 'responsiblePerson');

        $pdf = Pdf::loadView('pdf.hira', [
            'hazard'   => $hazard,
            'initLevel'    => RiskScoringService::level((int) $hazard->initial_risk_score),
            'residLevel'   => RiskScoringService::level((int) $hazard->residual_risk_score),
        ])->setPaper('a4');

        return $pdf->download("HIRA-{$hazard->id}-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // Internal Audit Report
    // ----------------------------------------------------------------

    public function auditReport(InternalAudit $audit): Response
    {
        abort_unless(auth()->user()?->can('manage audits'), 403);

        $audit->load('leadAuditor', 'teamMembers', 'findings.responsiblePerson', 'project', 'department');

        $pdf = Pdf::loadView('pdf.audit-report', [
            'audit' => $audit,
        ])->setPaper('a4');

        return $pdf->download("{$audit->audit_reference}-Report-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // Incident Report
    // ----------------------------------------------------------------

    public function incidentReport(Incident $incident): Response
    {
        abort_unless(auth()->user()?->can('manage incidents'), 403);

        $incident->load('project', 'reportedBy');

        $pdf = Pdf::loadView('pdf.incident-report', [
            'incident' => $incident,
            'riskLevel' => RiskScoringService::level((int) $incident->risk_score),
        ])->setPaper('a4');

        return $pdf->download("Incident-{$incident->id}-Report-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // EMS Environmental Aspect Assessment
    // ----------------------------------------------------------------

    public function environmentalAspect(EnvironmentalAspect $aspect): Response
    {
        abort_unless(auth()->user()?->can('manage environmental_aspects'), 403);

        $aspect->load('project', 'responsiblePerson');

        $pdf = Pdf::loadView('pdf.environmental-aspect', [
            'aspect'    => $aspect,
            'sigLevel'  => RiskScoringService::level((int) $aspect->significance_score),
        ])->setPaper('a4');

        return $pdf->download("EMS-Aspect-{$aspect->id}-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // ESIA Report (project-level comprehensive export)
    // ----------------------------------------------------------------

    public function esiaReport(EsiaReport $report): Response
    {
        abort_unless(auth()->user()?->can('manage esia_audits'), 403);

        $report->load('project', 'author', 'reviewedBy');

        $projectId = $report->project_id;

        $screening    = EsiaScreening::where('project_id', $projectId)->latest()->first();
        $scopingIssues = EsiaScopingIssue::where('project_id', $projectId)->orderBy('sort_order')->get();
        $baselineData  = EsiaBaselineData::where('project_id', $projectId)->orderBy('parameter_type')->get();
        $impacts       = EsiaImpactAssessment::where('project_id', $projectId)->get();
        $mitigations   = EsiaMitigationAction::where('project_id', $projectId)->orderBy('timeline_start')->get();
        $submissions   = EsiaRegulatorySubmission::where('project_id', $projectId)
            ->orderBy('submitted_at')->get();

        $pdf = Pdf::loadView('pdf.esia-report', compact(
            'report', 'screening', 'scopingIssues', 'baselineData',
            'impacts', 'mitigations', 'submissions'
        ))->setPaper('a4');

        return $pdf->download("ESIA-{$report->project_id}-Report-v{$report->version}-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // ESG Summary Report (aggregate — no specific record)
    // ----------------------------------------------------------------

    public function esgSummary(): Response
    {
        abort_unless(auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']), 403);

        $targets    = EsgTarget::with('owner')->orderBy('category')->orderBy('period')->get();
        $grievances = Grievance::whereNotIn('status', ['closed', 'resolved'])->get();
        $policies   = GovernancePolicy::where('status', 'active')->orderBy('review_date')->get();
        $social     = SocialIndicator::orderBy('period', 'desc')->orderBy('indicator_type')->get();

        $pdf = Pdf::loadView('pdf.esg-summary', compact('targets', 'grievances', 'policies', 'social'))
            ->setPaper('a4');

        return $pdf->download('ESG-Summary-' . now()->format('Y-m-d') . '.pdf');
    }

    // ----------------------------------------------------------------
    // HAZOP Study Report (specific study + all nodes + procedure)
    // ----------------------------------------------------------------

    public function hazopStudy(HazopStudy $study): Response
    {
        abort_unless(auth()->user()?->can('manage hazop'), 403);

        $study->load([
            'project',
            'department',
            'facilitator',
            'reviewedBy',
            'approvedBy',
        ]);

        $nodes = $study->nodes()
            ->with(['riskOwner', 'department', 'closureVerifiedBy'])
            ->orderBy('node_number')
            ->get();

        $pdf = Pdf::loadView('pdf.hazop-study', [
            'study' => $study,
            'nodes' => $nodes,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("{$study->study_ref}-Report-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // PTW Permit Certificate
    // ----------------------------------------------------------------

    public function ptwPermit(PermitToWork $permit): Response
    {
        abort_unless(auth()->user()?->can('manage permits'), 403);

        $permit->load([
            'project',
            'department',
            'requestedBy',
            'issuedBy',
            'areaAuthority',
            'supervisor',
            'finalApprovedBy',
            'completionConfirmedBy',
            'closeoutBy',
            'linkedHazard',
            'linkedHazopNode.study',
            'linkedIncident',
        ]);

        $checklistItems = $permit->checklistItems()->get();
        $approvals      = $permit->approvals()->with('approver')->get();

        $pdf = Pdf::loadView('pdf.ptw-permit', [
            'permit'         => $permit,
            'checklistItems' => $checklistItems,
            'approvals'      => $approvals,
        ])->setPaper('a4');

        return $pdf->download("{$permit->permit_number}-Certificate-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // HAZOP Procedure Document (static ISO-aligned procedure template)
    // ----------------------------------------------------------------

    public function hazopProcedure(): Response
    {
        abort_unless(auth()->user()?->can('manage hazop'), 403);

        $pdf = Pdf::loadView('pdf.hazop-procedure')->setPaper('a4');

        return $pdf->download('NOVAREX-HAZOP-Procedure-PRO-HSE-HAZOP-001-' . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // AMS Audit Report (ISO 9001 / 14001 / 45001 / 50001)
    // ----------------------------------------------------------------

    public function amsAuditReport(InternalAudit $audit): Response
    {
        abort_unless(auth()->user()?->can('manage audits'), 403);

        $audit->load([
            'project', 'department', 'leadAuditor', 'approvedBy',
            'teamMembers',
            'checklistItems',
            'nonConformities.assignedTo',
            'amsCapaActions.responsiblePerson',
            'amsCapaActions.nc',
        ]);

        $pdf = Pdf::loadView('pdf.ams-audit-report', compact('audit'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download(
            $audit->audit_reference . '-AMS-Report-' . now()->format('Ymd') . '.pdf'
        );
    }

    // ----------------------------------------------------------------
    // Environmental Audit Report (ISO 14001)
    // ----------------------------------------------------------------

    public function environmentalAudit(EnvironmentalAudit $audit): Response
    {
        abort_unless(auth()->user()?->can('manage esia_audits'), 403);

        $audit->load([
            'project', 'department', 'teamLeader', 'leadAuditor', 'approvedBy',
            'checklistItems',
            'findings.closedBy',
            // Step 17 approval workflow
            'leadAuditorSigner', 'pmApprover', 'clientApprover', 'finalApprover',
            'approvalLogs.user',
        ]);

        $pdf = Pdf::loadView('pdf.environmental-audit', compact('audit'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download(
            $audit->audit_number . '-Environmental-Audit-Report-' . now()->format('Ymd') . '.pdf'
        );
    }

    // ----------------------------------------------------------------
    // Invoice PDF
    // ----------------------------------------------------------------

    public function invoicePdf(Invoice $invoice): Response
    {
        abort_unless(auth()->user()?->can('manage invoices'), 403);

        $invoice->load('client', 'project', 'items', 'createdBy');

        $company = [
            'name'    => Setting::companyName(),
            'tagline' => Setting::companyTagline(),
            'address' => Setting::companyAddress(),
            'tin'     => Setting::companyTin(),
            'phone'   => Setting::companyPhone(),
            'email'   => Setting::companyEmail(),
        ];

        $bank = Setting::bankDetails();

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company', 'bank'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download("{$invoice->invoice_number}-" . now()->format('Ymd') . '.pdf');
    }

    // ----------------------------------------------------------------
    // HSE Maturity Scorecard
    // ----------------------------------------------------------------
    public function maturityScorecard(MaturityAssessment $assessment): Response
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor', 'business_director']),
            403
        );

        $assessment->load('project', 'assessedBy', 'scores.indicator.dimension');

        $breakdown = MaturityScoringService::dimensionBreakdown($assessment);

        // Build indicator detail grouped by dimension
        $indicatorDetail = [];
        foreach (MaturityDimension::with('indicators')->orderBy('sort_order')->get() as $dim) {
            $indicators = [];
            foreach ($dim->indicators as $ind) {
                $score = $assessment->scores->firstWhere('indicator_id', $ind->id);
                $indicators[] = [
                    'name'     => $ind->name,
                    'score'    => $score?->score ?? '—',
                    'auto'     => $score?->auto_calculated ?? false,
                    'evidence' => $score?->evidence,
                ];
            }
            $indicatorDetail[$dim->code] = [
                'name'       => $dim->name,
                'indicators' => $indicators,
            ];
        }

        // Trend: last 6 finalised assessments
        $trend = MaturityAssessment::where('status', 'finalised')
            ->orderByDesc('assessed_at')
            ->take(6)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($a) => [
                'period' => $a->period,
                'score'  => $a->overall_score,
                'level'  => $a->maturity_level,
            ])
            ->toArray();

        $levelDescription = match (true) {
            ($assessment->overall_score ?? 0) >= 4.3 => 'Optimizing — HSE practices are world-class, data-driven, and continuously improved.',
            ($assessment->overall_score ?? 0) >= 3.5 => 'Proactive — HSE is systematically managed with predictive controls in place.',
            ($assessment->overall_score ?? 0) >= 3.0 => 'Defined — Documented, standardised HSE processes exist and are consistently applied.',
            ($assessment->overall_score ?? 0) >= 2.0 => 'Basic — Some HSE processes exist but are reactive and inconsistently applied.',
            default                                   => 'Initial — HSE management is ad-hoc, reactive, and largely undocumented.',
        };

        $pdf = Pdf::loadView('pdf.maturity-scorecard', compact(
            'assessment', 'breakdown', 'indicatorDetail', 'trend', 'levelDescription'
        ))->setPaper('a4', 'portrait');

        $filename = 'HSE-Maturity-Scorecard-' . $assessment->period . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    // ================================================================
    // DOCX EXPORTS — same data, same views, Word format via PhpWord
    // ================================================================

    public function hiraDocx(HazardRegister $hazard): Response
    {
        abort_unless(auth()->user()?->can('manage hazards'), 403);

        $hazard->load('project', 'responsiblePerson');

        return DocxBuilderService::hira(
            $hazard,
            RiskScoringService::level((int) $hazard->initial_risk_score),
            RiskScoringService::level((int) $hazard->residual_risk_score)
        );
    }

    public function auditReportDocx(InternalAudit $audit): Response
    {
        abort_unless(auth()->user()?->can('manage audits'), 403);

        $audit->load('leadAuditor', 'teamMembers', 'findings.responsiblePerson', 'project', 'department');

        return DocxBuilderService::auditReport($audit);
    }

    public function incidentReportDocx(Incident $incident): Response
    {
        abort_unless(auth()->user()?->can('manage incidents'), 403);

        $incident->load('project', 'reportedBy');

        return DocxBuilderService::incidentReport(
            $incident,
            RiskScoringService::level((int) $incident->risk_score)
        );
    }

    public function environmentalAspectDocx(EnvironmentalAspect $aspect): Response
    {
        abort_unless(auth()->user()?->can('manage environmental_aspects'), 403);

        $aspect->load('project', 'responsiblePerson');

        return DocxBuilderService::environmentalAspect(
            $aspect,
            RiskScoringService::level((int) $aspect->significance_score)
        );
    }

    public function esiaReportDocx(EsiaReport $report): Response
    {
        abort_unless(auth()->user()?->can('manage esia_audits'), 403);

        $report->load('project', 'author', 'reviewedBy');

        $projectId     = $report->project_id;
        $screening     = EsiaScreening::where('project_id', $projectId)->latest()->first();
        $scopingIssues = EsiaScopingIssue::where('project_id', $projectId)->orderBy('sort_order')->get();
        $baselineData  = EsiaBaselineData::where('project_id', $projectId)->orderBy('parameter_type')->get();
        $impacts       = EsiaImpactAssessment::where('project_id', $projectId)->get();
        $mitigations   = EsiaMitigationAction::where('project_id', $projectId)->orderBy('timeline_start')->get();
        $submissions   = EsiaRegulatorySubmission::where('project_id', $projectId)->orderBy('submitted_at')->get();

        return DocxBuilderService::esiaReport(
            $report, $screening, $scopingIssues,
            $baselineData, $impacts, $mitigations, $submissions
        );
    }

    public function esgSummaryDocx(): Response
    {
        abort_unless(auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']), 403);

        $targets    = EsgTarget::with('owner')->orderBy('category')->orderBy('period')->get();
        $grievances = Grievance::whereNotIn('status', ['closed', 'resolved'])->get();
        $policies   = GovernancePolicy::where('status', 'active')->orderBy('review_date')->get();
        $social     = SocialIndicator::orderBy('period', 'desc')->orderBy('indicator_type')->get();

        return DocxBuilderService::esgSummary($targets, $grievances, $policies, $social);
    }

    public function hazopStudyDocx(HazopStudy $study): Response
    {
        abort_unless(auth()->user()?->can('manage hazop'), 403);

        $study->load(['project', 'department', 'facilitator', 'reviewedBy', 'approvedBy']);

        $nodes = $study->nodes()
            ->with(['riskOwner', 'department', 'closureVerifiedBy'])
            ->orderBy('node_number')
            ->get();

        return DocxBuilderService::hazopStudy($study, $nodes);
    }

    public function hazopProcedureDocx(): Response
    {
        abort_unless(auth()->user()?->can('manage hazop'), 403);

        return DocxBuilderService::hazopProcedure();
    }

    public function ptwPermitDocx(PermitToWork $permit): Response
    {
        abort_unless(auth()->user()?->can('manage permits'), 403);

        $permit->load([
            'project', 'department', 'requestedBy', 'issuedBy', 'areaAuthority',
            'supervisor', 'finalApprovedBy', 'completionConfirmedBy', 'closeoutBy',
            'linkedHazard', 'linkedHazopNode.study', 'linkedIncident',
        ]);

        $checklistItems = $permit->checklistItems()->get();
        $approvals      = $permit->approvals()->with('approver')->get();

        return DocxBuilderService::ptwPermit($permit, $checklistItems, $approvals);
    }

    public function amsAuditReportDocx(InternalAudit $audit): Response
    {
        abort_unless(auth()->user()?->can('manage audits'), 403);

        $audit->load([
            'project', 'department', 'leadAuditor', 'approvedBy', 'teamMembers',
            'checklistItems', 'nonConformities.assignedTo',
            'amsCapaActions.responsiblePerson', 'amsCapaActions.nc',
        ]);

        return DocxBuilderService::amsAuditReport($audit);
    }

    public function environmentalAuditDocx(EnvironmentalAudit $audit): Response
    {
        abort_unless(auth()->user()?->can('manage esia_audits'), 403);

        $audit->load([
            'project', 'department', 'teamLeader', 'leadAuditor', 'approvedBy',
            'checklistItems', 'findings.closedBy',
            'leadAuditorSigner', 'pmApprover', 'clientApprover', 'finalApprover',
            'approvalLogs.user',
        ]);

        return DocxBuilderService::environmentalAudit($audit);
    }

    public function invoiceDocx(Invoice $invoice): Response
    {
        abort_unless(auth()->user()?->can('manage invoices'), 403);

        $invoice->load('client', 'project', 'items', 'createdBy');

        $company = [
            'name'    => Setting::companyName(),
            'tagline' => Setting::companyTagline(),
            'address' => Setting::companyAddress(),
            'tin'     => Setting::companyTin(),
            'phone'   => Setting::companyPhone(),
            'email'   => Setting::companyEmail(),
        ];

        return DocxBuilderService::invoicePdf($invoice, $company, Setting::bankDetails());
    }

    public function maturityScorecardDocx(MaturityAssessment $assessment): Response
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor', 'business_director']),
            403
        );

        $assessment->load('project', 'assessedBy', 'scores.indicator.dimension');

        $breakdown = MaturityScoringService::dimensionBreakdown($assessment);

        $indicatorDetail = [];
        foreach (MaturityDimension::with('indicators')->orderBy('sort_order')->get() as $dim) {
            $indicators = [];
            foreach ($dim->indicators as $ind) {
                $score = $assessment->scores->firstWhere('indicator_id', $ind->id);
                $indicators[] = [
                    'name'     => $ind->name,
                    'score'    => $score?->score ?? '—',
                    'auto'     => $score?->auto_calculated ?? false,
                    'evidence' => $score?->evidence,
                ];
            }
            $indicatorDetail[$dim->code] = ['name' => $dim->name, 'indicators' => $indicators];
        }

        $trend = MaturityAssessment::where('status', 'finalised')
            ->orderByDesc('assessed_at')->take(6)->get()->reverse()->values()
            ->map(fn ($a) => ['period' => $a->period, 'score' => $a->overall_score, 'level' => $a->maturity_level])
            ->toArray();

        $levelDescription = match (true) {
            ($assessment->overall_score ?? 0) >= 4.3 => 'Optimizing — HSE practices are world-class, data-driven, and continuously improved.',
            ($assessment->overall_score ?? 0) >= 3.5 => 'Proactive — HSE is systematically managed with predictive controls in place.',
            ($assessment->overall_score ?? 0) >= 3.0 => 'Defined — Documented, standardised HSE processes exist and are consistently applied.',
            ($assessment->overall_score ?? 0) >= 2.0 => 'Basic — Some HSE processes exist but are reactive and inconsistently applied.',
            default                                   => 'Initial — HSE management is ad-hoc, reactive, and largely undocumented.',
        };

        return DocxBuilderService::maturityScorecard(
            $assessment, $breakdown, $indicatorDetail, $trend, $levelDescription
        );
    }

    // ================================================================
    // EMS FULL REPORT + SECTION EXPORTS
    // ================================================================

    private function emsData(): array
    {
        return [
            'aspects'           => EnvironmentalAspect::with('project', 'responsiblePerson')->orderBy('impact_category')->get(),
            'legalItems'        => LegalRegisterItem::orderBy('requirement_type')->get(),
            'permits'           => EnvironmentalPermit::with('project', 'responsibleOfficer')->orderBy('expiry_date')->get(),
            'monitoringRecords' => EnvironmentalMonitoringRecord::with('project', 'recordedBy')->orderByDesc('record_date')->get(),
            'wasteRecords'      => WasteTrackingRecord::with('project')->orderByDesc('generation_date')->get(),
            'spillReports'      => SpillReport::with('project', 'reportedBy')->orderByDesc('spill_date')->get(),
            'ciActions'         => EmsImprovementAction::with('assignedTo', 'project')->orderBy('target_date')->get(),
        ];
    }

    public function emsFullReport(): Response
    {
        abort_unless(auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor', 'business_director']), 403);

        $data = $this->emsData();

        $pdf = Pdf::loadView('pdf.ems-full-report', $data)->setPaper('a4', 'landscape');

        return $pdf->download('EMS-Full-Report-' . now()->format('Ymd') . '.pdf');
    }

    public function emsFullReportDocx(): Response
    {
        abort_unless(auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor', 'business_director']), 403);

        $data = $this->emsData();

        return DocxBuilderService::emsFullReport(...array_values($data));
    }

    // ----------------------------------------------------------------
    // Training Manual
    // ----------------------------------------------------------------
    public function trainingManual(): View
    {
        return view('training.manual');
    }
}
