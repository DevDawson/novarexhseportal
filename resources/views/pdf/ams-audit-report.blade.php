<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }

  /* ── Header ── */
  .header { background: #1e3a5f; color: #fff; padding: 14px 18px; display: table; width: 100%; }
  .header-left  { display: table-cell; vertical-align: middle; }
  .header-right { display: table-cell; text-align: right; vertical-align: middle; }
  .header h1 { font-size: 15pt; font-weight: bold; letter-spacing: 0.5px; }
  .header .sub { font-size: 8pt; opacity: 0.85; margin-top: 3px; }
  .header .ref-box { background: rgba(255,255,255,0.15); border-radius: 4px; padding: 6px 12px; display: inline-block; }
  .header .ref-box .ref-num  { font-size: 13pt; font-weight: bold; }
  .header .ref-box .ref-date { font-size: 7.5pt; opacity: 0.8; }

  /* ── Section title ── */
  .section-title { background: #1e3a5f; color: #fff; font-size: 9.5pt; font-weight: bold;
                   padding: 5px 10px; margin: 12px 0 6px; }
  .section-title-alt { background: #2d6a4f; color: #fff; font-size: 9.5pt; font-weight: bold;
                       padding: 5px 10px; margin: 12px 0 6px; }

  /* ── Meta grid ── */
  .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  .meta-table td { padding: 5px 8px; font-size: 8.5pt; border: 1px solid #d0d0d0; vertical-align: top; }
  .meta-table .lbl { background: #f0f4f8; font-weight: bold; width: 22%; color: #2c3e50; }

  /* ── Score box ── */
  .score-row { display: table; width: 100%; margin: 8px 0; }
  .score-cell { display: table-cell; width: 25%; padding: 10px; border-radius: 4px; text-align: center; vertical-align: middle; }
  .score-cell .big { font-size: 22pt; font-weight: bold; }
  .score-cell .lbl { font-size: 8pt; margin-top: 2px; }
  .score-bg-excellent { background: #d4edda; color: #155724; }
  .score-bg-good      { background: #cce5ff; color: #004085; }
  .score-bg-fair      { background: #fff3cd; color: #856404; }
  .score-bg-poor      { background: #f8d7da; color: #721c24; }
  .score-bg-na        { background: #e9ecef; color: #495057; }
  .stat-cell { display: table-cell; padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 3px;
               background: #fafafa; text-align: center; vertical-align: middle; font-size: 8pt; }
  .stat-cell .num { font-size: 16pt; font-weight: bold; }

  /* ── Checklist table ── */
  .checklist-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
  .checklist-table th { background: #2d6a4f; color: #fff; padding: 4px 6px; text-align: left; }
  .checklist-table td { padding: 4px 6px; border: 1px solid #dde; vertical-align: top; }
  .checklist-table tr:nth-child(even) td { background: #f8f8ff; }
  .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 7.5pt; font-weight: bold; }
  .badge-compliant    { background: #d4edda; color: #155724; }
  .badge-nc           { background: #f8d7da; color: #721c24; }
  .badge-observation  { background: #fff3cd; color: #856404; }
  .badge-na           { background: #e9ecef; color: #495057; }
  .badge-pending      { background: #e2e3e5; color: #383d41; }

  /* ── NC table ── */
  .nc-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
  .nc-table th { background: #7b2d3e; color: #fff; padding: 4px 6px; text-align: left; }
  .nc-table td { padding: 4px 6px; border: 1px solid #dde; vertical-align: top; }
  .nc-table tr:nth-child(even) td { background: #fff8f8; }
  .risk-high   { color: #721c24; font-weight: bold; }
  .risk-medium { color: #856404; font-weight: bold; }
  .risk-low    { color: #155724; font-weight: bold; }

  /* ── CAPA table ── */
  .capa-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
  .capa-table th { background: #4a3080; color: #fff; padding: 4px 6px; text-align: left; }
  .capa-table td { padding: 4px 6px; border: 1px solid #dde; vertical-align: top; }
  .capa-table tr:nth-child(even) td { background: #f8f8ff; }

  /* ── Narrative ── */
  .narrative { background: #f9fbfd; border-left: 4px solid #1e3a5f; padding: 8px 10px;
               margin: 6px 0; font-size: 8.5pt; line-height: 1.5; }

  /* ── Signature block ── */
  .sig-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
  .sig-table td { border: 1px solid #aaa; padding: 10px; width: 25%; vertical-align: bottom; text-align: center; }
  .sig-table .sig-line { border-top: 1px solid #555; margin-top: 28px; padding-top: 4px; font-size: 7.5pt; color: #555; }

  /* ── Footer ── */
  .footer { margin-top: 14px; border-top: 2px solid #1e3a5f; padding-top: 6px;
            font-size: 7pt; color: #777; text-align: center; }
  .page-break { page-break-after: always; }
  .cat-header { background: #344e6e; color: #fff; font-weight: bold; padding: 4px 8px;
                font-size: 8.5pt; margin-top: 6px; }
</style>
</head>
<body>

@include('filament.pdf.partials.letterhead')

{{-- ================================================================ --}}
{{-- TITLE BAND --}}
{{-- ================================================================ --}}
<div class="header">
  <div class="header-left">
    <h1>AUDIT MANAGEMENT SYSTEM — REPORT</h1>
    <div class="sub">ISO 9001 / ISO 14001 / ISO 45001 / ISO 50001 · Ref: ISO 19011:2018</div>
    <div class="sub" style="margin-top:4px;">{{ $audit->audit_title ?? 'Internal Audit Report' }}</div>
  </div>
  <div class="header-right">
    <div class="ref-box">
      <div class="ref-num">{{ $audit->audit_reference }}</div>
      <div class="ref-date">Generated: {{ now()->format('d M Y, H:i') }}</div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 1: AUDIT IDENTIFICATION --}}
{{-- ================================================================ --}}
<div class="section-title">1. AUDIT IDENTIFICATION</div>
<table class="meta-table">
  <tr>
    <td class="lbl">Audit Reference</td>
    <td>{{ $audit->audit_reference }}</td>
    <td class="lbl">Audit Type</td>
    <td>{{ $audit->audit_type ? \Illuminate\Support\Str::title(str_replace('_', ' ', $audit->audit_type)) : '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Standard(s)</td>
    <td>{{ \App\Models\InternalAudit::STANDARD_LABELS[$audit->standard] ?? $audit->standard }}</td>
    <td class="lbl">Status</td>
    <td>{{ \App\Models\InternalAudit::STATUS_LABELS[$audit->status] ?? $audit->status }}</td>
  </tr>
  <tr>
    <td class="lbl">Audit Date</td>
    <td>{{ $audit->audit_date?->format('d M Y') ?? '—' }}</td>
    <td class="lbl">Location / Site</td>
    <td>{{ $audit->audit_location ?? '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Project</td>
    <td>{{ $audit->project?->title ?? 'Not project-specific' }}</td>
    <td class="lbl">Department</td>
    <td>{{ $audit->department?->name ?? '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Lead Auditor</td>
    <td>{{ $audit->leadAuditor?->name ?? '—' }}</td>
    <td class="lbl">Auditee Representative</td>
    <td>{{ $audit->auditee_representative ?? '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Planned Period</td>
    <td colspan="3">
      {{ $audit->planned_start_date?->format('d M Y') ?? '—' }}
      @if($audit->planned_end_date) to {{ $audit->planned_end_date->format('d M Y') }} @endif
    </td>
  </tr>
  <tr>
    <td class="lbl">Approved By</td>
    <td>{{ $audit->approvedBy?->name ?? 'Pending approval' }}</td>
    <td class="lbl">Approved At</td>
    <td>{{ $audit->approved_at?->format('d M Y H:i') ?? '—' }}</td>
  </tr>
</table>

{{-- ================================================================ --}}
{{-- SECTION 2: SCOPE & OBJECTIVES --}}
{{-- ================================================================ --}}
<div class="section-title">2. SCOPE &amp; OBJECTIVES</div>
@if($audit->scope)
<div class="narrative"><strong>Scope:</strong> {{ $audit->scope }}</div>
@endif
@if($audit->audit_objectives)
<div class="narrative"><strong>Objectives:</strong> {{ $audit->audit_objectives }}</div>
@endif
@if($audit->audit_criteria)
<div class="narrative"><strong>Criteria:</strong> {{ $audit->audit_criteria }}</div>
@endif

{{-- ================================================================ --}}
{{-- SECTION 3: COMPLIANCE SCORECARD --}}
{{-- ================================================================ --}}
<div class="section-title">3. COMPLIANCE SCORECARD</div>
@php
  $checklistItems = $audit->checklistItems ?? collect();
  $total      = $checklistItems->count();
  $compliant  = $checklistItems->where('response', 'compliant')->count();
  $nc         = $checklistItems->where('response', 'non_compliant')->count();
  $obs        = $checklistItems->where('response', 'observation')->count();
  $na         = $checklistItems->where('response', 'not_applicable')->count();
  $pending    = $checklistItems->where('response', 'not_assessed')->count();
  $applicable = $total - $na - $pending;
  $score      = $applicable > 0 ? round(($compliant / $applicable) * 100, 1) : null;
  $rating     = $score === null ? null : ($score >= 90 ? 'excellent' : ($score >= 75 ? 'good' : ($score >= 50 ? 'fair' : 'poor')));
  $bgClass    = 'score-bg-' . ($rating ?? 'na');

  $openNcs    = $audit->nonConformities->whereIn('status', ['open', 'in_progress'])->count();
  $criticalNcs= $audit->nonConformities->where('nc_type', 'critical')->count();
  $openCapa   = $audit->amsCapaActions->whereIn('status', ['open', 'in_progress'])->count();
@endphp
<div class="score-row">
  <div class="score-cell {{ $bgClass }}" style="width:22%;">
    <div class="big">{{ $score !== null ? $score . '%' : 'N/A' }}</div>
    <div class="lbl">Checklist Compliance Score</div>
    @if($rating)<div class="lbl" style="font-weight:bold;margin-top:3px;">{{ strtoupper($rating) }}</div>@endif
  </div>
  <div style="display:table-cell;width:78%;padding-left:8px;">
    <div style="display:table;width:100%;">
      <div class="stat-cell" style="width:16%;">
        <div class="num" style="color:#155724;">{{ $compliant }}</div>
        <div>Compliant</div>
      </div>
      <div class="stat-cell" style="width:16%;">
        <div class="num" style="color:#856404;">{{ $obs }}</div>
        <div>Observations</div>
      </div>
      <div class="stat-cell" style="width:16%;">
        <div class="num" style="color:#721c24;">{{ $nc }}</div>
        <div>Non-Compliant</div>
      </div>
      <div class="stat-cell" style="width:16%;">
        <div class="num" style="color:#495057;">{{ $na }}</div>
        <div>N/A</div>
      </div>
      <div class="stat-cell" style="width:16%;">
        <div class="num" style="color:#721c24;">{{ $openNcs }}</div>
        <div>Open NCs</div>
      </div>
      <div class="stat-cell" style="width:16%;">
        <div class="num" style="color:#4a3080;">{{ $openCapa }}</div>
        <div>Open CAPA</div>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 4: AUDIT CHECKLIST --}}
{{-- ================================================================ --}}
<div class="section-title">4. AUDIT CHECKLIST ASSESSMENT</div>
@if($checklistItems->isEmpty())
  <p style="font-size:8.5pt;color:#666;padding:6px 0;">No checklist items recorded for this audit.</p>
@else
@php
  $grouped = $checklistItems->groupBy('iso_standard');
  $stdNames = \App\Services\AuditManagementService::standardLabels();
@endphp
@foreach($grouped as $std => $items)
  <div class="cat-header">{{ $stdNames[$std] ?? strtoupper($std) }}</div>
  <table class="checklist-table">
    <thead>
      <tr>
        <th style="width:12%;">Clause</th>
        <th>Audit Question / Requirement</th>
        <th style="width:13%;">Response</th>
        <th style="width:5%;">Score</th>
        <th style="width:22%;">Evidence / Notes</th>
      </tr>
    </thead>
    <tbody>
    @foreach($items->sortBy('sort_order') as $item)
      @php
        $badgeClass = match($item->response) {
          'compliant'      => 'badge-compliant',
          'non_compliant'  => 'badge-nc',
          'observation'    => 'badge-observation',
          'not_applicable' => 'badge-na',
          default          => 'badge-pending',
        };
        $responseLabel = \App\Models\AuditChecklistItem::RESPONSE_LABELS[$item->response] ?? $item->response;
      @endphp
      <tr>
        <td>{{ $item->clause_reference ?? '—' }}</td>
        <td>{{ $item->question }}</td>
        <td><span class="badge {{ $badgeClass }}">{{ $responseLabel }}</span></td>
        <td style="text-align:center;">{{ $item->score ?? '—' }}</td>
        <td>{{ $item->evidence_notes ?? '' }}{{ $item->auditor_notes ? (' | ' . $item->auditor_notes) : '' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
@endforeach
@endif

{{-- ================================================================ --}}
{{-- SECTION 5: NON-CONFORMITY REGISTER --}}
{{-- ================================================================ --}}
<div class="page-break"></div>
<div class="section-title" style="background:#7b2d3e;">5. NON-CONFORMITY REGISTER</div>
@if($audit->nonConformities->isEmpty())
  <p style="font-size:8.5pt;color:#666;padding:6px 0;">No non-conformities recorded for this audit.</p>
@else
<table class="nc-table">
  <thead>
    <tr>
      <th style="width:8%;">NC #</th>
      <th style="width:9%;">Type</th>
      <th style="width:11%;">Clause</th>
      <th>Description</th>
      <th style="width:10%;">Risk</th>
      <th style="width:12%;">Assigned To</th>
      <th style="width:9%;">Due Date</th>
      <th style="width:9%;">Status</th>
    </tr>
  </thead>
  <tbody>
  @foreach($audit->nonConformities->sortBy('nc_number') as $nc)
    @php
      $riskClass = match($nc->risk_level) {
        'high'   => 'risk-high',
        'medium' => 'risk-medium',
        'low'    => 'risk-low',
        default  => '',
      };
      $riskLabel = $nc->risk_score ? ($nc->risk_level ? strtoupper($nc->risk_level) . ' (' . $nc->risk_score . ')' : '—') : '—';
    @endphp
    <tr>
      <td><strong>{{ $nc->nc_number }}</strong></td>
      <td>{{ \App\Models\AuditNonConformity::NC_TYPE_LABELS[$nc->nc_type] ?? $nc->nc_type }}</td>
      <td>{{ $nc->clause_reference ?? '—' }}</td>
      <td>{{ \Illuminate\Support\Str::limit($nc->description, 120) }}</td>
      <td class="{{ $riskClass }}">{{ $riskLabel }}</td>
      <td>{{ $nc->assignedTo?->name ?? '—' }}</td>
      <td>{{ $nc->due_date?->format('d M Y') ?? '—' }}</td>
      <td>{{ \App\Models\AuditNonConformity::STATUS_LABELS[$nc->status] ?? $nc->status }}</td>
    </tr>
  @endforeach
  </tbody>
</table>

{{-- RCA Detail for major/critical NCs --}}
@php
  $rcaNcs = $audit->nonConformities->whereIn('nc_type', ['major', 'critical'])->where('rca_method', '!=', 'none');
@endphp
@if($rcaNcs->isNotEmpty())
<div class="section-title" style="background:#7b2d3e;font-size:8.5pt;">5b. ROOT CAUSE ANALYSIS — MAJOR / CRITICAL NCs</div>
@foreach($rcaNcs as $nc)
  <div style="margin-bottom:8px;border:1px solid #e0d0d0;padding:8px;border-radius:3px;font-size:8pt;">
    <strong>{{ $nc->nc_number }} — {{ \App\Models\AuditNonConformity::NC_TYPE_LABELS[$nc->nc_type] ?? '' }}</strong>
    &nbsp;|&nbsp; RCA Method: {{ \App\Models\AuditNonConformity::RCA_METHOD_LABELS[$nc->rca_method] ?? '' }}<br>
    @if($nc->why_1)<div style="margin-top:3px;"><em>Why 1:</em> {{ $nc->why_1 }}</div>@endif
    @if($nc->why_2)<div><em>Why 2:</em> {{ $nc->why_2 }}</div>@endif
    @if($nc->why_3)<div><em>Why 3:</em> {{ $nc->why_3 }}</div>@endif
    @if($nc->why_4)<div><em>Why 4:</em> {{ $nc->why_4 }}</div>@endif
    @if($nc->why_5)<div><em>Why 5 (Root cause):</em> <strong>{{ $nc->why_5 }}</strong></div>@endif
    @if($nc->root_cause_summary)<div style="margin-top:3px;"><strong>Summary:</strong> {{ $nc->root_cause_summary }}</div>@endif
    @if($nc->fishbone_people || $nc->fishbone_process)
    <div style="margin-top:4px;">
      <em>Fishbone:</em>
      @foreach(['people'=>'People','process'=>'Process','equipment'=>'Equipment','material'=>'Material','environment'=>'Environment','management'=>'Management'] as $field => $label)
        @if($nc->{'fishbone_'.$field}) <strong>{{ $label }}:</strong> {{ $nc->{'fishbone_'.$field} }} &nbsp; @endif
      @endforeach
    </div>
    @endif
  </div>
@endforeach
@endif
@endif

{{-- ================================================================ --}}
{{-- SECTION 6: CAPA REGISTER --}}
{{-- ================================================================ --}}
<div class="section-title" style="background:#4a3080;">6. CORRECTIVE &amp; PREVENTIVE ACTION (CAPA) REGISTER</div>
@if($audit->amsCapaActions->isEmpty())
  <p style="font-size:8.5pt;color:#666;padding:6px 0;">No CAPA actions recorded for this audit.</p>
@else
<table class="capa-table">
  <thead>
    <tr>
      <th style="width:9%;">CAPA #</th>
      <th style="width:8%;">Type</th>
      <th style="width:8%;">Linked NC</th>
      <th>Description</th>
      <th style="width:13%;">Responsible</th>
      <th style="width:9%;">Target Date</th>
      <th style="width:9%;">Status</th>
      <th style="width:10%;">Verification</th>
    </tr>
  </thead>
  <tbody>
  @foreach($audit->amsCapaActions->sortBy('action_number') as $capa)
    <tr>
      <td><strong>{{ $capa->action_number }}</strong></td>
      <td>{{ \App\Models\AuditCapaAction::ACTION_TYPE_LABELS[$capa->action_type] ?? $capa->action_type }}</td>
      <td>{{ $capa->nc?->nc_number ?? '—' }}</td>
      <td>{{ \Illuminate\Support\Str::limit($capa->description, 100) }}</td>
      <td>{{ $capa->responsiblePerson?->name ?? '—' }}</td>
      <td style="{{ $capa->is_overdue ? 'color:#721c24;font-weight:bold;' : '' }}">
        {{ $capa->target_date?->format('d M Y') ?? '—' }}
      </td>
      <td>{{ \App\Models\AuditCapaAction::STATUS_LABELS[$capa->status] ?? $capa->status }}</td>
      <td>{{ \App\Models\AuditCapaAction::VERIFICATION_LABELS[$capa->verification_status] ?? '—' }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
@endif

{{-- ================================================================ --}}
{{-- SECTION 7: AUDIT SUMMARY --}}
{{-- ================================================================ --}}
<div class="section-title">7. AUDIT SUMMARY &amp; CONCLUSIONS</div>
@if($audit->opening_meeting_notes)
<div class="narrative"><strong>Opening Meeting:</strong> {{ $audit->opening_meeting_notes }}</div>
@endif
@if($audit->closing_meeting_notes)
<div class="narrative"><strong>Closing Meeting:</strong> {{ $audit->closing_meeting_notes }}</div>
@endif
@if($audit->summary)
<div class="narrative"><strong>Conclusion:</strong> {{ $audit->summary }}</div>
@endif
@if($audit->closure_verification_notes)
<div class="narrative"><strong>Closure Verification:</strong> {{ $audit->closure_verification_notes }}
@if($audit->closure_date)&nbsp;(Date: {{ $audit->closure_date->format('d M Y') }})@endif</div>
@endif

{{-- ================================================================ --}}
{{-- SIGNATURE BLOCK --}}
{{-- ================================================================ --}}
<table class="sig-table" style="margin-top:18px;">
  <tr>
    <td>
      <div class="sig-line">Internal Auditor</div>
      <div style="font-size:7.5pt;color:#555;margin-top:3px;">Name / Date</div>
    </td>
    <td>
      <div class="sig-line">Lead Auditor — {{ $audit->leadAuditor?->name ?? '_______________' }}</div>
      <div style="font-size:7.5pt;color:#555;margin-top:3px;">Signature / Date</div>
    </td>
    <td>
      <div class="sig-line">Auditee Representative</div>
      <div style="font-size:7.5pt;color:#555;margin-top:3px;">Name / Designation</div>
    </td>
    <td>
      <div class="sig-line">Approved By — {{ $audit->approvedBy?->name ?? '_______________' }}</div>
      @if($audit->approved_at)<div style="font-size:7.5pt;color:#555;margin-top:3px;">{{ $audit->approved_at->format('d M Y') }}</div>@endif
    </td>
  </tr>
</table>

<div class="footer">
  NOVAREX Integrated HSE ERP &nbsp;|&nbsp; AMS Report: {{ $audit->audit_reference }}
  &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
  &nbsp;|&nbsp; <em>"An audit is only complete when all findings are closed, verified, and their effectiveness confirmed."</em>
  &nbsp;|&nbsp; Ref: ISO 19011:2018
</div>

</body>
</html>
