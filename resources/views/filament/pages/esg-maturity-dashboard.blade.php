<x-filament-panels::page>

@php
    $esgMi = (float) $composite['esg_mi'];
    $level = $composite['level'];
    $color = $composite['color'];

    $bandStyle = match($color) {
        'success' => 'background-color:#047857',
        'info'    => 'background-color:#2563eb',
        'primary' => 'background-color:#4338ca',
        'warning' => 'background-color:#d97706',
        default   => 'background-color:#dc2626',
    };
    $textAccent = match($color) {
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'info'    => 'text-blue-600 dark:text-blue-400',
        'primary' => 'text-indigo-600 dark:text-indigo-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        default   => 'text-red-600 dark:text-red-400',
    };

    $components = [
        'E' => [
            'label'   => 'Environmental',
            'weight'  => 40,
            'score'   => (float) $composite['e'],
            'color'   => 'emerald',
            'icon'    => '🌿',
            'indicators' => [
                'cr'  => ['label' => 'Compliance Rate (CR)',          'score' => (float)($scores['cr']  ?? 0)],
                'wr'  => ['label' => 'Waste Diversion Rate (WR)',      'score' => (float)($scores['wr']  ?? 0)],
                'er'  => ['label' => 'Emissions Reduction Rate (ER)',  'score' => (float)($scores['er']  ?? 0)],
                'wtr' => ['label' => 'Water Reduction Efficiency (WTR)','score' => (float)($scores['wtr'] ?? 0)],
                'ems' => ['label' => 'EMS Maturity Index (EMS)',       'score' => (float)($scores['ems'] ?? 0)],
            ],
        ],
        'S' => [
            'label'   => 'Social',
            'weight'  => 30,
            'score'   => (float) $composite['s'],
            'color'   => 'blue',
            'icon'    => '👥',
            'indicators' => [
                'tr'    => ['label' => 'Training Completion Rate (TR)',    'score' => (float)($scores['tr']    ?? 0)],
                'ltifr' => ['label' => 'LTIFR Performance Score (LTIFR)', 'score' => (float)($scores['ltifr'] ?? 0)],
                'ewr'   => ['label' => 'Employee Well-being Score (EWR)', 'score' => (float)($scores['ewr']   ?? 0)],
                'csr'   => ['label' => 'Community Engagement Score (CSR)','score' => (float)($scores['csr']   ?? 0)],
                'dei'   => ['label' => 'Diversity, Equity & Inclusion (DEI)','score' => (float)($scores['dei'] ?? 0)],
            ],
        ],
        'G' => [
            'label'   => 'Governance',
            'weight'  => 30,
            'score'   => (float) $composite['g'],
            'color'   => 'violet',
            'icon'    => '🏛️',
            'indicators' => [
                'ccr' => ['label' => 'Compliance & Ethics Score (CCR)',   'score' => (float)($scores['ccr'] ?? 0)],
                'acr' => ['label' => 'Audit Closure Rate (ACR)',          'score' => (float)($scores['acr'] ?? 0)],
                'dcr' => ['label' => 'Document Control Rate (DCR)',       'score' => (float)($scores['dcr'] ?? 0)],
                'ecr' => ['label' => 'Corrective Action Closure (ECR)',   'score' => (float)($scores['ecr'] ?? 0)],
                'mrr' => ['label' => 'Management Review Rate (MRR)',      'score' => (float)($scores['mrr'] ?? 0)],
            ],
        ],
    ];
@endphp

