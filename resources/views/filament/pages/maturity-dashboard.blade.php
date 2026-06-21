<x-filament-panels::page>

    @if (!$latest)
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <x-heroicon-o-chart-bar-square class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
            <h2 class="text-xl font-semibold text-gray-600 dark:text-gray-300">No Finalised Assessment Yet</h2>
            <p class="text-sm text-gray-400 mt-1">Create and finalise a Maturity Assessment to see the dashboard.</p>
            <a href="{{ route('filament.admin.resources.maturity-assessments.create') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                Create First Assessment
            </a>
        </div>
    @else

    {{-- ── Top KPI bar ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Overall HSE MI</p>
            @php
                $s = (float)($latest->overall_score ?? 0);
                $scoreColor = match(true) {
                    $s >= 4.3 => 'text-green-600 dark:text-green-400',
                    $s >= 3.5 => 'text-blue-600 dark:text-blue-400',
                    $s >= 3.0 => 'text-indigo-500 dark:text-indigo-400',
                    $s >= 2.0 => 'text-yellow-500 dark:text-yellow-400',
                    default   => 'text-red-500 dark:text-red-400',
                };
            @endphp
            <p class="mt-1 text-4xl font-bold {{ $scoreColor }}">
                {{ number_format($s, 2) }}<span class="text-lg font-normal text-gray-400"> / 5.00</span>
            </p>
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $latest->maturity_level }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Assessment Period</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $latest->period }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ ucfirst($latest->period_type) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Scope</p>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100">
                {{ $latest->project?->title ?? 'Organisation-wide' }}
            </p>
            <p class="text-sm text-gray-400">{{ $latest->assessed_at?->format('d M Y') }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Assessed By</p>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100">
                {{ $latest->assessedBy?->name ?? '—' }}
            </p>
            <span class="inline-block mt-1 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs px-2 py-0.5">Finalised</span>
        </div>
    </div>

    {{-- ── Maturity level legend ────────────────────────────────────────── --}}
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Maturity Level Scale</p>
        <div class="flex flex-wrap gap-2">
            @php
                $levels = [
                    ['Level 1: Initial',    '1.0–1.9', 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'],
                    ['Level 2: Basic',      '2.0–2.9', 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'],
                    ['Level 3: Defined',    '3.0–3.4', 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300'],
                    ['Level 4: Proactive',  '3.5–4.2', 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
                    ['Level 5: Optimizing', '4.3–5.0', 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'],
                ];
            @endphp
            @foreach($levels as [$label, $range, $cls])
                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $cls }}">
                    {{ $label }} <span class="opacity-60">({{ $range }})</span>
                </span>
            @endforeach
        </div>
    </div>

    {{-- ── Dimension Heat Map ───────────────────────────────────────────── --}}
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Dimension Heat Map</h3>
            <p class="text-xs text-gray-400 mt-0.5">Score per dimension — latest finalised assessment</p>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($dimensionBreakdown as $dim)
                @php
                    $sc = (float)($dim['score'] ?? 0);
                    $pct = ($sc / 5) * 100;
                    $barColor = match(true) {
                        $sc >= 4.3 => 'bg-green-500',
                        $sc >= 3.5 => 'bg-blue-500',
                        $sc >= 3.0 => 'bg-indigo-500',
                        $sc >= 2.0 => 'bg-yellow-400',
                        default    => 'bg-red-400',
                    };
                    $levelBadge = match(true) {
                        $sc >= 4.3 => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                        $sc >= 3.5 => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                        $sc >= 3.0 => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
                        $sc >= 2.0 => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                        default    => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                    };
                @endphp
                <div class="px-5 py-3 flex items-center gap-4">
                    <span class="w-6 text-xs font-bold text-gray-400">{{ $dim['code'] }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ $dim['name'] }}</span>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-100">{{ number_format($sc, 2) }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $levelBadge }}">{{ $dim['level'] }}</span>
                            </div>
                        </div>
                        <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 w-10 text-right">{{ $dim['weight'] }}%</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Trend table (last 6 finalised assessments) ───────────────────── --}}
    @if($trend->count() > 1)
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Maturity Trend</h3>
            <p class="text-xs text-gray-400 mt-0.5">Overall HSE MI across recent finalised assessments</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                        <th class="px-5 py-2 text-left">Period</th>
                        <th class="px-5 py-2 text-left">Score</th>
                        <th class="px-5 py-2 text-left">Level</th>
                        <th class="px-5 py-2 text-left">vs Previous</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($trend as $i => $a)
                        @php
                            $prev   = $i < $trend->count() - 1 ? $trend[$i + 1] : null;
                            $change = $prev ? ((float)$a->overall_score - (float)$prev->overall_score) : null;
                        @endphp
                        <tr class="{{ $a->id === $latest->id ? 'bg-primary-50 dark:bg-primary-900/20 font-semibold' : '' }}">
                            <td class="px-5 py-2 text-gray-700 dark:text-gray-200">
                                {{ $a->period }}
                                @if($a->id === $latest->id)
                                    <span class="ml-1 text-xs text-primary-500 font-normal">(latest)</span>
                                @endif
                            </td>
                            <td class="px-5 py-2 text-gray-800 dark:text-gray-100">{{ number_format($a->overall_score, 2) }}</td>
                            <td class="px-5 py-2 text-xs text-gray-600 dark:text-gray-300">{{ $a->maturity_level }}</td>
                            <td class="px-5 py-2">
                                @if($change !== null)
                                    <span class="font-medium {{ $change > 0 ? 'text-green-600' : ($change < 0 ? 'text-red-500' : 'text-gray-400') }}">
                                        {{ $change > 0 ? '▲' : ($change < 0 ? '▼' : '—') }}
                                        {{ $change != 0 ? number_format(abs($change), 2) : '0.00' }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Quick actions ────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('filament.admin.resources.maturity-assessments.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            <x-heroicon-o-list-bullet class="w-4 h-4" />
            All Assessments
        </a>
        <a href="{{ route('filament.admin.resources.maturity-assessments.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
            <x-heroicon-o-plus class="w-4 h-4" />
            New Assessment
        </a>
        <a href="{{ route('pdf.maturity', $latest) }}" target="_blank"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
            <x-heroicon-o-document-arrow-down class="w-4 h-4" />
            Executive PDF Scorecard
        </a>
    </div>

    @endif

</x-filament-panels::page>
