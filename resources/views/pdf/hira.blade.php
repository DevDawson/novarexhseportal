@extends('pdf.layout')

@section('doc-title', 'Hazard Risk Assessment')
@section('doc-ref', 'HIRA-' . $hazard->id)

@php
    $levelColors = ['low'=>'low','medium'=>'medium','high'=>'high','critical'=>'critical'];
    $initColor   = $levelColors[$initLevel]  ?? 'gray';
    $residColor  = $levelColors[$residLevel] ?? 'gray';
    $controls = $hazard->additional_controls ?? [];
    $controlLabels = \App\Models\HazardRegister::CONTROL_HIERARCHY_OPTIONS;
@endphp

@section('content')

<div class="section">
    <div class="section-title">Activity & Hazard Identification</div>
    <div class="grid-2">
        <div class="field"><label>Project / Location</label><span>{{ $hazard->project?->title ?? 'Company-wide' }}</span></div>
        <div class="field"><label>Location</label><span>{{ $hazard->location ?? '—' }}</span></div>
        <div class="field"><label>Activity / Task</label><span>{{ $hazard->activity_task }}</span></div>
        <div class="field"><label>Hazard Category</label><span>{{ \App\Models\HazardRegister::HAZARD_CATEGORY_LABELS[$hazard->hazard_category] ?? $hazard->hazard_category }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Hazard Description</label><span>{{ $hazard->hazard_description }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Who Might Be Harmed</label><span>{{ $hazard->who_might_be_harmed }}</span></div>
    </div>
</div>

<div class="section">
    <div class="section-title">Initial Risk Assessment (Before Controls)</div>
    <div class="grid-3">
        <div class="field"><label>Likelihood (L)</label><span>{{ $hazard->initial_likelihood }} / 5</span></div>
        <div class="field"><label>Severity (S)</label><span>{{ $hazard->initial_severity }} / 5</span></div>
        <div class="field"><label>Risk Score (L×S)</label><span><span class="badge badge-{{ $initColor }}">{{ $hazard->initial_risk_score }} — {{ ucfirst($initLevel) }}</span></span></div>
    </div>
    <div class="field" style="margin-top:6px"><label>Existing Controls</label><span>{{ $hazard->existing_controls ?? '—' }}</span></div>
</div>

<div class="section">
    <div class="section-title">Additional Controls (Hierarchy of Controls)</div>
    @if(count($controls))
        <ul style="margin-left:14px; font-size:9px; line-height:1.8;">
            @foreach($controls as $key)
                <li>{{ $controlLabels[$key] ?? $key }}</li>
            @endforeach
        </ul>
    @endif
    @if($hazard->additional_controls_description)
        <div class="field" style="margin-top:6px"><label>Description</label><span>{{ $hazard->additional_controls_description }}</span></div>
    @endif
</div>

<div class="section">
    <div class="section-title">Residual Risk Assessment (After Controls)</div>
    <div class="grid-3">
        <div class="field"><label>Likelihood (L)</label><span>{{ $hazard->residual_likelihood }} / 5</span></div>
        <div class="field"><label>Severity (S)</label><span>{{ $hazard->residual_severity }} / 5</span></div>
        <div class="field"><label>Residual Risk Score</label><span><span class="badge badge-{{ $residColor }}">{{ $hazard->residual_risk_score }} — {{ ucfirst($residLevel) }}</span></span></div>
    </div>
</div>

<div class="section">
    <div class="section-title">Action & Review</div>
    <div class="grid-3">
        <div class="field"><label>Status</label><span>{{ \App\Models\HazardRegister::STATUS_LABELS[$hazard->status] ?? $hazard->status }}</span></div>
        <div class="field"><label>Responsible Person</label><span>{{ $hazard->responsiblePerson?->name ?? '—' }}</span></div>
        <div class="field"><label>Review Date</label><span>{{ $hazard->review_date?->format('d M Y') ?? '—' }}</span></div>
    </div>
</div>

<div class="sig-row">
    <div class="sig-box"><label>Prepared By</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Reviewed By (HSE)</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Approved By (MD)</label><br><br><br><span>Name &amp; Signature</span></div>
</div>

@endsection
