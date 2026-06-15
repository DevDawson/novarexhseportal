@extends('pdf.layout')

@section('doc-title', 'Internal Audit Report')
@section('doc-ref', $audit->audit_reference)

@php
    $findingColors = [
        'major_nonconformity' => 'danger',
        'minor_nonconformity' => 'warning',
        'opportunity_for_improvement' => 'info',
        'observation' => 'gray',
        'conformity' => 'success',
    ];
    $statusColors = [
        'open' => 'danger',
        'action_planned' => 'warning',
        'closed' => 'success',
        'verified' => 'success',
    ];
@endphp

@section('content')

<div class="section">
    <div class="section-title">Audit Information</div>
    <div class="grid-2">
        <div class="field"><label>Audit Reference</label><span>{{ $audit->audit_reference }}</span></div>
        <div class="field"><label>Audit Date</label><span>{{ $audit->audit_date?->format('d M Y') }}</span></div>
        <div class="field"><label>Audit Type</label><span>{{ \App\Models\InternalAudit::AUDIT_TYPE_LABELS[$audit->audit_type] ?? $audit->audit_type }}</span></div>
        <div class="field"><label>Standard</label><span>{{ \App\Models\InternalAudit::STANDARD_LABELS[$audit->standard] ?? $audit->standard }}{{ $audit->standard_other ? ' ('.$audit->standard_other.')' : '' }}</span></div>
        <div class="field"><label>Lead Auditor</label><span>{{ $audit->leadAuditor?->name ?? '—' }}</span></div>
        <div class="field"><label>Status</label><span>{{ \App\Models\InternalAudit::STATUS_LABELS[$audit->status] ?? $audit->status }}</span></div>
        <div class="field"><label>Project / Area</label><span>{{ $audit->project?->title ?? $audit->department?->name ?? 'Company-wide' }}</span></div>
        <div class="field"><label>Team Members</label><span>{{ $audit->teamMembers->pluck('name')->implode(', ') ?: '—' }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Scope</label><span>{{ $audit->scope }}</span></div>
    </div>
</div>

@if($audit->summary)
<div class="section">
    <div class="section-title">Executive Summary</div>
    <p style="font-size:9px; line-height:1.6; padding: 0 4px;">{{ $audit->summary }}</p>
</div>
@endif

<div class="section">
    <div class="section-title">Audit Findings ({{ $audit->findings->count() }} total, {{ $audit->non_conformity_count }} NC)</div>
    @if($audit->findings->isEmpty())
        <p style="font-size:9px; color:#6b7280; padding:8px;">No findings recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:8%">#</th>
                <th style="width:10%">Clause</th>
                <th style="width:18%">Type</th>
                <th style="width:34%">Description</th>
                <th style="width:18%">Corrective Action</th>
                <th style="width:12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audit->findings as $i => $finding)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $finding->clause_reference ?? '—' }}</td>
                <td><span class="badge badge-{{ $findingColors[$finding->finding_type] ?? 'gray' }}">{{ \App\Models\AuditFinding::FINDING_TYPE_LABELS[$finding->finding_type] ?? $finding->finding_type }}</span></td>
                <td>{{ $finding->description }}</td>
                <td>{{ $finding->corrective_action ?? '—' }}</td>
                <td><span class="badge badge-{{ $statusColors[$finding->status] ?? 'gray' }}">{{ \App\Models\AuditFinding::STATUS_LABELS[$finding->status] ?? $finding->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="sig-row">
    <div class="sig-box"><label>Lead Auditor</label><br><br><br><span>{{ $audit->leadAuditor?->name }}</span></div>
    <div class="sig-box"><label>Auditee Representative</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Management Representative</label><br><br><br><span>Name &amp; Signature</span></div>
</div>

@endsection
