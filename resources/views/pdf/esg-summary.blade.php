@extends('pdf.layout')

@section('doc-title', 'ESG Performance Summary Report')
@section('doc-ref', 'ESG-' . now()->format('Y-m'))

@section('content')

{{-- ESG Targets --}}
<div class="section">
    <div class="section-title">ESG Targets & Progress</div>
    @if($targets->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:8px;">No targets recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th>Category</th>
                <th>Indicator</th>
                <th>Target</th>
                <th>Actual</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($targets as $t)
            @php
                $catBadge = ['environmental'=>'success','social'=>'info','governance'=>'warning'];
                $statusBadge = ['achieved'=>'success','on_track'=>'info','at_risk'=>'warning','off_track'=>'danger','not_started'=>'gray'];
                $progress = $t->progress_percent;
            @endphp
            <tr>
                <td>{{ $t->period }}</td>
                <td><span class="badge badge-{{ $catBadge[$t->category]??'gray' }}">{{ ucfirst($t->category) }}</span></td>
                <td>{{ $t->indicator }}</td>
                <td>{{ number_format((float)$t->target_value,2) }} {{ $t->unit }}</td>
                <td>{{ $t->actual_value !== null ? number_format((float)$t->actual_value,2).' '.$t->unit : '—' }}</td>
                <td>{{ $progress !== null ? number_format($progress,1).'%' : '—' }}</td>
                <td><span class="badge badge-{{ $statusBadge[$t->status]??'gray' }}">{{ \App\Models\EsgTarget::STATUS_LABELS[$t->status]??$t->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Open Grievances --}}
<div class="section">
    <div class="section-title">Open Grievances ({{ $grievances->count() }})</div>
    @if($grievances->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:8px;">No open grievances.</p>
    @else
    <table>
        <thead>
            <tr><th>Reference</th><th>Category</th><th>Severity</th><th>Received</th><th>Status</th><th>Target</th></tr>
        </thead>
        <tbody>
            @foreach($grievances as $g)
            @php $sevBadge=['high'=>'danger','medium'=>'warning','low'=>'success']; @endphp
            <tr>
                <td>{{ $g->reference }}</td>
                <td>{{ \App\Models\Grievance::CATEGORY_LABELS[$g->category]??$g->category }}</td>
                <td><span class="badge badge-{{ $sevBadge[$g->severity]??'gray' }}">{{ ucfirst($g->severity) }}</span></td>
                <td>{{ $g->received_date?->format('d M Y') }}</td>
                <td>{{ \App\Models\Grievance::STATUS_LABELS[$g->status]??$g->status }}</td>
                <td>{{ $g->target_resolution_date?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Active Policies --}}
<div class="section">
    <div class="section-title">Policy Register — Active Policies ({{ $policies->count() }})</div>
    @if($policies->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:8px;">No active policies.</p>
    @else
    <table>
        <thead>
            <tr><th>Title</th><th>Type</th><th>Version</th><th>Owner</th><th>Review Due</th></tr>
        </thead>
        <tbody>
            @foreach($policies as $p)
            <tr>
                <td>{{ $p->title }}</td>
                <td>{{ \App\Models\GovernancePolicy::TYPE_LABELS[$p->policy_type]??$p->policy_type }}</td>
                <td>{{ $p->version }}</td>
                <td>{{ $p->document_owner }}</td>
                <td style="{{ $p->is_overdue_review ? 'color:#dc2626;font-weight:bold;' : '' }}">{{ $p->review_date?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Social Indicators --}}
<div class="section">
    <div class="section-title">Social Performance Indicators</div>
    @if($social->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:8px;">No social indicator data recorded.</p>
    @else
    <table>
        <thead>
            <tr><th>Period</th><th>Indicator</th><th>Value</th><th>Unit</th></tr>
        </thead>
        <tbody>
            @foreach($social as $s)
            <tr>
                <td>{{ $s->period }}</td>
                <td>{{ \App\Models\SocialIndicator::INDICATOR_LABELS[$s->indicator_type]??$s->indicator_type }}</td>
                <td>{{ number_format((float)$s->value, 2) }}</td>
                <td>{{ $s->unit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="sig-row">
    <div class="sig-box"><label>Prepared By (ESG Officer)</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Reviewed By (Director)</label><br><br><br><span>Name &amp; Signature</span></div>
    <div class="sig-box"><label>Approved By (MD)</label><br><br><br><span>{{ now()->format('d M Y') }}</span></div>
</div>

@endsection
