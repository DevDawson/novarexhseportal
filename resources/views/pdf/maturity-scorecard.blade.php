<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a202c; }

    .page { padding: 20px 24px; }

    /* Letterhead */
    .lh-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .lh-logo td { vertical-align: middle; padding: 0; }
    .lh-logo .logo-cell { width: 70px; }
    .lh-logo img { max-height: 52px; max-width: 65px; }
    .lh-logo .name-cell { padding-left: 10px; }
    .lh-logo .company-name { font-size: 15px; font-weight: bold; color: #1a365d; }
    .lh-logo .company-sub { font-size: 8px; color: #718096; margin-top: 2px; }
    .lh-divider { border-top: 2.5px solid #2b6cb0; margin: 6px 0; }
    .lh-doc-title { font-size: 12px; font-weight: bold; color: #2b6cb0; text-align: center; margin: 4px 0 12px; }

    /* Score banner */
    .score-banner { background-color: #ebf8ff; border: 1.5px solid #2b6cb0; border-radius: 6px; padding: 10px 14px; margin-bottom: 12px; }
    .score-table { width: 100%; border-collapse: collapse; }
    .score-table td { vertical-align: middle; padding: 2px 6px; }
    .big-score { font-size: 32px; font-weight: bold; color: #2b6cb0; }
    .level-label { font-size: 13px; font-weight: bold; color: #2c5282; }
    .meta-row td { font-size: 9px; color: #4a5568; padding: 1px 4px; }

    /* Section title */
    .section-title { font-size: 9px; font-weight: bold; color: #2b6cb0; text-transform: uppercase;
                     letter-spacing: 0.5px; border-bottom: 1px solid #bee3f8; padding-bottom: 3px; margin: 10px 0 6px; }

    /* Dimension table */
    .dim-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .dim-table th { background-color: #2b6cb0; color: #fff; font-size: 8px; font-weight: bold;
                    text-align: left; padding: 4px 6px; }
    .dim-table td { padding: 4px 6px; font-size: 9px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .dim-table tr:nth-child(even) td { background-color: #f7fafc; }

    .bar-cell { width: 90px; }
    .bar-outer { width: 90px; height: 7px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden; }
    .bar-inner { height: 7px; border-radius: 4px; }

    .badge { border-radius: 9px; padding: 1px 6px; font-size: 7.5px; font-weight: bold; display: inline-block; }
    .badge-l1 { background-color: #fed7d7; color: #9b2c2c; }
    .badge-l2 { background-color: #fefcbf; color: #744210; }
    .badge-l3 { background-color: #e0e7ff; color: #3730a3; }
    .badge-l4 { background-color: #dbeafe; color: #1e40af; }
    .badge-l5 { background-color: #c6f6d5; color: #276749; }

    /* Indicator detail */
    .ind-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5px; }
    .ind-table .dim-header td { background-color: #ebf8ff; font-weight: bold; color: #2c5282;
                                 font-size: 8.5px; padding: 3px 6px; border-top: 1px solid #bee3f8; }
    .ind-table td { padding: 3px 6px; border-bottom: 1px solid #f0f4f8; color: #4a5568; }
    .ind-table .score-cell { width: 40px; text-align: center; font-weight: bold; color: #2b6cb0; }
    .ind-table .auto-cell { width: 45px; text-align: center; font-size: 7.5px; color: #718096; }

    /* Trend */
    .trend-table { width: 100%; border-collapse: collapse; font-size: 9px; }
    .trend-table th { background-color: #2b6cb0; color: #fff; padding: 4px 8px; text-align: left; font-size: 8px; }
    .trend-table td { padding: 4px 8px; border-bottom: 1px solid #e2e8f0; }
    .trend-table tr:nth-child(even) td { background-color: #f7fafc; }

    /* Footer */
    .footer { border-top: 1px solid #e2e8f0; margin-top: 14px; padding-top: 5px;
              font-size: 7.5px; color: #a0aec0; text-align: center; }
</style>
</head>
<body>
<div class="page">

    {{-- Letterhead --}}
    @include('filament.pdf.partials.letterhead')

    <div class="lh-doc-title">HSE MATURITY INDEX — EXECUTIVE SCORECARD</div>

    {{-- Score banner --}}
    <div class="score-banner">
        <table class="score-table">
            <tr>
                <td style="width:100px; text-align:center;">
                    <div class="big-score">{{ number_format($assessment->overall_score ?? 0, 2) }}</div>
                    <div style="font-size:8px; color:#718096;">out of 5.00</div>
                </td>
                <td>
                    <div class="level-label">{{ $assessment->maturity_level }}</div>
                    <div style="font-size:8.5px; color:#4a5568; margin-top:4px;">
                        {{ $levelDescription }}
                    </div>
                </td>
            </tr>
        </table>
        <table class="score-table meta-row" style="margin-top:6px;">
            <tr>
                <td><strong>Period:</strong> {{ $assessment->period }} ({{ ucfirst($assessment->period_type) }})</td>
                <td><strong>Scope:</strong> {{ $assessment->project?->title ?? 'Organisation-wide' }}</td>
                <td><strong>Assessed by:</strong> {{ $assessment->assessedBy?->name ?? '—' }}</td>
                <td><strong>Date:</strong> {{ $assessment->assessed_at?->format('d M Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- Dimension breakdown --}}
    <div class="section-title">Dimension Summary (Heat Map)</div>
    <table class="dim-table">
        <thead>
            <tr>
                <th style="width:20px;">#</th>
                <th>Dimension</th>
                <th style="width:40px; text-align:center;">Weight</th>
                <th style="width:50px; text-align:center;">Score</th>
                <th style="width:100px;">Progress</th>
                <th style="width:90px;">Level</th>
            </tr>
        </thead>
        <tbody>
            @foreach($breakdown as $dim)
                @php
                    $sc  = (float)($dim['score'] ?? 0);
                    $pct = round(($sc / 5) * 100);
                    $barColor = match(true) {
                        $sc >= 4.3 => '#48bb78',
                        $sc >= 3.5 => '#4299e1',
                        $sc >= 3.0 => '#7f9cf5',
                        $sc >= 2.0 => '#ecc94b',
                        default    => '#fc8181',
                    };
                    $badgeCls = match(true) {
                        $sc >= 4.3 => 'badge-l5',
                        $sc >= 3.5 => 'badge-l4',
                        $sc >= 3.0 => 'badge-l3',
                        $sc >= 2.0 => 'badge-l2',
                        default    => 'badge-l1',
                    };
                @endphp
                <tr>
                    <td style="text-align:center; color:#718096;">{{ $dim['code'] }}</td>
                    <td>{{ $dim['name'] }}</td>
                    <td style="text-align:center; color:#4a5568;">{{ $dim['weight'] }}%</td>
                    <td style="text-align:center; font-weight:bold; color:#2b6cb0;">{{ number_format($sc, 2) }}</td>
                    <td class="bar-cell">
                        <div class="bar-outer">
                            <div class="bar-inner" style="width:{{ $pct }}%; background-color:{{ $barColor }};"></div>
                        </div>
                    </td>
                    <td><span class="badge {{ $badgeCls }}">{{ $dim['level'] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Indicator detail by dimension --}}
    <div class="section-title">Indicator Detail</div>
    <table class="ind-table">
        @foreach($indicatorDetail as $dimCode => $group)
            <tr class="dim-header">
                <td colspan="4">{{ $dimCode }} — {{ $group['name'] }}</td>
            </tr>
            @foreach($group['indicators'] as $ind)
                <tr>
                    <td>{{ $ind['name'] }}</td>
                    <td class="score-cell">{{ $ind['score'] }} / 5</td>
                    <td class="auto-cell">{{ $ind['auto'] ? '⚡ Auto' : 'Manual' }}</td>
                    <td style="color:#718096; font-size:7.5px;">{{ Str::limit($ind['evidence'] ?? '', 60) }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>

    {{-- Trend --}}
    @if(count($trend) > 1)
    <div class="section-title">Maturity Trend (Last {{ count($trend) }} Assessments)</div>
    <table class="trend-table">
        <thead>
            <tr>
                <th>Period</th>
                <th>Score</th>
                <th>Level</th>
                <th>Change</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trend as $i => $t)
                @php
                    $prev   = $i < count($trend) - 1 ? $trend[$i + 1] : null;
                    $change = $prev ? ((float)$t['score'] - (float)$prev['score']) : null;
                @endphp
                <tr>
                    <td>{{ $t['period'] }}</td>
                    <td style="font-weight:bold; color:#2b6cb0;">{{ number_format($t['score'], 2) }}</td>
                    <td>{{ $t['level'] }}</td>
                    <td>
                        @if($change !== null)
                            {{ $change > 0 ? '▲' : ($change < 0 ? '▼' : '—') }}
                            {{ $change != 0 ? number_format(abs($change), 2) : '0.00' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Notes --}}
    @if($assessment->notes)
    <div class="section-title">Assessor Notes</div>
    <div style="font-size:9px; color:#4a5568; line-height:1.5; padding:6px; background:#f7fafc; border-radius:4px;">
        {{ $assessment->notes }}
    </div>
    @endif

    <div class="footer">
        Generated by PortalHSE &bull; {{ now()->format('d M Y H:i') }} &bull; Confidential — For internal use only
    </div>

</div>
</body>
</html>
