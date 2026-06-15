@extends('pdf.layout')

@section('doc-title', 'Environmental Aspect & Impact Assessment')
@section('doc-ref', 'EMS-' . str_pad($aspect->id, 4, '0', STR_PAD_LEFT))

@php
    $sigColors = ['low'=>'low','medium'=>'medium','high'=>'high','critical'=>'critical'];
    $color = $sigColors[$sigLevel] ?? 'gray';
    $statusBadge = ['significant'=>'danger','not_significant'=>'success','controlled'=>'info'];
@endphp

@section('content')

<div class="section">
    <div class="section-title">Aspect & Impact Identification</div>
    <div class="grid-2">
        <div class="field"><label>Project / Facility</label><span>{{ $aspect->project?->title ?? 'Company-wide' }}</span></div>
        <div class="field"><label>Impact Category</label><span>{{ \App\Models\EnvironmentalAspect::IMPACT_CATEGORY_LABELS[$aspect->impact_category] ?? $aspect->impact_category }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Activity / Process</label><span>{{ $aspect->activity_process }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Environmental Aspect</label><span>{{ $aspect->environmental_aspect }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Environmental Impact</label><span>{{ $aspect->environmental_impact }}</span></div>
    </div>
</div>

<div class="section">
    <div class="section-title">Significance Evaluation</div>
    <div class="grid-3">
        <div class="field"><label>Likelihood (L)</label><span>{{ $aspect->likelihood }} / 5</span></div>
        <div class="field"><label>Severity (S)</label><span>{{ $aspect->severity }} / 5</span></div>
        <div class="field"><label>Significance Score (L×S)</label><span><span class="badge badge-{{ $color }}">{{ $aspect->significance_score }} — {{ ucfirst($sigLevel) }}</span></span></div>
        <div class="field"><label>Status</label><span><span class="badge badge-{{ $statusBadge[$aspect->status] ?? 'gray' }}">{{ \App\Models\EnvironmentalAspect::STATUS_LABELS[$aspect->status] ?? $aspect->status }}</span></span></div>
        <div class="field"><label>Legal Requirement Ref</label><span>{{ $aspect->legal_requirement_ref ?? '—' }}</span></div>
        <div class="field"><label>Review Date</label><span>{{ $aspect->review_date?->format('d M Y') ?? '—' }}</span></div>
    </div>
</div>

<div class="section">
    <div class="section-title">Controls & Responsibility</div>
    <div class="grid-2">
        <div class="field"><label>Responsible Person</label><span>{{ $aspect->responsiblePerson?->name ?? '—' }}</span></div>
        <div class="field" style="grid-column:span 2"><label>Existing Controls</label><span>{{ $aspect->existing_controls ?? '—' }}</span></div>
    </div>
</div>

<div class="sig-row">
    <div class="sig-box"><label>Prepared By (EMS Officer)</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Reviewed By (HSE Lead)</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Approved By (MD)</label><br><br><br><span>Name &amp; Signature</span></div>
</div>

@endsection
