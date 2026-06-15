@extends('pdf.layout')

@section('title', 'ESIA Report — ' . $report->report_title)

@section('content')

{{-- Header Block --}}
<table class="grid-2" style="margin-bottom:0">
    <tr>
        <td><strong>Project:</strong></td>
        <td>{{ $report->project->title ?? '—' }}</td>
        <td><strong>Report Type:</strong></td>
        <td>{{ \App\Models\EsiaReport::REPORT_TYPE_LABELS[$report->report_type] ?? $report->report_type }}</td>
    </tr>
    <tr>
        <td><strong>Version:</strong></td>
        <td>{{ $report->version }}</td>
        <td><strong>Status:</strong></td>
        <td>
            <span class="badge-{{ in_array($report->status, ['approved','final']) ? 'success' : (in_array($report->status, ['rejected']) ? 'danger' : 'info') }}">
                {{ \App\Models\EsiaReport::STATUS_LABELS[$report->status] ?? $report->status }}
            </span>
        </td>
    </tr>
    <tr>
        <td><strong>Author / Lead Assessor:</strong></td>
        <td>{{ $report->author?->name ?? '—' }}</td>
        <td><strong>Date Prepared:</strong></td>
        <td>{{ $report->date_prepared?->format('d M Y') ?? '—' }}</td>
    </tr>
    @if($report->reviewed_by)
    <tr>
        <td><strong>Reviewed By:</strong></td>
        <td>{{ $report->reviewedBy?->name }}</td>
        <td><strong>Review Date:</strong></td>
        <td>{{ $report->review_date?->format('d M Y') ?? '—' }}</td>
    </tr>
    @endif
</table>

{{-- Executive Summary --}}
@if($report->executive_summary)
<div class="section-title">Executive Summary</div>
<p style="white-space:pre-wrap">{{ $report->executive_summary }}</p>
@endif

{{-- Screening Summary --}}
@if($screening)
<div class="section-title">Step 2 — Screening Result</div>
<table class="grid-3">
    <tr>
        <th>Scale Score</th><th>Sensitivity Score</th><th>Pollution Potential</th>
    </tr>
    <tr>
        <td>{{ $screening->scale }} / 5</td>
        <td>{{ $screening->sensitivity }} / 5</td>
        <td>{{ $screening->pollution_potential }} / 5</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Total Screening Score:</strong> {{ $screening->screening_score }} / 15</td>
        <td>
            <span class="badge-{{ ['A'=>'danger','B'=>'warning','C'=>'success'][$screening->category] ?? 'gray' }}">
                Category {{ $screening->category }} — {{ \App\Models\EsiaScreening::CATEGORY_LABELS[$screening->category] }}
            </span>
        </td>
    </tr>
</table>
@if($screening->screening_justification)
<p><strong>Justification:</strong> {{ $screening->screening_justification }}</p>
@endif
@endif

{{-- Scoping Issues --}}
@if($scopingIssues->count())
<div class="section-title">Step 3 — Scoping Issues ({{ $scopingIssues->count() }} total)</div>
<table width="100%" style="border-collapse:collapse;font-size:10px">
    <thead>
        <tr style="background:#1e40af;color:#fff">
            <th style="padding:4px 6px;text-align:left">Issue Type</th>
            <th style="padding:4px 6px;text-align:left">Title</th>
            <th style="padding:4px 6px;text-align:left">Expert</th>
            <th style="padding:4px 6px;text-align:center">In Scope</th>
        </tr>
    </thead>
    <tbody>
        @foreach($scopingIssues as $issue)
        <tr style="{{ $loop->odd ? 'background:#f1f5f9' : '' }}">
            <td style="padding:3px 6px">{{ \App\Models\EsiaScopingIssue::ISSUE_TYPE_LABELS[$issue->issue_type] ?? $issue->issue_type }}</td>
            <td style="padding:3px 6px">{{ $issue->issue_title }}</td>
            <td style="padding:3px 6px">{{ $issue->responsible_expert ?? '—' }}</td>
            <td style="padding:3px 6px;text-align:center">{{ $issue->included_in_scope ? '✓' : '✗' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Baseline Data Summary --}}
@if($baselineData->count())
<div class="section-title">Step 4 — Baseline Data Summary ({{ $baselineData->count() }} measurements)</div>
<table width="100%" style="border-collapse:collapse;font-size:10px">
    <thead>
        <tr style="background:#1e40af;color:#fff">
            <th style="padding:4px 6px;text-align:left">Parameter Type</th>
            <th style="padding:4px 6px;text-align:left">Parameter</th>
            <th style="padding:4px 6px;text-align:right">Value</th>
            <th style="padding:4px 6px;text-align:left">Standard</th>
            <th style="padding:4px 6px;text-align:center">Exceeds?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($baselineData as $bd)
        <tr style="{{ $loop->odd ? 'background:#f1f5f9' : '' }}{{ $bd->exceeds_limit ? ';color:#dc2626' : '' }}">
            <td style="padding:3px 6px">{{ \App\Models\EsiaBaselineData::PARAMETER_TYPE_LABELS[$bd->parameter_type] ?? $bd->parameter_type }}</td>
            <td style="padding:3px 6px">{{ $bd->parameter_name }}</td>
            <td style="padding:3px 6px;text-align:right">{{ $bd->measurement_value }} {{ $bd->unit }}</td>
            <td style="padding:3px 6px">{{ $bd->standard_limit ?? '—' }}</td>
            <td style="padding:3px 6px;text-align:center">{{ $bd->exceeds_limit ? '⚠' : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Impact Matrix --}}