{{-- ══════════════════════════════════════════════════════════════
     ESG-MI HEADLINE BANNER
══════════════════════════════════════════════════════════════ --}}
<div class="mb-6 rounded-xl overflow-hidden shadow border border-gray-200 dark:border-gray-700">
    <div style="{{ $bandStyle }}" class="px-6 py-4">
        <div class="flex items-end justify-between">
            <div class="text-white">
                <div class="text-xs font-semibold uppercase tracking-widest opacity-75">ESG Maturity Index (ESG-MI)</div>
                <div class="text-4xl font-black mt-1">{{ number_format($esgMi, 2) }}%</div>
                <div class="text-lg font-semibold mt-0.5 opacity-90">{{ $level }}</div>
            </div>
            <div class="text-white text-right text-sm opacity-80 space-y-1">
                <div class="font-mono text-xs">
                    ESG-MI = (E×40 + S×30 + G×30) ÷ 100
                </div>
                <div class="font-mono text-xs">
                    = ({{ number_format($composite['e'],1) }}×40 + {{ number_format($composite['s'],1) }}×30 + {{ number_format($composite['g'],1) }}×30) ÷ 100
                </div>
                @if($latestInfo)
                <div class="text-xs opacity-70 mt-2">
                    Period: {{ $latestInfo['period'] }} · Assessed: {{ $latestInfo['assessed_at'] }}
                </div>
                @else
                <div class="text-xs opacity-70 mt-2">Live calculation — no finalized assessment yet</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Component bar ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-gray-700 bg-gray-50 dark:bg-gray-800/40">
        @foreach($components as $key => $comp)
        @php
            $cs = match($comp['color']) {
                'emerald' => 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20',
                'blue'    => 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20',
                'violet'  => 'text-violet-700 dark:text-violet-300 bg-violet-50 dark:bg-violet-900/20',
                default   => '',
            };
        @endphp
        <div class="p-4 text-center {{ $cs }}">
            <div class="text-lg">{{ $comp['icon'] }}</div>
            <div class="text-xs font-semibold uppercase tracking-wide mt-1">{{ $key }} — {{ $comp['label'] }} ({{ $comp['weight'] }}%)</div>
            <div class="text-3xl font-black mt-1">{{ number_format($comp['score'], 1) }}%</div>
            <div class="text-xs opacity-70 mt-0.5">Weighted: +{{ number_format($comp['score'] * $comp['weight'] / 100, 2) }} pts</div>
        </div>
        @endforeach
    </div>

    {{-- Maturity level scale ──────────────────────────────────────── --}}
    <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-3 flex flex-wrap gap-2 bg-white dark:bg-gray-900">
        @foreach([
            ['range' => '90–100%', 'level' => 'Transformational', 'cls' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'],
            ['range' => '80–89%',  'level' => 'Advanced',         'cls' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
            ['range' => '70–79%',  'level' => 'Managed',          'cls' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300'],
            ['range' => '60–69%',  'level' => 'Developing',       'cls' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'],
            ['range' => '<60%',    'level' => 'Initial',           'cls' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
        ] as $lvl)
        <span class="text-xs rounded-full px-3 py-1 font-medium {{ $lvl['cls'] }} {{ $level === $lvl['level'] ? 'ring-2 ring-offset-1 ring-gray-500 font-bold' : 'opacity-60' }}">
            {{ $lvl['range'] }} — {{ $lvl['level'] }}
        </span>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     COMPONENT BREAKDOWN — E / S / G
══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    @foreach($components as $key => $comp)
    @php
        $hdrStyle = match($comp['color']) {
            'emerald' => 'background-color:#065f46',
            'blue'    => 'background-color:#1e40af',
            'violet'  => 'background-color:#5b21b6',
            default   => 'background-color:#374151',
        };
        $barStyle = match($comp['color']) {
            'emerald' => 'background-color:#10b981',
            'blue'    => 'background-color:#3b82f6',
            'violet'  => 'background-color:#8b5cf6',
            default   => 'background-color:#6b7280',
        };
        $row = match($comp['color']) {
            'emerald' => 'bg-emerald-50 dark:bg-emerald-900/10',
            'blue'    => 'bg-blue-50 dark:bg-blue-900/10',
            'violet'  => 'bg-violet-50 dark:bg-violet-900/10',
            default   => '',
        };
    @endphp
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div style="{{ $hdrStyle }}" class="text-white px-4 py-3 flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-widest opacity-75">{{ $comp['icon'] }} {{ $key }} Component</div>
                <div class="font-semibold">{{ $comp['label'] }} · Weight {{ $comp['weight'] }}%</div>
            </div>
            <div class="text-2xl font-black">{{ number_format($comp['score'], 1) }}%</div>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-700 {{ $row }}">
            @foreach($comp['indicators'] as $indKey => $ind)
            @php
                $s = $ind['score'];
                $src = $autoSources[$indKey] ?? 'manual';
                $srcBadge = match($src) {
                    'auto'            => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300', 'AUTO'],
                    'semi_auto'       => ['bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300', 'SEMI-AUTO'],
                    'manual_required' => ['bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300', 'ENTER MANUALLY'],
                    default           => ['bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300', 'MANUAL'],
                };
                $barWidth = max(2, min(100, $s));
            @endphp
            <div class="px-4 py-2.5">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $ind['label'] }}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $srcBadge[0] }}">{{ $srcBadge[1] }}</span>
                        <span class="text-sm font-bold {{ $s >= 80 ? 'text-green-600 dark:text-green-400' : ($s >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                            {{ $s > 0 ? number_format($s, 1) . '%' : '—' }}
                        </span>
                    </div>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full" style="{{ $barStyle }};width:{{ $barWidth }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-4 py-2 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700 text-center text-xs text-gray-500">
            {{ $key }} = avg of 5 indicators · Formula: ({{ implode(' + ', array_keys($comp['indicators'])) }}) ÷ 5
        </div>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════
     HISTORICAL TREND TABLE
══════════════════════════════════════════════════════════════ --}}
@if(count($history) > 0)
<div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="bg-gray-700 dark:bg-gray-900 text-white px-5 py-3 font-semibold text-sm">
        Historical ESG-MI Trend (Finalized Assessments)
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-2 text-left">Period</th>
                    <th class="px-4 py-2 text-center">E (40%)</th>
                    <th class="px-4 py-2 text-center">S (30%)</th>
                    <th class="px-4 py-2 text-center">G (30%)</th>
                    <th class="px-4 py-2 text-center">ESG-MI</th>
                    <th class="px-4 py-2 text-center">Level</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($history as $i => $h)
                @php
                    $hMi    = (float)($h['esg_mi'] ?? 0);
                    $hLevel = \App\Models\EsgMaturityAssessment::emiToLevel($hMi);
                    $prev   = $history[$i + 1] ?? null;
                    $delta  = $prev ? $hMi - (float)($prev['esg_mi'] ?? 0) : null;
                @endphp
                <tr class="{{ $i === 0 ? 'bg-blue-50 dark:bg-blue-900/20 font-semibold' : 'hover:bg-gray-50 dark:hover:bg-gray-800/40' }}">
                    <td class="px-4 py-2 font-mono text-xs">{{ $h['period'] }}</td>
                    <td class="px-4 py-2 text-center text-emerald-700 dark:text-emerald-400">{{ number_format((float)($h['e_score'] ?? 0), 1) }}%</td>
                    <td class="px-4 py-2 text-center text-blue-700 dark:text-blue-400">{{ number_format((float)($h['s_score'] ?? 0), 1) }}%</td>
                    <td class="px-4 py-2 text-center text-violet-700 dark:text-violet-400">{{ number_format((float)($h['g_score'] ?? 0), 1) }}%</td>
                    <td class="px-4 py-2 text-center font-bold">
                        {{ number_format($hMi, 2) }}%
                        @if($delta !== null)
                        <span class="{{ $delta >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-xs ml-1">
                            {{ $delta >= 0 ? '▲' : '▼' }}{{ number_format(abs($delta), 1) }}
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center text-xs font-medium {{ match($hLevel) {
                        'Transformational' => 'text-emerald-700 dark:text-emerald-300',
                        'Advanced'         => 'text-blue-700 dark:text-blue-300',
                        'Managed'          => 'text-indigo-700 dark:text-indigo-300',
                        'Developing'       => 'text-amber-700 dark:text-amber-300',
                        default            => 'text-red-700 dark:text-red-300',
                    } }}">{{ $hLevel }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     KEY TERM DEFINITIONS
