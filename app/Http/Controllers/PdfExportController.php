<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentalAspect;
use App\Models\EsgTarget;
use App\Models\Grievance;
use App\Models\GovernancePolicy;
use App\Models\HazardRegister;
use App\Models\Incident;
use App\Models\InternalAudit;
use App\Models\SocialIndicator;
use App\Services\RiskScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
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
}