@if($impacts->count())
<div class="section-title">Steps 5 & 6 — Impact Assessment Matrix ({{ $impacts->count() }} impacts)</div>
<table width="100%" style="border-collapse:collapse;font-size:9px">
    <thead>
        <tr style="background:#1e40af;color:#fff">
            <th style="padding:4px 6px;text-align:left">Activity</th>
            <th style="padding:4px 6px;text-align:left">Receptor</th>
            <th style="padding:4px 6px;text-align:left">Category</th>
            <th style="padding:4px 6px;text-align:center">Score</th>
            <th style="padding:4px 6px;text-align:center">Significance</th>
            <th style="padding:4px 6px;text-align:center">Nature</th>
        </tr>
    </thead>
    <tbody>
        @foreach($impacts->sortByDesc('significance_score') as $impact)
        <tr style="{{ $loop->odd ? 'background:#f1f5f9' : '' }}">
            <td style="padding:3px 6px">{{ $impact->activity }}</td>
            <td style="padding:3px 6px">{{ $impact->receptor }}</td>
            <td style="padding:3px 6px">{{ \App\Models\EsiaImpactAssessment::IMPACT_CATEGORY_LABELS[$impact->impact_category] ?? $impact->impact_category }}</td>
            <td style="padding:3px 6px;text-align:center">{{ $impact->significance_score }}</td>
            <td style="padding:3px 6px;text-align:center">
                <span class="badge-{{ \App\Models\EsiaImpactAssessment::levelColor($impact->significance_level) }}">
                    {{ ucfirst($impact->significance_level) }}
                </span>
            </td>
            <td style="padding:3px 6px;text-align:center">{{ ucfirst($impact->nature) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ESMP Mitigation Actions --}}
@if($mitigations->count())
<div class="section-title">Step 8 — Environmental & Social Management Plan ({{ $mitigations->count() }} actions)</div>
<table width="100%" style="border-collapse:collapse;font-size:9px">
    <thead>
        <tr style="background:#1e40af;color:#fff">
            <th style="padding:4px 6px;text-align:left">Action</th>
            <th style="padding:4px 6px;text-align:left">Type</th>
            <th style="padding:4px 6px;text-align:left">Responsible</th>
            <th style="padding:4px 6px;text-align:left">Timeline</th>
            <th style="padding:4px 6px;text-align:center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mitigations as $m)
        <tr style="{{ $loop->odd ? 'background:#f1f5f9' : '' }}">
            <td style="padding:3px 6px">{{ \Illuminate\Support\Str::limit($m->activity_description, 60) }}</td>
            <td style="padding:3px 6px">{{ \App\Models\EsiaMitigationAction::TYPE_LABELS[$m->mitigation_type] ?? $m->mitigation_type }}</td>
            <td style="padding:3px 6px">{{ $m->responsible_party ?? '—' }}</td>
            <td style="padding:3px 6px">
                {{ $m->timeline_start?->format('d M Y') ?? '—' }} – {{ $m->timeline_end?->format('d M Y') ?? '—' }}
            </td>
            <td style="padding:3px 6px;text-align:center">
                <span class="badge-{{ ['completed'=>'success','overdue'=>'danger','in_progress'=>'info','planned'=>'gray','cancelled'=>'warning'][$m->status] ?? 'gray' }}">
                    {{ \App\Models\EsiaMitigationAction::STATUS_LABELS[$m->status] ?? $m->status }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Regulatory Submissions --}}
@if($submissions->count())
<div class="section-title">Step 11 — Regulatory Submissions</div>
@foreach($submissions as $sub)
<table class="grid-2" style="margin-bottom:8px">
    <tr>
        <td><strong>Authority:</strong></td>
        <td>{{ $sub->regulatory_authority }}</td>
        <td><strong>Type:</strong></td>
        <td>{{ \App\Models\EsiaRegulatorySubmission::SUBMISSION_TYPE_LABELS[$sub->submission_type] ?? $sub->submission_type }}</td>
    </tr>
    <tr>
        <td><strong>Reference:</strong></td>
        <td>{{ $sub->reference_number ?? '—' }}</td>
        <td><strong>Status:</strong></td>
        <td>
            <span class="badge-{{ ['approved'=>'success','rejected'=>'danger','under_review'=>'primary','submitted'=>'info'][$sub->status] ?? 'gray' }}">
                {{ \App\Models\EsiaRegulatorySubmission::STATUS_LABELS[$sub->status] ?? $sub->status }}
            </span>
        </td>
    </tr>
    @if($sub->submitted_at)
    <tr>
        <td><strong>Submitted:</strong></td>
        <td>{{ $sub->submitted_at->format('d M Y') }}</td>
        <td><strong>Decision Date:</strong></td>
        <td>{{ $sub->decision_date?->format('d M Y') ?? '—' }}</td>
    </tr>
    @endif
    @if($sub->approval_conditions)
    <tr>
        <td colspan="4"><strong>Approval Conditions:</strong> {{ $sub->approval_conditions }}</td>
    </tr>
    @endif
</table>
@endforeach
@endif

{{-- Review Comments --}}
@if($report->review_comments)
<div class="section-title">Review Comments</div>
<p style="white-space:pre-wrap">{{ $report->review_comments }}</p>
@endif

{{-- Signature Block --}}
<div class="sig-row" style="margin-top:30px">
    <div><strong>Prepared By</strong><br><br><br>{{ $report->author?->name ?? '________________________' }}<br><small>Lead Assessor</small></div>
    <div><strong>Reviewed By</strong><br><br><br>{{ $report->reviewedBy?->name ?? '________________________' }}<br><small>Technical Reviewer</small></div>
    <div><strong>Approved By</strong><br><br><br>________________________<br><small>Managing Director</small></div>
</div>

@endsection