══════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="bg-gray-700 dark:bg-gray-900 text-white px-5 py-3 font-semibold text-sm">
        Key ESG Terms in NOVAREX ERP
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach([
            ['term' => 'Key Performance Indicator (KPI)',
             'def'  => 'Measures a specific area of ESG performance — e.g. Compliance Rate, LTIFR Performance Score, Audit Closure Rate. Each KPI is calculated from live ERP data across environmental audits, training records, CAPA actions, and HR data. KPIs are the building blocks of the ESG-MI.'],
            ['term' => 'ESG Maturity Index (ESG-MI)',
             'def'  => 'A composite score combining 15 KPIs across three pillars (Environmental 40%, Social 30%, Governance 30%) into a single 0–100% value. Formula: ESG-MI = (E×40 + S×30 + G×30) ÷ 100. Levels: Transformational ≥90%, Advanced 80–89%, Managed 70–79%, Developing 60–69%, Initial <60%.'],
            ['term' => 'Continual Improvement',
             'def'  => 'The process of using KPI trends and ESG-MI scores to identify weak areas and implement targeted improvement actions. Tracked through the EMS Continual Improvement module (ISO 14001 Cl. 10) and ESG targets. Rising ESG-MI scores over successive assessment periods confirm effective improvement.'],
            ['term' => 'Management Review',
             'def'  => 'Periodic senior leadership evaluation of ESG-MI trends, pillar scores (E/S/G), and individual KPI performance against targets. Outputs include approval of ESG improvement programs, updated targets, and resource allocation. The MRR indicator measures how consistently management reviews are conducted.'],
        ] as $item)
        <div class="px-5 py-3 grid grid-cols-4 gap-4 items-start">
            <div class="col-span-1 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $item['term'] }}</div>
            <div class="col-span-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $item['def'] }}</div>
        </div>
        @endforeach
    </div>
</div>

</x-filament-panels::page>
