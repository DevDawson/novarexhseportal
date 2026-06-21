<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════════════
     EMS MATURITY INDEX — headline banner
════════════════════════════════════════════════════════════════ --}}
<div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    {{-- Colour band per level --}}
    @php
        $bandColor = match($color) {
            'success' => 'bg-green-600',
            'info'    => 'bg-blue-600',
            'primary' => 'bg-indigo-600',
            'warning' => 'bg-yellow-500',
            default   => 'bg-red-600',
        };
        $textColor = match($color) {
            'success' => 'text-green-700 dark:text-green-300',
            'info'    => 'text-blue-700 dark:text-blue-300',
            'primary' => 'text-indigo-700 dark:text-indigo-300',
            'warning' => 'text-yellow-700 dark:text-yellow-300',
            default   => 'text-red-700 dark:text-red-300',
        };
        $bgLight = match($color) {
            'success' => 'bg-green-50 dark:bg-green-900/20',
            'info'    => 'bg-blue-50 dark:bg-blue-900/20',
            'primary' => 'bg-indigo-50 dark:bg-indigo-900/20',
            'warning' => 'bg-yellow-50 dark:bg-yellow-900/20',
            default   => 'bg-red-50 dark:bg-red-900/20',
        };
    @endphp
    <div class="{{ $bandColor }} px-6 py-3 flex items-center justify-between">
        <div class="text-white">
            <div class="text-xs font-medium uppercase tracking-widest opacity-80">EMS Maturity Index (EMI)</div>
            <div class="text-2xl font-bold mt-0.5">{{ number_format($emi, 2) }}% — {{ $level }}</div>
        </div>
        <div class="text-white text-right">
            <div class="text-xs opacity-70">Formula: (CR×25 + AS×20 + CAC×20 + OA×20 + TR×15) ÷ 100</div>
            <div class="text-sm font-semibold mt-0.5">{{ $status }}</div>
        </div>
    </div>

    {{-- Component breakdown ─────────────────────────────────────── --}}
    <div class="grid grid-cols-5 divide-x divide-gray-200 dark:divide-gray-700 {{ $bgLight }}">
        @foreach($components as $key => $c)
        <div class="p-4 text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">{{ strtoupper($key) }} ({{ $c['weight'] }}%)</div>
            <div class="text-xl font-bold {{ $textColor }} mt-1">{{ number_format($c['value'], 1) }}%</div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $c['label'] }}</div>
            <div class="text-xs font-semibold text-gray-600 dark:text-gray-300 mt-1">+{{ number_format($c['weighted'], 2) }} pts</div>
        </div>
        @endforeach
    </div>

    {{-- Maturity scale ──────────────────────────────────────────── --}}
    <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 flex gap-4 bg-gray-50 dark:bg-gray-800/50 text-xs">
        @foreach([
            ['range' => '90–100%', 'level' => 'Optimized',  'status' => 'Excellent',                    'cls' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
            ['range' => '80–89%',  'level' => 'Managed',    'status' => 'Good',                         'cls' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
            ['range' => '70–79%',  'level' => 'Defined',    'status' => 'Satisfactory',                 'cls' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300'],
            ['range' => '60–69%',  'level' => 'Developing', 'status' => 'Needs Improvement',            'cls' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'],
            ['range' => '<60%',    'level' => 'Initial',    'status' => 'Significant Improvement Req.', 'cls' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
        ] as $lvl)
        <div class="flex items-center gap-1.5 rounded-full px-3 py-1 {{ $lvl['cls'] }} {{ $level === $lvl['level'] ? 'ring-2 ring-offset-1 ring-gray-400' : 'opacity-70' }}">
            <span class="font-bold">{{ $lvl['range'] }}</span>
            <span>{{ $lvl['level'] }}</span>
            <span class="opacity-70">— {{ $lvl['status'] }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ISO 14001 PDCA STRUCTURE
════════════════════════════════════════════════════════════════ --}}
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
        ISO 14001 EMS Structure — PDCA Model
    </h2>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        {{-- PLAN ─────────────────────────────────────────────────── --}}
        <div class="rounded-lg border border-blue-200 dark:border-blue-800 overflow-hidden">
            <div class="bg-blue-600 text-white px-4 py-2 font-bold text-sm tracking-wide">1. PLAN</div>
            <div class="p-3 space-y-1 bg-blue-50 dark:bg-blue-900/20 text-xs text-gray-700 dark:text-gray-300">
                <div class="flex items-start gap-1.5"><span class="text-blue-500 dark:text-blue-300 mt-0.5">▸</span> Environmental Policy</div>
                <div class="flex items-start gap-1.5"><span class="text-blue-500 dark:text-blue-300 mt-0.5">▸</span> Aspects &amp; Impacts Assessment</div>
                <div class="flex items-start gap-1.5"><span class="text-blue-500 dark:text-blue-300 mt-0.5">▸</span> Legal &amp; Requirements Register</div>
                <div class="flex items-start gap-1.5"><span class="text-blue-500 dark:text-blue-300 mt-0.5">▸</span> Objectives, Targets &amp; Programs</div>
            </div>
        </div>

        {{-- DO ──────────────────────────────────────────────────── --}}
        <div class="rounded-lg border border-green-200 dark:border-green-800 overflow-hidden">
            <div class="bg-green-600 text-white px-4 py-2 font-bold text-sm tracking-wide">2. DO</div>
            <div class="p-3 space-y-1 bg-green-50 dark:bg-green-900/20 text-xs text-gray-700 dark:text-gray-300">
                <div class="flex items-start gap-1.5"><span class="text-green-500 dark:text-green-300 mt-0.5">▸</span> Operational Controls</div>
                <div class="flex items-start gap-1.5"><span class="text-green-500 dark:text-green-300 mt-0.5">▸</span> Training &amp; Awareness</div>
                <div class="flex items-start gap-1.5"><span class="text-green-500 dark:text-green-300 mt-0.5">▸</span> Communication &amp; Consultation</div>
                <div class="flex items-start gap-1.5"><span class="text-green-500 dark:text-green-300 mt-0.5">▸</span> Emergency Preparedness</div>
            </div>
        </div>

        {{-- CHECK ───────────────────────────────────────────────── --}}
        <div class="rounded-lg border border-yellow-200 dark:border-yellow-800 overflow-hidden">
            <div class="bg-yellow-500 text-white px-4 py-2 font-bold text-sm tracking-wide">3. CHECK</div>
            <div class="p-3 space-y-1 bg-yellow-50 dark:bg-yellow-900/20 text-xs text-gray-700 dark:text-gray-300">
                <div class="flex items-start gap-1.5"><span class="text-yellow-600 dark:text-yellow-300 mt-0.5">▸</span> Monitoring &amp; KPI Management</div>
                <div class="flex items-start gap-1.5"><span class="text-yellow-600 dark:text-yellow-300 mt-0.5">▸</span> Compliance Evaluation</div>
                <div class="flex items-start gap-1.5"><span class="text-yellow-600 dark:text-yellow-300 mt-0.5">▸</span> Internal Environmental Audits</div>
            </div>
        </div>

        {{-- ACT ─────────────────────────────────────────────────── --}}
        <div class="rounded-lg border border-red-200 dark:border-red-800 overflow-hidden">
            <div class="bg-red-600 text-white px-4 py-2 font-bold text-sm tracking-wide">4. ACT</div>
            <div class="p-3 space-y-1 bg-red-50 dark:bg-red-900/20 text-xs text-gray-700 dark:text-gray-300">
                <div class="flex items-start gap-1.5"><span class="text-red-500 dark:text-red-300 mt-0.5">▸</span> Nonconformities &amp; Corrective Actions</div>
                <div class="flex items-start gap-1.5"><span class="text-red-500 dark:text-red-300 mt-0.5">▸</span> Management Review</div>
                <div class="flex items-start gap-1.5"><span class="text-red-500 dark:text-red-300 mt-0.5">▸</span> Continual Improvement (Cl. 10)</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CONTINUAL IMPROVEMENT KPIs 15.1–15.4
════════════════════════════════════════════════════════════════ --}}
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
        Continual Improvement KPIs (ISO 14001 Clause 10)
    </h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($kpis as $num => $kpi)
        @php
            $v = $kpi['value'];
            $t = $kpi['target'];
            $lib = $kpi['lower_is_better'];
            $good = $lib ? ($v <= $t) : ($v >= $t);
            $kpiColor = $good ? 'text-green-600 dark:text-green-400' : ($lib ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400');
            $kpiBg    = $good ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                              : ($lib ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
                                      : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800');
        @endphp
        <div class="rounded-lg border p-4 {{ $kpiBg }}">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase">KPI {{ $num }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ $kpi['label'] }}</div>
            <div class="text-2xl font-bold {{ $kpiColor }} mt-2">{{ number_format($v, 1) }}%</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Target: {{ $lib ? '≤' : '≥' }} {{ $t }}%
                @if($lib)<span class="ml-1 text-xs">(lower = better)</span>@endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     EMS TERM DEFINITIONS
════════════════════════════════════════════════════════════════ --}}
<div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="bg-gray-700 dark:bg-gray-900 text-white px-5 py-3 font-semibold text-sm">
        Key EMS Terms in NOVAREX ERP
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach([
            ['term' => 'Key Performance Indicator (KPI)', 'def' => 'Measures a specific area of EMS performance (e.g., Compliance Rate, Audit Score, Training Completion). Each KPI is calculated from live ERP data and displayed on the EMS Dashboard and widget panels.'],
            ['term' => 'EMS Maturity Index (EMI)',        'def' => 'A composite score combining 5 weighted KPIs into a single 0–100% value that shows overall EMS effectiveness. Formula: EMI = (CR×25 + AS×20 + CAC×20 + OA×20 + TR×15) ÷ 100. Not required by ISO 14001 but provides a single headline metric for executive review.'],
            ['term' => 'Continual Improvement (Cl. 10)', 'def' => 'The formal EMS process (ISO 14001 Clause 10) of using KPI and EMI results to identify and implement actions that enhance environmental performance. Tracked in the EMS → Continual Improvement module as EMS-CI actions linked to PDCA phases.'],
            ['term' => 'Management Review',               'def' => 'Periodic senior leadership review of KPI trends, EMI scores, PDCA status, and CI actions. Outputs include updated objectives, resource decisions, and approved improvement actions. Feeds the Act phase of the PDCA cycle.'],
        ] as $item)
        <div class="px-5 py-3 grid grid-cols-4 gap-4">
            <div class="col-span-1 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $item['term'] }}</div>
            <div class="col-span-3 text-sm text-gray-600 dark:text-gray-300">{{ $item['def'] }}</div>
        </div>
        @endforeach
    </div>
</div>

</x-filament-panels::page>
