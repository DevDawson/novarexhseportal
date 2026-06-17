<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PTW Certificate – {{ $permit->permit_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1a1a1a; background: #fff; }
  .page { padding: 12mm 14mm; }

  /* Header */
  .header { display: table; width: 100%; border: 2px solid #1e3a5f; margin-bottom: 6px; }
  .header-logo { display: table-cell; width: 18%; background: #1e3a5f; color: #fff; padding: 8px; vertical-align: middle; text-align: center; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
  .header-title { display: table-cell; vertical-align: middle; padding: 8px 12px; }
  .header-title h1 { font-size: 15px; font-weight: bold; color: #1e3a5f; margin-bottom: 2px; }
  .header-title p { font-size: 8px; color: #555; }
  .header-meta { display: table-cell; width: 22%; background: #f7f7f7; padding: 8px; vertical-align: middle; text-align: right; }
  .header-meta .permit-no { font-size: 13px; font-weight: bold; color: #1e3a5f; }
  .header-meta .risk-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 10px; font-weight: bold; margin-top: 4px; }
  .risk-high { background: #dc2626; color: #fff; }
  .risk-medium { background: #d97706; color: #fff; }
  .risk-low { background: #16a34a; color: #fff; }

  /* Status banner */
  .status-banner { text-align: center; padding: 4px; font-size: 10px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 6px; border-radius: 2px; }
  .status-active   { background: #d1fae5; color: #065f46; }
  .status-approved { background: #dbeafe; color: #1e40af; }
  .status-closed   { background: #e5e7eb; color: #374151; }
  .status-draft    { background: #fef3c7; color: #92400e; }
  .status-default  { background: #fef3c7; color: #92400e; }

  /* Grid sections */
  .section { margin-bottom: 6px; border: 1px solid #d1d5db; border-radius: 2px; }
  .section-title { background: #1e3a5f; color: #fff; padding: 4px 8px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
  .section-body { padding: 6px 8px; }
  .grid-2 { display: table; width: 100%; }
  .grid-2 .col { display: table-cell; width: 50%; vertical-align: top; padding: 2px 4px 2px 0; }
  .grid-3 { display: table; width: 100%; }
  .grid-3 .col { display: table-cell; width: 33.33%; vertical-align: top; padding: 2px 4px 2px 0; }
  .grid-4 { display: table; width: 100%; }
  .grid-4 .col { display: table-cell; width: 25%; vertical-align: top; padding: 2px 4px 2px 0; }
  .field-label { font-size: 7.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
  .field-value { font-size: 9px; font-weight: bold; color: #111; border-bottom: 1px dotted #d1d5db; padding-bottom: 2px; min-height: 14px; }
  .field-value.empty { font-weight: normal; color: #9ca3af; font-style: italic; }

  /* Gas test table */
  .gas-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
  .gas-table th { background: #374151; color: #fff; padding: 3px 6px; font-size: 8px; text-align: center; }
  .gas-table td { border: 1px solid #d1d5db; padding: 3px 6px; font-size: 8.5px; text-align: center; }
  .gas-table .limit { font-size: 7px; color: #6b7280; }

  /* Checklist */
  .checklist-table { width: 100%; border-collapse: collapse; }
  .checklist-table th { background: #374151; color: #fff; padding: 3px 6px; font-size: 8px; text-align: left; }
  .checklist-table td { border: 1px solid #e5e7eb; padding: 3px 6px; font-size: 8.5px; vertical-align: middle; }
  .checklist-table tr:nth-child(even) td { background: #f9fafb; }
  .tick { color: #16a34a; font-weight: bold; font-size: 11px; }
  .cross { color: #dc2626; font-weight: bold; font-size: 11px; }

  /* PPE */
  .ppe-list { column-count: 2; column-gap: 8px; padding: 2px 0; }
  .ppe-item { font-size: 8.5px; margin-bottom: 2px; break-inside: avoid; }
  .ppe-item::before { content: "✓ "; color: #16a34a; font-weight: bold; }

  /* Safety toggles */
  .toggle-row { display: table; width: 100%; margin-bottom: 3px; }
  .toggle-label { display: table-cell; width: 45%; font-size: 8.5px; vertical-align: middle; }
  .toggle-required { display: table-cell; width: 27%; font-size: 8.5px; vertical-align: middle; }
  .toggle-confirmed { display: table-cell; width: 28%; font-size: 8.5px; vertical-align: middle; }
  .badge { display: inline-block; padding: 1px 6px; border-radius: 2px; font-size: 8px; font-weight: bold; }
  .badge-yes  { background: #d1fae5; color: #065f46; }
  .badge-no   { background: #fee2e2; color: #991b1b; }
  .badge-na   { background: #e5e7eb; color: #374151; }
  .badge-verified   { background: #dbeafe; color: #1e40af; }
  .badge-unverified { background: #fef3c7; color: #92400e; }

  /* Approvals table */
  .approval-table { width: 100%; border-collapse: collapse; }
  .approval-table th { background: #1e3a5f; color: #fff; padding: 4px 6px; font-size: 8px; }
  .approval-table td { border: 1px solid #d1d5db; padding: 4px 6px; font-size: 8.5px; vertical-align: middle; }
  .approval-table .sig-box { height: 22px; border-top: 1px solid #111; margin-top: 6px; }

  /* Signatures footer */
  .sig-section { display: table; width: 100%; margin-top: 4px; }
  .sig-cell { display: table-cell; width: 25%; padding: 4px 8px; text-align: center; }
  .sig-line { border-top: 1px solid #374151; padding-top: 4px; margin-top: 20px; font-size: 7.5px; color: #374151; }
  .sig-name { font-weight: bold; font-size: 8px; color: #1e3a5f; }

  /* Footer */
  .doc-footer { text-align: center; font-size: 7px; color: #9ca3af; margin-top: 8px; border-top: 1px solid #e5e7eb; padding-top: 4px; }
  .page-break { page-break-before: always; }
  .text-right { text-align: right; }
  .mt2 { margin-top: 2px; }
  .mt4 { margin-top: 4px; }
  .bold { font-weight: bold; }
</style>
</head>
<body>
<div class="page">

@include('filament.pdf.partials.letterhead')

{{-- HEADER --}}
<div class="header">
  <div class="header-logo">{{ \App\Models\Setting::companyName() }}</div>
  <div class="header-title">
    <h1>PERMIT TO WORK CERTIFICATE</h1>
    <p>ISO 45001:2018 Compliant — Authorised Work Permit System</p>
    <p>Document Reference: PTW-FORM-001 | Revision: 02 | Classification: Controlled</p>
  </div>
  <div class="header-meta">
    <div class="permit-no">{{ $permit->permit_number }}</div>
    @php
      $rc = $permit->risk_classification ?? 'low';
      $rcLabel = strtoupper($rc);
    @endphp
    <div>
      <span class="risk-badge risk-{{ $rc }}">{{ $rcLabel }} RISK</span>
    </div>
    @if ($permit->risk_score)
      <div style="font-size:7.5px;color:#555;margin-top:2px;">L×S Score: {{ $permit->risk_score }}</div>
    @endif
  </div>
</div>

{{-- STATUS BANNER --}}
@php
  $statusClass = match($permit->status) {
    'active'   => 'status-active',
    'approved' => 'status-approved',
    'closed'   => 'status-closed',
    'draft'    => 'status-draft',
    default    => 'status-default',
  };
  $statusLabel = \App\Models\PermitToWork::STATUS_LABELS[$permit->status] ?? strtoupper($permit->status);
@endphp
<div class="status-banner {{ $statusClass }}">
  STATUS: {{ strtoupper($statusLabel) }}
</div>

{{-- SECTION 1 — PERMIT INFORMATION --}}
<div class="section">
  <div class="section-title">1. Permit Information</div>
  <div class="section-body">
    <div class="grid-4">
      <div class="col">
        <div class="field-label">Permit Type</div>
        <div class="field-value">{{ \App\Models\PermitToWork::PERMIT_TYPE_LABELS[$permit->permit_type] ?? $permit->permit_type }}</div>
      </div>
      <div class="col">
        <div class="field-label">Work Order / Job No.</div>
        <div class="field-value {{ $permit->work_order_id ? '' : 'empty' }}">{{ $permit->work_order_id ?? 'N/A' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Issue Date</div>
        <div class="field-value">{{ $permit->created_at?->format('d M Y') ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Project</div>
        <div class="field-value {{ $permit->project ? '' : 'empty' }}">{{ $permit->project?->title ?? 'Company Premises' }}</div>
      </div>
    </div>
    <div class="grid-3 mt2">
      <div class="col">
        <div class="field-label">Work Location / Equipment</div>
        <div class="field-value">{{ $permit->location }}</div>
      </div>
      <div class="col">
        <div class="field-label">Site Area / Zone</div>
        <div class="field-value {{ $permit->site_area ? '' : 'empty' }}">{{ $permit->site_area ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Department</div>
        <div class="field-value {{ $permit->department ? '' : 'empty' }}">{{ $permit->department?->name ?? '—' }}</div>
      </div>
    </div>
    <div class="mt2">
      <div class="field-label">Description of Work</div>
      <div style="font-size:8.5px;padding:3px 0;border-bottom:1px dotted #d1d5db;">{{ $permit->description ?? '—' }}</div>
    </div>
  </div>
</div>

{{-- SECTION 2 — VALIDITY & TIMING --}}
<div class="section">
  <div class="section-title">2. Validity Period & Timing</div>
  <div class="section-body">
    <div class="grid-4">
      <div class="col">
        <div class="field-label">Valid From (Start)</div>
        <div class="field-value bold">{{ $permit->valid_from?->format('d M Y H:i') ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Valid To (Expiry)</div>
        <div class="field-value bold">{{ $permit->valid_to?->format('d M Y H:i') ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Estimated Duration</div>
        <div class="field-value">{{ $permit->duration_estimate ? $permit->duration_estimate . ' hours' : '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Actual Start / Completion</div>
        <div class="field-value">
          {{ $permit->actual_start?->format('d M Y H:i') ?? '—' }}
          / {{ $permit->actual_completion?->format('d M Y H:i') ?? '—' }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- SECTION 3 — PERSONNEL --}}
<div class="section">
  <div class="section-title">3. Personnel</div>
  <div class="section-body">
    <div class="grid-4">
      <div class="col">
        <div class="field-label">Permit Holder / Performer</div>
        <div class="field-value">{{ $permit->requestedBy?->name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Work Supervisor</div>
        <div class="field-value">{{ $permit->supervisor?->name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Issuer / Authorizer</div>
        <div class="field-value">{{ $permit->issuedBy?->name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Area Authority / Safety Officer</div>
        <div class="field-value">{{ $permit->areaAuthority?->name ?? '—' }}</div>
      </div>
    </div>
    <div class="grid-3 mt2">
      <div class="col">
        <div class="field-label">Contractor Company</div>
        <div class="field-value {{ $permit->contractor_company ? '' : 'empty' }}">{{ $permit->contractor_company ?? 'N/A (Own workforce)' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Contractor Representative</div>
        <div class="field-value {{ $permit->contractor_name ? '' : 'empty' }}">{{ $permit->contractor_name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Number of Workers</div>
        <div class="field-value">{{ $permit->number_of_workers ?? '—' }}</div>
      </div>
    </div>
  </div>
</div>

{{-- SECTION 4 — RISK ASSESSMENT --}}
<div class="section">
  <div class="section-title">4. Risk Assessment</div>
  <div class="section-body">
    <div class="grid-4">
      <div class="col">
        <div class="field-label">Likelihood (L)</div>
        <div class="field-value">{{ $permit->likelihood ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Severity (S)</div>
        <div class="field-value">{{ $permit->severity ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Risk Score (L×S)</div>
        <div class="field-value bold">{{ $permit->risk_score ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Risk Classification</div>
        <div class="field-value bold">
          @if ($permit->risk_classification)
            <span class="risk-badge risk-{{ $permit->risk_classification }}">{{ strtoupper($permit->risk_classification) }}</span>
          @else —
          @endif
        </div>
      </div>
    </div>
    @if ($permit->linkedHazard || $permit->linkedHazopNode)
    <div class="grid-2 mt2">
      @if ($permit->linkedHazard)
      <div class="col">
        <div class="field-label">Linked HAZID Entry</div>
        <div class="field-value">{{ $permit->linkedHazard->hazard_id }} — {{ \Str::limit($permit->linkedHazard->hazard_description, 60) }}</div>
      </div>
      @endif
      @if ($permit->linkedHazopNode)
      <div class="col">
        <div class="field-label">Linked HAZOP Node</div>
        <div class="field-value">{{ $permit->linkedHazopNode->study?->study_ref }} / Node {{ $permit->linkedHazopNode->node_number }}</div>
      </div>
      @endif
    </div>
    @endif
  </div>
</div>

{{-- SECTION 5 — HAZARDS & CONTROLS --}}
<div class="section">
  <div class="section-title">5. Hazards Identified & Control Measures</div>
  <div class="section-body">
    <div class="grid-2">
      <div class="col">
        <div class="field-label">Hazards Identified</div>
        <div style="font-size:8.5px;padding:3px 0;min-height:30px;">{{ $permit->hazards_identified ?? 'None documented.' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Precautions / Control Measures</div>
        <div style="font-size:8.5px;padding:3px 0;min-height:30px;">{{ $permit->precautions_taken ?? 'None documented.' }}</div>
      </div>
    </div>
    @if (!empty($permit->ppe_required))
    <div class="mt2">
      <div class="field-label">PPE Required</div>
      <div class="ppe-list mt2">
        @foreach ($permit->ppe_required_labels as $ppe)
          <div class="ppe-item">{{ $ppe }}</div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>

{{-- SECTION 6 — SAFETY CONTROLS --}}
<div class="section">
  <div class="section-title">6. Safety Controls & Verification</div>
  <div class="section-body">
    <div class="toggle-row" style="background:#f3f4f6;padding:3px 6px;font-size:8px;font-weight:bold;border-bottom:1px solid #d1d5db;">
      <div class="toggle-label">Control</div>
      <div class="toggle-required">Required?</div>
      <div class="toggle-confirmed">Confirmed in Place?</div>
    </div>

    @php
      $controls = [
        ['label' => 'Isolation / Lock-Out Tag-Out (LOTO)', 'req' => $permit->isolation_required, 'conf' => $permit->loto_verified],
        ['label' => 'Gas Testing (Atmospheric)',           'req' => $permit->gas_test_required,  'conf' => $permit->gas_testing_verified],
        ['label' => 'Fire Watch',                          'req' => $permit->fire_watch_required, 'conf' => $permit->fire_watch_confirmed],
        ['label' => 'Barricading / Exclusion Zone',       'req' => $permit->barricading_required,'conf' => $permit->barricading_confirmed],
        ['label' => 'Emergency Standby',                   'req' => $permit->emergency_standby_required,'conf' => $permit->emergency_standby_confirmed],
      ];
    @endphp

    @foreach ($controls as $ctrl)
    <div class="toggle-row" style="padding:3px 6px;border-bottom:1px dotted #e5e7eb;">
      <div class="toggle-label">{{ $ctrl['label'] }}</div>
      <div class="toggle-required">
        @if ($ctrl['req'])
          <span class="badge badge-yes">YES</span>
        @else
          <span class="badge badge-na">NO</span>
        @endif
      </div>
      <div class="toggle-confirmed">
        @if (!$ctrl['req'])
          <span class="badge badge-na">N/A</span>
        @elseif ($ctrl['conf'])
          <span class="badge badge-verified">VERIFIED ✓</span>
        @else
          <span class="badge badge-unverified">PENDING</span>
        @endif
      </div>
    </div>
    @endforeach

    @if ($permit->gas_test_required && !empty($permit->gas_test_results))
    <table class="gas-table mt4">
      <thead>
        <tr>
          <th>Parameter</th>
          <th>Result</th>
          <th>Safe Limit</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @php $gtr = $permit->gas_test_results ?? []; @endphp
        <tr>
          <td>Oxygen (O₂)</td>
          <td>{{ $gtr['o2'] ?? '—' }}%</td>
          <td class="limit">19.5 – 23.5%</td>
          <td>
            @php $o2 = (float)($gtr['o2'] ?? 0); @endphp
            @if ($o2 >= 19.5 && $o2 <= 23.5)
              <span class="badge badge-yes">SAFE</span>
            @elseif ($o2 > 0)
              <span class="badge badge-no">ALERT</span>
            @else —
            @endif
          </td>
        </tr>
        <tr>
          <td>Flammable Gas (LEL)</td>
          <td>{{ $gtr['lel'] ?? '—' }}%</td>
          <td class="limit">&lt; 10%</td>
          <td>
            @php $lel = (float)($gtr['lel'] ?? 0); @endphp
            @if (isset($gtr['lel'])) <span class="badge {{ $lel < 10 ? 'badge-yes' : 'badge-no' }}">{{ $lel < 10 ? 'SAFE' : 'ALERT' }}</span>
            @else —
            @endif
          </td>
        </tr>
        <tr>
          <td>Hydrogen Sulphide (H₂S)</td>
          <td>{{ $gtr['h2s'] ?? '—' }} ppm</td>
          <td class="limit">&lt; 10 ppm</td>
          <td>
            @php $h2s = (float)($gtr['h2s'] ?? 0); @endphp
            @if (isset($gtr['h2s'])) <span class="badge {{ $h2s < 10 ? 'badge-yes' : 'badge-no' }}">{{ $h2s < 10 ? 'SAFE' : 'ALERT' }}</span>
            @else —
            @endif
          </td>
        </tr>
        <tr>
          <td>Carbon Monoxide (CO)</td>
          <td>{{ $gtr['co'] ?? '—' }} ppm</td>
          <td class="limit">&lt; 35 ppm</td>
          <td>
            @php $co = (float)($gtr['co'] ?? 0); @endphp
            @if (isset($gtr['co'])) <span class="badge {{ $co < 35 ? 'badge-yes' : 'badge-no' }}">{{ $co < 35 ? 'SAFE' : 'ALERT' }}</span>
            @else —
            @endif
          </td>
        </tr>
      </tbody>
    </table>
    @if (!empty($gtr['tested_by']) || !empty($gtr['tested_at']))
    <div style="font-size:7.5px;color:#6b7280;margin-top:3px;">
      Tested by: <strong>{{ $gtr['tested_by'] ?? '—' }}</strong>
      &nbsp;|&nbsp;
      Test date/time: <strong>{{ $gtr['tested_at'] ?? '—' }}</strong>
    </div>
    @endif
    @endif

    @if ($permit->emergency_procedures)
    <div class="mt4">
      <div class="field-label">Emergency Procedures / Rescue Plan</div>
      <div style="font-size:8.5px;padding:3px 0;">{{ $permit->emergency_procedures }}</div>
    </div>
    @endif
  </div>
</div>

{{-- SECTION 7 — PERMIT CHECKLIST --}}
@if ($checklistItems->isNotEmpty())
<div class="section">
  <div class="section-title">7. Permit Pre-Condition Checklist</div>
  <div class="section-body">
    <table class="checklist-table">
      <thead>
        <tr>
          <th style="width:4%">#</th>
          <th style="width:72%">Checklist Item</th>
          <th style="width:12%;text-align:center;">Verified</th>
          <th style="width:12%">Remarks</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($checklistItems as $i => $item)
        <tr>
          <td style="text-align:center;">{{ $i + 1 }}</td>
          <td>{{ $item->item }}</td>
          <td style="text-align:center;">
            @if ($item->is_checked)
              <span class="tick">✓</span>
            @else
              <span class="cross">✗</span>
            @endif
          </td>
          <td>{{ $item->remarks ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- SECTION 8 — APPROVAL CHAIN --}}
<div class="section">
  <div class="section-title">8. Approval Chain</div>
  <div class="section-body">
    @if ($approvals->isNotEmpty())
    <table class="approval-table">
      <thead>
        <tr>
          <th style="width:22%">Stage</th>
          <th style="width:22%">Approver</th>
          <th style="width:15%;text-align:center;">Decision</th>
          <th style="width:20%">Decided At</th>
          <th style="width:21%">Comments</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($approvals as $approval)
        <tr>
          <td>{{ \App\Models\PtwApproval::STAGE_LABELS[$approval->approval_stage] ?? $approval->approval_stage }}</td>
          <td>{{ $approval->approver?->name ?? '—' }}</td>
          <td style="text-align:center;">
            @php
              $dc = match($approval->decision) {
                'approved' => 'badge-yes',
                'rejected' => 'badge-no',
                'modification_requested' => 'badge-unverified',
                default => 'badge-na',
              };
            @endphp
            <span class="badge {{ $dc }}">{{ strtoupper($approval->decision) }}</span>
          </td>
          <td>{{ $approval->decided_at?->format('d M Y H:i') ?? 'Pending' }}</td>
          <td>{{ \Str::limit($approval->comments, 50) ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div style="font-size:8.5px;color:#9ca3af;font-style:italic;padding:4px 0;">No approval records recorded yet.</div>
    @endif

    @if ($permit->final_approved_by_id || $permit->approved_at)
    <div class="grid-2 mt4">
      <div class="col">
        <div class="field-label">Final Approval By</div>
        <div class="field-value">{{ $permit->finalApprovedBy?->name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Final Approval Date/Time</div>
        <div class="field-value">{{ $permit->approved_at?->format('d M Y H:i') ?? '—' }}</div>
      </div>
    </div>
    @endif
  </div>
</div>

{{-- SECTION 9 — CLOSURE --}}
@if (in_array($permit->status, ['closed', 'suspended', 'cancelled', 'expired']))
<div class="section">
  <div class="section-title">9. Closure & Completion</div>
  <div class="section-body">
    <div class="grid-4">
      <div class="col">
        <div class="field-label">Closed / Completed By</div>
        <div class="field-value">{{ $permit->closeoutBy?->name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Closeout Date/Time</div>
        <div class="field-value">{{ $permit->closeout_at?->format('d M Y H:i') ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Completion Confirmed By</div>
        <div class="field-value">{{ $permit->completionConfirmedBy?->name ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="field-label">Completion Date</div>
        <div class="field-value">{{ $permit->completion_date?->format('d M Y') ?? '—' }}</div>
      </div>
    </div>
    @if ($permit->closeout_notes)
    <div class="mt2">
      <div class="field-label">Closeout Notes</div>
      <div style="font-size:8.5px;padding:3px 0;">{{ $permit->closeout_notes }}</div>
    </div>
    @endif
    @if ($permit->final_inspection_notes)
    <div class="mt2">
      <div class="field-label">Final Site Inspection Notes</div>
      <div style="font-size:8.5px;padding:3px 0;">{{ $permit->final_inspection_notes }}</div>
    </div>
    @endif
    @if ($permit->suspension_reason)
    <div class="mt2">
      <div class="field-label">Suspension Reason</div>
      <div style="font-size:8.5px;padding:3px 0;color:#92400e;">{{ $permit->suspension_reason }}</div>
    </div>
    @endif
  </div>
</div>
@endif

{{-- SIGNATURES --}}
<div class="section">
  <div class="section-title">Signatures & Authorisation</div>
  <div class="section-body">
    <div class="sig-section">
      <div class="sig-cell">
        <div style="height:25px;"></div>
        <div class="sig-line">
          <div class="sig-name">Permit Holder / Performer</div>
          <div>{{ $permit->requestedBy?->name ?? '___________________' }}</div>
          <div style="margin-top:2px;">Date: _________________</div>
        </div>
      </div>
      <div class="sig-cell">
        <div style="height:25px;"></div>
        <div class="sig-line">
          <div class="sig-name">Work Supervisor</div>
          <div>{{ $permit->supervisor?->name ?? '___________________' }}</div>
          <div style="margin-top:2px;">Date: _________________</div>
        </div>
      </div>
      <div class="sig-cell">
        <div style="height:25px;"></div>
        <div class="sig-line">
          <div class="sig-name">Issuer / Authorizer</div>
          <div>{{ $permit->issuedBy?->name ?? '___________________' }}</div>
          <div style="margin-top:2px;">Date: _________________</div>
        </div>
      </div>
      <div class="sig-cell">
        <div style="height:25px;"></div>
        <div class="sig-line">
          <div class="sig-name">Area Authority / HSE Officer</div>
          <div>{{ $permit->areaAuthority?->name ?? '___________________' }}</div>
          <div style="margin-top:2px;">Date: _________________</div>
        </div>
      </div>
    </div>
    <div style="text-align:center;font-size:7.5px;color:#dc2626;font-weight:bold;margin-top:6px;border:1px solid #dc2626;padding:3px;">
      ⚠ THIS PERMIT IS NOT VALID UNLESS SIGNED BY ALL AUTHORISED PARTIES ABOVE ⚠
    </div>
  </div>
</div>

{{-- FOOTER --}}
<div class="doc-footer">
  NOVAREX HSE Management System &nbsp;|&nbsp;
  Permit: {{ $permit->permit_number }} &nbsp;|&nbsp;
  Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp;
  ISO 45001:2018 — Clause 8.1.3 (Management of Change) | Clause 8.1 (Operational Planning &amp; Control)
</div>

</div><!-- /page -->
</body>
</html>
