<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Environmental Audit Report — {{ $audit->audit_number }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1a202c; background:#fff; }

  .page-header { background:#1a3c5e; color:#fff; padding:14px 20px; }
  .page-header h1 { font-size:16px; font-weight:700; letter-spacing:.5px; }
  .page-header .sub { font-size:10px; color:#90cdf4; margin-top:3px; }

  .meta-grid { display:table; width:100%; border-collapse:collapse; margin-top:12px; }
  .meta-row { display:table-row; }
  .meta-cell { display:table-cell; padding:5px 10px; border:1px solid #e2e8f0; font-size:8.5px; width:25%; vertical-align:top; }
  .meta-cell .lbl { color:#718096; font-size:7.5px; text-transform:uppercase; letter-spacing:.4px; }
  .meta-cell .val { font-weight:600; margin-top:2px; }

  h2 { font-size:11px; font-weight:700; background:#2d3748; color:#fff; padding:5px 10px; margin-top:14px; }
  h3 { font-size:9.5px; font-weight:700; background:#edf2f7; color:#2d3748; padding:4px 8px; margin-top:8px; border-left:3px solid #3182ce; }

  table { width:100%; border-collapse:collapse; margin-top:6px; font-size:8px; }
  th { background:#2d3748; color:#fff; padding:5px 6px; text-align:left; font-size:7.5px; text-transform:uppercase; }
  td { padding:4px 6px; border-bottom:1px solid #e2e8f0; vertical-align:top; }
  tr:nth-child(even) td { background:#f7fafc; }

  .badge { display:inline-block; padding:1px 5px; border-radius:3px; font-size:7.5px; font-weight:600; }
  .b-success  { background:#c6f6d5; color:#276749; }
  .b-warning  { background:#fef3c7; color:#92400e; }
  .b-danger   { background:#fed7d7; color:#9b2c2c; }
  .b-info     { background:#bee3f8; color:#2c5282; }
  .b-gray     { background:#e2e8f0; color:#4a5568; }
  .b-primary  { background:#e9d8fd; color:#44337a; }

  .score-box { text-align:center; padding:12px; margin:10px 0; border:2px solid; border-radius:6px; }
  .score-excellent { border-color:#38a169; background:#f0fff4; }
  .score-good      { border-color:#3182ce; background:#ebf8ff; }
  .score-fair      { border-color:#d69e2e; background:#fffff0; }
  .score-poor      { border-color:#e53e3e; background:#fff5f5; }
  .score-critical  { border-color:#742a2a; background:#fff5f5; }
  .sig-table td { padding:6px 10px; border:1px solid #e2e8f0; width:25%; vertical-align:top; }
  .sig-table th { background:#2d3748; color:#fff; padding:5px 10px; text-align:left; }
  .sig-blank { border-bottom:1px solid #4a5568; margin-top:24px; margin-bottom:3px; width:85%; }
  .score-number { font-size:26px; font-weight:700; }
  .score-label  { font-size:11px; font-weight:600; margin-top:2px; }

  .stat-row { display:table; width:100%; border-collapse:collapse; margin:8px 0; }
  .stat-cell { display:table-cell; text-align:center; border:1px solid #e2e8f0; padding:6px; width:20%; }
  .stat-cell .num { font-size:16px; font-weight:700; }
  .stat-cell .lbl { font-size:7.5px; color:#718096; }

  .section-text { padding:8px; font-size:8.5px; line-height:1.5; background:#f7fafc; border:1px solid #e2e8f0; margin-top:4px; }
  .footer { text-align:center; font-size:7.5px; color:#718096; margin-top:20px; border-top:1px solid #e2e8f0; padding-top:8px; }
  .page-break { page-break-before: always; }

  @media print { .page-break { page-break-before: always; } }
</style>
</head>
<body>

@include('filament.pdf.partials.letterhead')

{{-- ── TITLE BAND ───────────────────────────────────────────────────── --}}
<div class="page-header">
  <h1>ENVIRONMENTAL AUDIT REPORT</h1>
  <div class="sub">
    ISO 14001:2015 Environmental Management System | ISO 19011:2018 Audit Guidelines
  </div>
</div>

{{-- ── AUDIT IDENTITY GRID ───────────────────────────────────────────── --}}
<div class="meta-grid">
  <div class="meta-row">
    <div class="meta-cell"><div class="lbl">Audit ID</div><div class="val">{{ $audit->audit_number }}</div></div>
    <div class="meta-cell"><div class="lbl">Reference No.</div><div class="val">{{ $audit->audit_reference ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Audit Type</div><div class="val">{{ \App\Models\EnvironmentalAudit::AUDIT_TYPE_LABELS[$audit->audit_type] ?? $audit->audit_type }}</div></div>
    <div class="meta-cell"><div class="lbl">Status</div><div class="val">{{ \App\Models\EnvironmentalAudit::STATUS_LABELS[$audit->status] ?? $audit->status }}</div></div>
  </div>
  <div class="meta-row">
    <div class="meta-cell"><div class="lbl">Audit Title</div><div class="val">{{ $audit->audit_title }}</div></div>
    <div class="meta-cell"><div class="lbl">Site / Location</div><div class="val">{{ $audit->site_location ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Audit Date</div><div class="val">{{ $audit->audit_date?->format('d M Y') ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Method</div><div class="val">{{ \App\Models\EnvironmentalAudit::AUDIT_METHOD_LABELS[$audit->audit_method] ?? $audit->audit_method }}</div></div>
  </div>
  <div class="meta-row">
    <div class="meta-cell"><div class="lbl">Project</div><div class="val">{{ $audit->project->title ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Department</div><div class="val">{{ $audit->department->name ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Duration</div><div class="val">{{ $audit->audit_duration_days }} day(s)</div></div>
    <div class="meta-cell"><div class="lbl">Team Leader</div><div class="val">{{ $audit->teamLeader->name ?? '—' }}</div></div>
  </div>
  <div class="meta-row">
    <div class="meta-cell"><div class="lbl">Lead Auditor</div><div class="val">{{ $audit->leadAuditor->name ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Co-Auditors</div><div class="val">{{ $audit->co_auditors ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Technical Experts</div><div class="val">{{ $audit->technical_experts ?? '—' }}</div></div>
    <div class="meta-cell"><div class="lbl">Auditee Rep(s)</div><div class="val">{{ $audit->auditee_representatives ?? '—' }}</div></div>
  </div>
</div>

{{-- ── COMPLIANCE SCORE ──────────────────────────────────────────────── --}}
<h2>AUDIT COMPLIANCE SCORE</h2>
@php
  $ratingClass = match($audit->rating) {
    'excellent' => 'score-excellent',
    'good'      => 'score-good',
    'fair'      => 'score-fair',
    'poor'      => 'score-poor',
    'critical'  => 'score-critical',
    default     => 'score-poor',
  };
  $ratingLabel = match($audit->rating) {
    'excellent' => 'EXCELLENT — Fully Compliant System (90–100%)',
    'good'      => 'GOOD — Minor Gaps, No Major Risk (80–89%)',
    'fair'      => 'FAIR — Significant Improvements Required (70–79%)',
    'poor'      => 'POOR — Major Non-Compliance, Urgent Action Required (50–69%)',
    'critical'  => 'CRITICAL — Severe Non-Compliance, Immediate Action Required (<50%)',
    default     => 'Not Assessed',
  };
@endphp
<div class="score-box {{ $ratingClass }}">
  <div class="score-number">{{ number_format($audit->compliance_score, 1) }}%</div>
  <div class="score-label">{{ $ratingLabel }}</div>
</div>

{{-- Summary stats --}}
@php
  $items        = $audit->checklistItems;
  $applicable   = $items->filter(fn($i) => $i->compliance_status !== 'not_applicable');
  $compliant    = $applicable->where('compliance_status', 'compliant')->count();
  $partial      = $applicable->where('compliance_status', 'partially_compliant')->count();
  $nonCompliant = $applicable->where('compliance_status', 'non_compliant')->count();
  $na           = $items->where('compliance_status', 'not_applicable')->count();
  $totalItems   = $items->count();

  $findings     = $audit->findings;
  $majorNCs     = $findings->where('finding_type', 'major_nc')->count();
  $minorNCs     = $findings->where('finding_type', 'minor_nc')->count();
  $observations = $findings->where('finding_type', 'observation')->count();
  $ofis         = $findings->where('finding_type', 'ofi')->count();
  $openActions  = $findings->where('action_status', '!=', 'closed')->count();
@endphp

<div class="stat-row">
  <div class="stat-cell"><div class="num" style="color:#38a169">{{ $compliant }}</div><div class="lbl">Compliant</div></div>
  <div class="stat-cell"><div class="num" style="color:#d69e2e">{{ $partial }}</div><div class="lbl">Partial</div></div>
  <div class="stat-cell"><div class="num" style="color:#e53e3e">{{ $nonCompliant }}</div><div class="lbl">Non-Compliant</div></div>
  <div class="stat-cell"><div class="num" style="color:#718096">{{ $na }}</div><div class="lbl">Not Applicable</div></div>
  <div class="stat-cell"><div class="num">{{ $totalItems }}</div><div class="lbl">Total Items</div></div>
</div>
<div class="stat-row">
  <div class="stat-cell"><div class="num" style="color:#e53e3e">{{ $majorNCs }}</div><div class="lbl">Major NCs</div></div>
  <div class="stat-cell"><div class="num" style="color:#d69e2e">{{ $minorNCs }}</div><div class="lbl">Minor NCs</div></div>
  <div class="stat-cell"><div class="num" style="color:#3182ce">{{ $observations }}</div><div class="lbl">Observations</div></div>
  <div class="stat-cell"><div class="num" style="color:#38a169">{{ $ofis }}</div><div class="lbl">OFIs</div></div>
  <div class="stat-cell"><div class="num" style="color:#e53e3e">{{ $openActions }}</div><div class="lbl">Open Actions</div></div>
</div>

{{-- ── SCOPE & OBJECTIVES ────────────────────────────────────────────── --}}
@if($audit->scope || $audit->objectives || $audit->criteria)
<h2>SCOPE, OBJECTIVES & CRITERIA</h2>
@if($audit->scope)
  <h3>Audit Scope</h3>
  <div class="section-text">{{ $audit->scope }}</div>
@endif
@if($audit->objectives)
  <h3>Audit Objectives</h3>
  <div class="section-text">{{ $audit->objectives }}</div>
@endif
@if($audit->criteria)
  <h3>Audit Criteria</h3>
  <div class="section-text">{{ $audit->criteria }}</div>
@endif
@endif

{{-- ── CHECKLIST BY CATEGORY ────────────────────────────────────────── --}}
<div class="page-break"></div>
<h2>CHECKLIST ASSESSMENT RESULTS (ISO 14001 REQUIREMENTS)</h2>

@php
  $grouped = $items->groupBy('category');
  $catLabels = \App\Services\EnvironmentalAuditService::categoryLabels();
  $statusColors = ['compliant'=>'b-success','partially_compliant'=>'b-warning','non_compliant'=>'b-danger','not_applicable'=>'b-gray'];
  $statusLabels = ['compliant'=>'Compliant','partially_compliant'=>'Partial','non_compliant'=>'Non-Compliant','not_applicable'=>'N/A'];
@endphp

@foreach($grouped as $cat => $catItems)
<h3>{{ $catLabels[$cat] ?? "Category {$cat}" }}</h3>
<table>
  <thead>
    <tr>
      <th style="width:7%">Code</th>
      <th style="width:43%">Requirement</th>
      <th style="width:14%">Status</th>
      <th style="width:36%">Evidence / Findings Notes</th>
    </tr>
  </thead>
  <tbody>
    @foreach($catItems as $item)
    <tr>
      <td><strong>{{ $item->item_code }}</strong></td>
      <td>{{ $item->item_description }}</td>
      <td>
        <span class="badge {{ $statusColors[$item->compliance_status] ?? 'b-gray' }}">
          {{ $statusLabels[$item->compliance_status] ?? $item->compliance_status }}
        </span>
      </td>
      <td>
        @if($item->evidence_notes)
          <em>Evidence:</em> {{ \Str::limit($item->evidence_notes, 120) }}<br>
        @endif
        @if($item->findings_notes)
          <strong>Finding:</strong> {{ \Str::limit($item->findings_notes, 120) }}
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endforeach

{{-- ── FINDINGS REGISTER ────────────────────────────────────────────── --}}
@if($findings->count() > 0)
<div class="page-break"></div>
<h2>AUDIT FINDINGS REGISTER</h2>
@php
  $typeColors = ['major_nc'=>'b-danger','minor_nc'=>'b-warning','observation'=>'b-info','ofi'=>'b-success'];
  $typeLabels = \App\Models\EnvironmentalAuditFinding::FINDING_TYPE_LABELS;
  $riskColors = ['low'=>'b-success','medium'=>'b-warning','high'=>'b-danger','critical'=>'b-danger'];
  $riskLabels = \App\Models\EnvironmentalAuditFinding::RISK_LEVEL_LABELS;
  $statusColors2 = ['open'=>'b-danger','in_progress'=>'b-warning','closed'=>'b-success'];
  $statusLabels2 = \App\Models\EnvironmentalAuditFinding::ACTION_STATUS_LABELS;
@endphp

<table>
  <thead>
    <tr>
      <th style="width:6%">No.</th>
      <th style="width:12%">Type</th>
      <th style="width:30%">Description</th>
      <th style="width:12%">Risk</th>
      <th style="width:20%">Action Owner / Dept</th>
      <th style="width:10%">Due Date</th>
      <th style="width:10%">Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($findings as $f)
    <tr>
      <td><strong>{{ $f->finding_number }}</strong></td>
      <td><span class="badge {{ $typeColors[$f->finding_type] ?? 'b-gray' }}">{{ $typeLabels[$f->finding_type] ?? $f->finding_type }}</span>
        @if($f->regulatory_impact)<br><span class="badge b-danger" style="font-size:6.5px;margin-top:2px;">REG. IMPACT</span>@endif
      </td>
      <td>{{ \Str::limit($f->description, 150) }}</td>
      <td>
        <span class="badge {{ $riskColors[$f->risk_level] ?? 'b-gray' }}">{{ $riskLabels[$f->risk_level] ?? $f->risk_level }}</span>
        <br><small>L={{ $f->likelihood }} × S={{ $f->severity }} = {{ $f->risk_score }}</small>
      </td>
      <td>
        {{ $f->action_owner ?? '—' }}
        @if($f->department_responsible)<br><small>{{ $f->department_responsible }}</small>@endif
      </td>
      <td>{{ $f->target_completion_date?->format('d M Y') ?? '—' }}</td>
      <td><span class="badge {{ $statusColors2[$f->action_status] ?? 'b-gray' }}">{{ $statusLabels2[$f->action_status] ?? $f->action_status }}</span></td>
    </tr>
    @if($f->recommended_action)
    <tr>
      <td colspan="7" style="background:#fffaf0;font-size:7.5px;padding:3px 6px;">
        <strong>Recommended Action:</strong> {{ \Str::limit($f->recommended_action, 200) }}
      </td>
    </tr>
    @endif
    @endforeach
  </tbody>
</table>
@endif

{{-- ── RISK SUMMARY (Step 16) ───────────────────────────────────────── --}}
@if($findings->count() > 0)
<h2>RISK SUMMARY</h2>
@php
  $critical = $findings->where('risk_level', 'critical')->count();
  $high     = $findings->where('risk_level', 'high')->count();
  $medium   = $findings->where('risk_level', 'medium')->count();
  $low      = $findings->where('risk_level', 'low')->count();
@endphp
<div class="stat-row">
  <div class="stat-cell"><div class="num" style="color:#742a2a">{{ $critical }}</div><div class="lbl">Critical Risk</div></div>
  <div class="stat-cell"><div class="num" style="color:#e53e3e">{{ $high }}</div><div class="lbl">High Risk</div></div>
  <div class="stat-cell"><div class="num" style="color:#d69e2e">{{ $medium }}</div><div class="lbl">Medium Risk</div></div>
  <div class="stat-cell"><div class="num" style="color:#38a169">{{ $low }}</div><div class="lbl">Low Risk</div></div>
  <div class="stat-cell"><div class="num">{{ $findings->count() }}</div><div class="lbl">Total Findings</div></div>
</div>

@if($majorNCs > 0)
<h3>Major Non-Conformances Requiring Immediate Action</h3>
<table>
  <thead>
    <tr>
      <th style="width:8%">No.</th>
      <th style="width:50%">Description</th>
      <th style="width:20%">Action Owner</th>
      <th style="width:12%">Due Date</th>
      <th style="width:10%">Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($findings->where('finding_type', 'major_nc') as $f)
    <tr>
      <td><strong>{{ $f->finding_number }}</strong></td>
      <td>{{ $f->description }}</td>
      <td>{{ $f->action_owner ?? '—' }}</td>
      <td>{{ $f->target_completion_date?->format('d M Y') ?? '—' }}</td>
      <td><span class="badge {{ $statusColors2[$f->action_status] ?? 'b-gray' }}">{{ $statusLabels2[$f->action_status] ?? $f->action_status }}</span></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif
@endif

{{-- ── MANAGEMENT SUMMARY ───────────────────────────────────────────── --}}
@if($audit->management_summary || $audit->closing_notes)
<h2>MANAGEMENT SUMMARY & EXECUTIVE NOTES</h2>
@if($audit->management_summary)
  <h3>Management Summary</h3>
  <div class="section-text">{{ $audit->management_summary }}</div>
@endif
@if($audit->closing_notes)
  <h3>Auditor Conclusion / Recommendations</h3>
  <div class="section-text">{{ $audit->closing_notes }}</div>
@endif
@endif

{{-- ── RECOMMENDATIONS (Step 16) ───────────────────────────────────── --}}
@if($findings->whereNotNull('recommended_action')->count() > 0)
<h2>RECOMMENDATIONS (Step 16)</h2>
<table>
  <thead>
    <tr>
      <th style="width:8%">Finding</th>
      <th style="width:12%">Type</th>
      <th style="width:80%">Recommended Action</th>
    </tr>
  </thead>
  <tbody>
    @foreach($findings->whereNotNull('recommended_action') as $f)
    <tr>
      <td><strong>{{ $f->finding_number }}</strong></td>
      <td><span class="badge {{ $typeColors[$f->finding_type] ?? 'b-gray' }}">{{ $typeLabels[$f->finding_type] ?? $f->finding_type }}</span></td>
      <td>{{ $f->recommended_action }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

{{-- ── APPROVAL TRAIL (Step 17) ─────────────────────────────────────── --}}
<div class="page-break"></div>
<h2>MANAGEMENT APPROVAL TRAIL (Step 17)</h2>
@php
  $approvalLogs = $audit->approvalLogs ?? collect();
@endphp

@if($approvalLogs->count() > 0)
<table>
  <thead>
    <tr>
      <th style="width:22%">Stage</th>
      <th style="width:18%">Signed By</th>
      <th style="width:12%">Action</th>
      <th style="width:14%">Date &amp; Time</th>
      <th style="width:34%">Comments</th>
    </tr>
  </thead>
  <tbody>
    @foreach($approvalLogs as $log)
    <tr>
      <td><strong>{{ \App\Models\EnvAuditApprovalLog::$stageLabels[$log->stage] ?? $log->stage }}</strong></td>
      <td>{{ $log->signature_text }}</td>
      <td>
        <span class="badge {{ $log->action === 'approved' ? 'b-success' : 'b-danger' }}">
          {{ ucfirst($log->action) }}
        </span>
      </td>
      <td>{{ $log->signed_at?->format('d M Y H:i') }}</td>
      <td>{{ $log->comments ?? '—' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@else
<div class="section-text" style="color:#718096;font-style:italic;">No approval actions recorded yet. Submit the audit to begin the Step 17 approval workflow.</div>
@endif

{{-- Multi-stage signature block ───────────────────────────────────── --}}
<div style="margin-top:16px;">
  <table class="sig-table">
    <tr>
      <th>Auditor (Prepared)</th>
      <th>Lead Auditor</th>
      <th>Project Manager</th>
      <th>Client Representative</th>
    </tr>
    <tr style="height:50px; vertical-align:bottom;">
      <td>
        <div class="sig-blank"></div>
        <div>{{ $audit->leadAuditor->name ?? '_______________________' }}</div>
        <div style="font-size:7.5px;color:#718096;">{{ $audit->lead_auditor_signed_at?->format('d M Y') ?? 'Date: ______________' }}</div>
      </td>
      <td>
        <div class="sig-blank"></div>
        <div>{{ $audit->leadAuditorSigner->name ?? '_______________________' }}</div>
        <div style="font-size:7.5px;color:#718096;">{{ $audit->lead_auditor_signed_at?->format('d M Y') ?? 'Date: ______________' }}</div>
      </td>
      <td>
        <div class="sig-blank"></div>
        <div>{{ $audit->pmApprover->name ?? '_______________________' }}</div>
        <div style="font-size:7.5px;color:#718096;">{{ $audit->pm_approved_at?->format('d M Y') ?? 'Date: ______________' }}</div>
      </td>
      <td>
        <div class="sig-blank"></div>
        <div>{{ $audit->clientApprover->name ?? '_______________________' }}</div>
        <div style="font-size:7.5px;color:#718096;">{{ $audit->client_approved_at?->format('d M Y') ?? 'Date: ______________' }}</div>
      </td>
    </tr>
  </table>
</div>

@if($audit->final_approved_at)
<div style="margin-top:10px; padding:8px 12px; background:#f0fff4; border:1px solid #38a169; font-size:8.5px;">
  <strong style="color:#276749;">FINALLY APPROVED</strong> by {{ $audit->finalApprover->name ?? '—' }}
  on {{ $audit->final_approved_at->format('d M Y H:i') }}
  @if($audit->final_comments) — {{ $audit->final_comments }} @endif
</div>
@endif

<div class="footer">
  Generated: {{ now()->format('d M Y H:i') }} |
  Audit Ref: {{ $audit->audit_number }} |
  {{ config('app.name') }} — Environmental Management System (ISO 14001:2015)<br>
  "An audit is only complete when all findings are closed, verified, and their effectiveness has been confirmed."
</div>

</body>
</html>
