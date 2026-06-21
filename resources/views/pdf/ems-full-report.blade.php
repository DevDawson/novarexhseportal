@extends('pdf.layout')

@section('doc-title', 'Environmental Management System (EMS) — Full Report')
@section('doc-ref', 'EMS-FULL-' . now()->format('Y-m-d'))

@section('content')

{{-- ── EXECUTIVE SUMMARY ── --}}
<div class="section">
    <div class="section-title">EMS Maturity Overview — {{ now()->format('d M Y') }}</div>
    <table>
        <tr>
            <th style="width:20%">Aspects & Impacts</th>
            <th style="width:16%">Waste Records</th>
            <th style="width:16%">Spill Reports</th>
            <th style="width:16%">Monitoring Records</th>
            <th style="width:16%">Active Permits</th>
            <th style="width:16%">Legal Items</th>
        </tr>
        <tr>
            <td style="text-align:center; font-size:12px; font-weight:bold; color:#1d4ed8;">{{ $aspects->count() }}</td>
            <td style="text-align:center; font-size:12px; font-weight:bold; color:#1d4ed8;">{{ $wasteRecords->count() }}</td>
            <td style="text-align:center; font-size:12px; font-weight:bold; color:#dc2626;">{{ $spillReports->count() }}</td>
            <td style="text-align:center; font-size:12px; font-weight:bold; color:#1d4ed8;">{{ $monitoringRecords->count() }}</td>
            <td style="text-align:center; font-size:12px; font-weight:bold; color:#16a34a;">{{ $permits->where('status','active')->count() }}</td>
            <td style="text-align:center; font-size:12px; font-weight:bold; color:#1d4ed8;">{{ $legalItems->count() }}</td>
        </tr>
    </table>
</div>

{{-- ── SECTION 1: ENVIRONMENTAL ASPECTS ── --}}
<div class="section">
    <div class="section-title">1. Environmental Aspects & Impacts ({{ $aspects->count() }} records)</div>
    @if($aspects->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No aspects recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:22%">Aspect</th>
                <th style="width:22%">Impact</th>
                <th style="width:15%">Category</th>
                <th style="width:12%">Significance</th>
                <th style="width:12%">Score</th>
                <th style="width:17%">Project</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aspects as $a)
            <tr>
                <td>{{ $a->environmental_aspect }}</td>
                <td>{{ $a->environmental_impact }}</td>
                <td>{{ ucwords(str_replace('_',' ',$a->impact_category ?? '—')) }}</td>
                <td>{{ ucfirst($a->significance_level ?? '—') }}</td>
                <td style="text-align:center;">{{ $a->significance_score ?? '—' }}</td>
                <td>{{ $a->project?->title ?? 'Company-wide' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── SECTION 2: LEGAL & COMPLIANCE REGISTER ── --}}
