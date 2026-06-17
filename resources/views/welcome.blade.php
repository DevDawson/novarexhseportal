<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NovarexHSE — Operations platform for HSE &amp; Sustainability consultancies</title>
    <meta name="description" content="One platform for project delivery, HSE compliance, finance, and payroll — built for Tanzania's HSE &amp; Sustainability consultancies.">

    {{-- Fonts: Space Grotesk (display) · IBM Plex Sans (body) · IBM Plex Mono (codes / log feed) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN for this static page. For production, move utility classes into your Vite-built CSS. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#1C2127',
                        paper: '#F6F4EF',
                        primary: '#3B82F6',
                        success: '#22C55E',
                        rust: '#C1442D',
                        line: '#DAD4C6',
                        linedark: '#343B45',
                    },
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        body: ['"IBM Plex Sans"', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                },
            },
        };
    </script>

    <style>
        @media (prefers-reduced-motion: reduce) {
            .log-scroll { animation: none !important; }
        }

        .log-scroll {
            animation: log-scroll 22s linear infinite;
        }
        .log-scroll:hover {
            animation-play-state: paused;
        }
        @keyframes log-scroll {
            0%   { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        /* Connector lines for the workflow diagram */
        .spoke::before {
            content: '';
            position: absolute;
            top: -1.25rem;
            left: 50%;
            width: 1px;
            height: 1.25rem;
            background: #DAD4C6;
        }
    </style>
</head>
<body class="bg-paper text-ink font-body antialiased">

    {{-- ============ NAV ============ --}}
    <header class="border-b border-line">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <a href="/" class="flex items-baseline gap-2">
                <span class="font-display text-xl font-700 tracking-tight">Novarex<span class="text-primary">HSE</span></span>
                <span class="hidden sm:inline font-mono text-[11px] uppercase tracking-[0.2em] text-ink/50">v1.0 · TZ</span>
            </a>

            <nav class="hidden md:flex items-center gap-8 font-mono text-[13px] uppercase tracking-[0.12em] text-ink/70">
                <a href="#modules" class="hover:text-ink transition-colors">Modules</a>
                <a href="#workflow" class="hover:text-ink transition-colors">Workflow</a>
                <a href="#payroll" class="hover:text-ink transition-colors">Payroll</a>
            </nav>

            <a href="{{ url('/admin') }}"
               class="font-mono text-[13px] uppercase tracking-[0.12em] border border-ink px-4 py-2 hover:bg-ink hover:text-paper transition-colors">
                Sign in
            </a>
        </div>
    </header>

    {{-- ============ HERO ============ --}}
    <section class="bg-ink text-paper">
        <div class="max-w-6xl mx-auto px-6 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center">

            {{-- Left: thesis --}}
            <div>
                <p class="font-mono text-[12px] uppercase tracking-[0.25em] text-primary mb-5">
                    Operations Platform &middot; Mwanza, Tanzania
                </p>

                <h1 class="font-display text-4xl md:text-5xl font-700 leading-[1.08] mb-6">
                    Run HSE &amp; Sustainability consulting like a site<br class="hidden md:block">
                    you'd pass on the first inspection.
                </h1>

                <p class="text-paper/70 text-base md:text-lg leading-relaxed mb-8 max-w-md">
                    Projects, incidents, risk registers, tenders, invoices, payroll and
                    document control — on one record, with the access controls your
                    MD, accountant, and field officers each actually need.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ url('/admin') }}"
                       class="font-mono text-[13px] uppercase tracking-[0.12em] bg-primary text-white px-6 py-3 hover:bg-primary/90 transition-colors">
                        Sign in to your workspace
                    </a>
                    <a href="#workflow"
                       class="font-mono text-[13px] uppercase tracking-[0.12em] border border-paper/30 px-6 py-3 hover:border-paper transition-colors">
                        See how it fits together
                    </a>
                </div>
            </div>

            {{-- Right: signature element — live operations log --}}
            <div class="border border-linedark rounded-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-linedark font-mono text-[11px] uppercase tracking-[0.2em] text-paper/50">
                    <span>Live Operations Log</span>
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Synced
                    </span>
                </div>

                <div class="h-72 overflow-hidden relative font-mono text-[12.5px] leading-relaxed">
                    <div class="log-scroll absolute inset-x-0 top-0">
                        @php
                            $entries = [
                                ['time' => '08:42', 'ref' => 'NHSE-2026-001', 'tag' => 'INC', 'color' => 'bg-rust',  'text' => 'Near miss reported · Mwanza Gold Plant'],
                                ['time' => '09:15', 'ref' => 'TND-2026-003', 'tag' => 'TND', 'color' => 'bg-primary', 'text' => 'Tender stage → Won · Geita Gold Mining'],
                                ['time' => '10:03', 'ref' => 'NHSE-2026-002', 'tag' => 'ESA', 'color' => 'bg-success',  'text' => 'OHS audit findings submitted for review'],
                                ['time' => '11:30', 'ref' => 'INV-2026-0001', 'tag' => 'INV', 'color' => 'bg-primary', 'text' => 'Invoice sent · TZS 43,660,000'],
                                ['time' => '13:05', 'ref' => 'NHSE-EMP-006', 'tag' => 'PAY', 'color' => 'bg-success',  'text' => 'Payroll processed · PAYE, NSSF, NHIF, WCF'],
                                ['time' => '14:20', 'ref' => 'NHSE-2026-001', 'tag' => 'RSK', 'color' => 'bg-rust',  'text' => 'Risk rating updated → 9 (High)'],
                                ['time' => '15:48', 'ref' => 'NHSE-2026-003', 'tag' => 'DLV', 'color' => 'bg-success',  'text' => 'Training certificate template approved'],
                                ['time' => '16:30', 'ref' => 'EXP-0231', 'tag' => 'EXP', 'color' => 'bg-primary', 'text' => 'Field expense approved · Per diem, fuel'],
                            ];
                            // Duplicate the list so the scroll loop is seamless.
                            $feed = array_merge($entries, $entries);
                        @endphp

                        @foreach ($feed as $entry)
                            <div class="flex items-start gap-3 px-4 py-2.5 border-b border-linedark/60">
                                <span class="text-paper/40 shrink-0">{{ $entry['time'] }}</span>
                                <span class="text-paper/40 shrink-0">{{ $entry['ref'] }}</span>
                                <span class="shrink-0 px-1.5 py-0.5 rounded-sm text-[10px] font-medium text-ink {{ $entry['color'] }}">
                                    {{ $entry['tag'] }}
                                </span>
                                <span class="text-paper/80">{{ $entry['text'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    {{-- fade edges --}}
                    <div class="absolute inset-x-0 top-0 h-6 bg-gradient-to-b from-ink to-transparent pointer-events-none"></div>
                    <div class="absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-ink to-transparent pointer-events-none"></div>
                </div>
            </div>

        </div>

        {{-- Stat strip --}}
        <div class="border-t border-linedark">
            <div class="max-w-6xl mx-auto px-6 py-5 grid grid-cols-2 md:grid-cols-4 gap-6 font-mono text-[12px] uppercase tracking-[0.12em] text-paper/60">
                <div><span class="text-paper text-lg font-display block mb-1">6</span>Core modules</div>
                <div><span class="text-paper text-lg font-display block mb-1">7</span>Role-based access levels</div>
                <div><span class="text-paper text-lg font-display block mb-1">4</span>Statutory deductions auto-calculated</div>
                <div><span class="text-paper text-lg font-display block mb-1">1</span>Record per project</div>
            </div>
        </div>
    </section>

    {{-- ============ MODULES ============ --}}
    <section id="modules" class="max-w-6xl mx-auto px-6 py-20">
        <div class="mb-12 max-w-2xl">
            <p class="font-mono text-[12px] uppercase tracking-[0.25em] text-success mb-3">System Map</p>
            <h2 class="font-display text-3xl md:text-4xl font-700 leading-tight mb-4">
                Six modules. One source of truth.
            </h2>
            <p class="text-ink/65 leading-relaxed">
                Each module is built around the records your teams already use day to
                day — projects, claims, leave forms, invoices — connected so nothing
                gets re-typed between departments.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-px bg-line border border-line">
            @php
                $modules = [
                    ['code' => 'MOD-01', 'title' => 'Dashboard & Core Admin', 'desc' => 'Role-based access for MD, HR, Finance, BD, IT, HSE, and Secretary, plus a corporate document register with expiry alerts.'],
                    ['code' => 'MOD-02', 'title' => 'HSE & Technical Operations', 'desc' => 'Projects, ESIA &amp; audits, incident reports with severity scoring, and a live risk register with calculated ratings.'],
                    ['code' => 'MOD-03', 'title' => 'Business Development', 'desc' => 'Client CRM and a tender pipeline tracked from identified through prequalification to won or lost.'],
                    ['code' => 'MOD-04', 'title' => 'Finance & Expenses', 'desc' => 'Invoicing with line items and VAT, field expense claims with MD/Accountant approval, and a petty cash book.'],
                    ['code' => 'MOD-05', 'title' => 'HR & Payroll', 'desc' => 'Staff registry, leave requests with approval workflow, and payroll with Tanzania statutory deductions built in.'],
                    ['code' => 'MOD-06', 'title' => 'Project Deliverables', 'desc' => 'Document control with revision history, review status, and due dates across every active project.'],
                ];
            @endphp

            @foreach ($modules as $module)
                <div class="bg-paper p-7">
                    <p class="font-mono text-[11px] uppercase tracking-[0.2em] text-primary mb-4">{{ $module['code'] }}</p>
                    <h3 class="font-display text-lg font-600 mb-2">{{ $module['title'] }}</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">{!! $module['desc'] !!}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ WORKFLOW ============ --}}
    <section id="workflow" class="bg-ink text-paper">
        <div class="max-w-6xl mx-auto px-6 py-20">
            <div class="mb-16 max-w-2xl">
                <p class="font-mono text-[12px] uppercase tracking-[0.25em] text-primary mb-3">How it connects</p>
                <h2 class="font-display text-3xl md:text-4xl font-700 leading-tight mb-4">
                    Open a project. Everything is already attached.
                </h2>
                <p class="text-paper/60 leading-relaxed">
                    A project record carries its own incidents, risks, audits, expenses,
                    invoices, and deliverables — so the field officer, the accountant,
                    and the MD are always looking at the same file.
                </p>
            </div>

            {{-- Hub --}}
            <div class="flex justify-center mb-5">
                <div class="border border-primary px-6 py-4 text-center">
                    <p class="font-mono text-[11px] uppercase tracking-[0.2em] text-primary mb-1">Project Record</p>
                    <p class="font-display text-base font-600">NHSE-2026-001</p>
                </div>
            </div>

            {{-- Connector line --}}
            <div class="flex justify-center">
                <div class="w-px h-5 bg-paper/20"></div>
            </div>

            {{-- Spokes --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 pt-5 border-t border-paper/10">
                @php
                    $spokes = [
                        'Incidents', 'Risk Register', 'ESIA / Audits', 'Field Expenses', 'Invoices', 'Deliverables',
                    ];
                @endphp

                @foreach ($spokes as $spoke)
                    <div class="spoke relative border border-paper/15 px-4 py-5 text-center hover:border-primary/60 transition-colors">
                        <p class="font-mono text-[12px] uppercase tracking-[0.12em] text-paper/80">{{ $spoke }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ PAYROLL HIGHLIGHT ============ --}}
    <section id="payroll" class="max-w-6xl mx-auto px-6 py-20">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <p class="font-mono text-[12px] uppercase tracking-[0.25em] text-success mb-3">Built for Tanzania</p>
                <h2 class="font-display text-3xl md:text-4xl font-700 leading-tight mb-4">
                    Payroll that already knows<br>PAYE, NSSF, NHIF, and WCF.
                </h2>
                <p class="text-ink/65 leading-relaxed mb-6 max-w-md">
                    Enter a gross salary and the statutory breakdown calculates itself
                    against current TRA bands — with NSSF, WCF, and NHIF rates you can
                    adjust as regulations change.
                </p>
                <a href="{{ url('/admin') }}"
                   class="font-mono text-[13px] uppercase tracking-[0.12em] border border-ink px-6 py-3 hover:bg-ink hover:text-paper transition-colors">
                    Open payroll workspace
                </a>
            </div>

            {{-- Payslip-style breakdown card --}}
            <div class="border border-line bg-white">
                <div class="px-6 py-4 border-b border-line flex items-center justify-between">
                    <p class="font-mono text-[11px] uppercase tracking-[0.2em] text-ink/50">Payslip Preview</p>
                    <p class="font-mono text-[11px] text-ink/50">June 2026</p>
                </div>
                <dl class="px-6 py-2 divide-y divide-line font-mono text-sm">
                    <div class="flex justify-between py-3">
                        <dt class="text-ink/60">Gross salary</dt>
                        <dd>TZS 2,200,000</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-ink/60">PAYE</dt>
                        <dd class="text-rust">- 264,000</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-ink/60">NSSF (employee, 10%)</dt>
                        <dd class="text-rust">- 220,000</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-ink/60">NHIF (3%)</dt>
                        <dd class="text-rust">- 66,000</dd>
                    </div>
                    <div class="flex justify-between py-3 text-ink/40 text-xs">
                        <dt>WCF (employer, 0.5%) — not deducted from staff</dt>
                        <dd>11,000</dd>
                    </div>
                    <div class="flex justify-between py-4 font-display text-base font-600">
                        <dt>Net salary</dt>
                        <dd class="text-success">TZS 1,650,000</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- ============ FOOTER / CTA ============ --}}
    <footer class="bg-ink text-paper">
        <div class="max-w-6xl mx-auto px-6 py-16 text-center">
            <h2 class="font-display text-2xl md:text-3xl font-700 mb-4">
                Bring your operations onto one record.
            </h2>
            <p class="text-paper/60 mb-8 max-w-md mx-auto">
                Sign in to your workspace, or talk to us about onboarding your team.
            </p>
            <a href="{{ url('/admin') }}"
               class="font-mono text-[13px] uppercase tracking-[0.12em] bg-primary text-white px-6 py-3 hover:bg-primary/90 transition-colors">
                Sign in to your workspace
            </a>
        </div>
        <div class="border-t border-linedark">
            <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 font-mono text-[11px] uppercase tracking-[0.15em] text-paper/40">
                <span>NovarexHSE &middot; Built by Deeteki</span>
                <span>&copy; {{ date('Y') }} &mdash; Mwanza, Tanzania</span>
            </div>
        </div>
    </footer>

</body>
</html>
