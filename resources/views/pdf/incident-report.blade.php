@extends('pdf.layout')

@section('doc-title', 'Incident Investigation Report')
@section('doc-ref', 'INC-' . str_pad($incident->id, 5, '0', STR_PAD_LEFT))

@php
    $severityColors = ['near_miss'=>'gray','first_aid'=>'info','medical_treatment'=>'warning','lost_time'=>'high','fatality'=>'critical','environmental'=>'warning','property_damage'=>'warning'];
    $riskColors = ['low'=>'low','medium'=>'medium','high'=>'high','critical'=>'critical'];
    $color = $riskColors[$riskLevel] ?? 'gray';
@endphp

@section('content')

<div class="section">
    <div class="section-title">Incident Details</div>
    <div class="grid-2">
        <div class="field"><label>Incident Date</label><span>{{ $incident->incident_date?->format('d M Y') }}</span></div>
        <div class="field"><label>Reported By</label><span>{{ $incident->reportedBy?->name ?? '—' }}</span></div>
        <div class="field"><label>Project / Location</label><span>{{ $incident->project?->title ?? 'Company-wide' }}{{ $incident->location ? ' — '.$incident->location : '' }}</span></div>
        <div class="field"><label>Incident Type</label><span>{{ ucwords(str_replace('_',' ', $incident->incident_type)) }}</span></div>
        <div class="field"><label>Severity</label><span>{{ ucwords(str_replace('_',' ', $incident->severity ?? '—')) }}</span></div>
        <div class="field"><label>Risk Score</label><span><span class="badge badge-{{ $color }}">{{ $incident->risk_score }} — {{ ucfirst($riskLevel) }}</span></span></div>
        <div class="field" style="grid-column:span 2"><label>Description</label><span>{{ $incident->description }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Immediate Action Taken</label><span>{{ $incident->immediate_action ?? '—' }}</span></div>
    </div>
</div>

@if($incident->root_cause || $incident->why_1)
<div class="section">
    <div class="section-title">Root Cause Analysis</div>
    @if($incident->root_cause)
        <div class="field"><label>Root Cause</label><span>{{ $incident->root_cause }}</span></div>
    @endif
    @if($incident->why_1)
        <table style="margin-top:6px;">
            <tr><th style="width:15%">Why 1</th><td>{{ $incident->why_1 }}</td></tr>
            @if($incident->why_2)<tr><th>Why 2</th><td>{{ $incident->why_2 }}</td></tr>@endif
            @if($incident->why_3)<tr><th>Why 3</th><td>{{ $incident->why_3 }}</td></tr>@endif
            @if($incident->why_4)<tr><th>Why 4</th><td>{{ $incident->why_4 }}</td></tr>@endif
            @if($incident->why_5)<tr><th>Why 5</th><td>{{ $incident->why_5 }}</td></tr>@endif
        </table>
    @endif
</div>
@endif

@if($incident->corrective_actions || $incident->corrective_actions_plan)
<div class="section">
    <div class="section-title">Corrective Actions</div>
    @if($incident->corrective_actions)
        <div class="field"><label>Corrective Actions</label><span>{{ $incident->corrective_actions }}</span></div>
    @endif
    @php $plan = $incident->corrective_actions_plan ?? []; @endphp
    @if(count($plan))
        <table style="margin-top:6px;">
            <thead><tr><th>#</th><th>Action</th><th>Responsible</th><th>Target Date</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($plan as $i => $row)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $row['action'] ?? '—' }}</td>
                    <td>{{ $row['responsible'] ?? '—' }}</td>
                    <td>{{ $row['target_date'] ?? '—' }}</td>
                    <td>{{ $row['status'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endif

<div class="section">
    <div class="section-title">Investigation Status</div>
    <div class="grid-3">
        <div class="field"><label>Report Status</label><span>{{ ucwords(str_replace('_',' ',$incident->status)) }}</span></div>
        <div class="field"><label>Investigation Status</label><span>{{ ucwords(str_replace('_',' ',$incident->investigation_status ?? '—')) }}</span></div>
        <div class="field"><label>Closed Date</label><span>{{ $incident->closed_date?->format('d M Y') ?? '—' }}</span></div>
    </div>
</div>

<div class="sig-row">
    <div class="sig-box"><label>Reported By</label><br><br><br><span>{{ $incident->reportedBy?->name }}</span></div>
    <div class="sig-box"><label>HSE Representative</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Managing Director</label><br><br><br><span>Name &amp; Signature</span></div>
</div>

@endsection