<div class="section">
    <div class="section-title">2. Legal & Compliance Register ({{ $legalItems->count() }} items)</div>
    @if($legalItems->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No legal requirements recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:30%">Requirement</th>
                <th style="width:15%">Type</th>
                <th style="width:18%">Issuing Authority</th>
                <th style="width:18%">Compliance Status</th>
                <th style="width:10%">Expiry</th>
                <th style="width:9%">Review Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($legalItems as $l)
            <tr>
                <td>{{ $l->requirement_title }}</td>
                <td>{{ ucwords(str_replace('_',' ',$l->requirement_type ?? '—')) }}</td>
                <td>{{ $l->issuing_authority ?? '—' }}</td>
                <td>{{ ucwords(str_replace('_',' ',$l->compliance_status ?? '—')) }}</td>
                <td>{{ $l->expiry_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $l->next_review_date?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── SECTION 3: ENVIRONMENTAL PERMITS ── --}}
<div class="section">
    <div class="section-title">3. Environmental Permits & Licences ({{ $permits->count() }} records)</div>
    @if($permits->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No permits recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:15%">Permit No.</th>
                <th style="width:20%">Type</th>
                <th style="width:22%">Issuing Authority</th>
                <th style="width:12%">Issue Date</th>
                <th style="width:12%">Expiry Date</th>
                <th style="width:10%">Status</th>
                <th style="width:9%">Project</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permits as $p)
            <tr>
                <td>{{ $p->permit_number }}</td>
                <td>{{ ucwords(str_replace('_',' ',$p->permit_type ?? '—')) }}</td>
                <td>{{ $p->issuing_authority }}</td>
                <td>{{ $p->issue_date?->format('d M Y') ?? '—' }}</td>
                <td style="{{ ($p->expiry_date && $p->expiry_date->isPast()) ? 'color:#dc2626;font-weight:bold;' : '' }}">
                    {{ $p->expiry_date?->format('d M Y') ?? 'Indefinite' }}
                </td>
                <td>{{ ucwords(str_replace('_',' ',$p->status)) }}</td>
                <td>{{ $p->project?->title ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── SECTION 4: MONITORING RECORDS ── --}}
<div class="section">
    <div class="section-title">4. Environmental Monitoring Records ({{ $monitoringRecords->count() }} records)</div>
    @if($monitoringRecords->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No monitoring records.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:14%">Date</th>
                <th style="width:22%">Metric Type</th>
                <th style="width:14%">Value</th>
                <th style="width:15%">Status</th>
                <th style="width:18%">Project</th>
                <th style="width:17%">Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monitoringRecords as $m)
            <tr>
                <td>{{ $m->record_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ \App\Models\EnvironmentalMonitoringRecord::METRIC_TYPE_LABELS[$m->metric_type] ?? ucwords(str_replace('_',' ',$m->metric_type)) }}</td>
                <td style="text-align:right;">{{ $m->value }} {{ $m->unit }}</td>
                <td>{{ ucwords(str_replace('_',' ',$m->status ?? '—')) }}</td>
                <td>{{ $m->project?->title ?? 'Company-wide' }}</td>
                <td>{{ $m->recordedBy?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── SECTION 5: WASTE TRACKING ── --}}
<div class="section">
    <div class="section-title">5. Waste Tracking Records ({{ $wasteRecords->count() }} records)</div>
    @if($wasteRecords->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No waste records.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:18%">Type</th>
                <th style="width:24%">Description</th>
                <th style="width:12%">Quantity</th>
                <th style="width:14%">Disposal Method</th>
                <th style="width:12%">Gen. Date</th>
                <th style="width:10%">Status</th>
                <th style="width:10%">Project</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wasteRecords as $w)
            <tr>
                <td>{{ ucwords(str_replace('_',' ',$w->waste_type)) }}</td>
                <td>{{ $w->waste_description }}</td>
                <td style="text-align:right;">{{ $w->quantity }} {{ $w->unit }}</td>
                <td>{{ ucwords(str_replace('_',' ',$w->disposal_method ?? '—')) }}</td>
                <td>{{ $w->generation_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ ucwords(str_replace('_',' ',$w->status)) }}</td>
                <td>{{ $w->project?->title ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── SECTION 6: SPILL REPORTS ── --}}
<div class="section">
    <div class="section-title">6. Chemical & Oil Spill Reports ({{ $spillReports->count() }} records)</div>
    @if($spillReports->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No spill reports recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:13%">Reference</th>
                <th style="width:11%">Date</th>
                <th style="width:18%">Substance</th>
                <th style="width:14%">Volume</th>
                <th style="width:15%">Media Affected</th>
                <th style="width:20%">Immediate Actions</th>
                <th style="width:9%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($spillReports as $s)
            <tr>
                <td>{{ $s->spill_reference }}</td>
                <td>{{ $s->spill_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $s->substance_spilled }} ({{ ucfirst($s->substance_type) }})</td>
                <td style="text-align:right;">{{ $s->estimated_volume ? $s->estimated_volume.' '.$s->volume_unit : '—' }}</td>
                <td>{{ ucwords(str_replace('_',' ',$s->environmental_media_affected ?? '—')) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($s->immediate_actions ?? '—', 60) }}</td>
                <td>{{ ucwords(str_replace('_',' ',$s->status)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── SECTION 7: IMPROVEMENT ACTIONS ── --}}
<div class="section">
    <div class="section-title">7. Continual Improvement (CI) Actions ({{ $ciActions->count() }} actions)</div>
    @if($ciActions->isEmpty())
        <p style="font-size:9px;color:#6b7280;padding:6px;">No CI actions recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:12%">Reference</th>
                <th style="width:30%">Title</th>
                <th style="width:12%">PDCA</th>
                <th style="width:10%">Priority</th>
                <th style="width:14%">Assigned To</th>
                <th style="width:12%">Target Date</th>
                <th style="width:10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ciActions as $c)
            <tr>
                <td>{{ $c->reference }}</td>
                <td>{{ $c->title }}</td>
                <td style="text-align:center;">{{ strtoupper($c->pdca_phase ?? '—') }}</td>
                <td>{{ ucfirst($c->priority ?? '—') }}</td>
                <td>{{ $c->assignedTo?->name ?? '—' }}</td>
                <td style="{{ $c->target_date?->isPast() && $c->status !== 'closed' ? 'color:#dc2626;' : '' }}">
                    {{ $c->target_date?->format('d M Y') ?? '—' }}
                </td>
                <td>{{ \App\Models\EmsImprovementAction::STATUS_LABELS[$c->status] ?? ucfirst($c->status ?? '—') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
