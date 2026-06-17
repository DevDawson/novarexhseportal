<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PortalHSE — HSE & Sustainability Management Platform</title>
  <meta name="description" content="The all-in-one HSE, EMS, ESG, ESIA, Permit-to-Work, Finance & Payroll platform built for Tanzania's environmental and safety consultancies.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            display: ['"Space Grotesk"', 'sans-serif'],
          },
          colors: {
            brand: { 50:'#eff6ff', 100:'#dbeafe', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8', 900:'#1e3a5f' },
            navy: { 950:'#020817', 900:'#0f172a', 800:'#1e293b', 700:'#334155', 600:'#475569' },
          },
          backgroundImage: {
            'hero-gradient': 'radial-gradient(ellipse 80% 60% at 50% 0%, rgba(59,130,246,.18) 0%, transparent 70%)',
            'card-gradient': 'linear-gradient(135deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,.01) 100%)',
          },
        },
      },
    };
  </script>
  <style>
    html { scroll-behavior: smooth; }
    .log-scroll { animation: log-scroll 24s linear infinite; }
    .log-scroll:hover { animation-play-state: paused; }
    @keyframes log-scroll {
      0%   { transform: translateY(0); }
      100% { transform: translateY(-50%); }
    }
    .glow { box-shadow: 0 0 80px rgba(59,130,246,.15), 0 0 20px rgba(59,130,246,.08); }
    .badge-glow { box-shadow: 0 0 12px rgba(59,130,246,.4); }
    @media (prefers-reduced-motion: reduce) { .log-scroll { animation: none !important; } }
  </style>
</head>
<body class="bg-navy-950 text-white font-sans antialiased">

<!-- ═══════════ NAVBAR ═══════════ -->
<header class="fixed top-0 inset-x-0 z-50 border-b border-white/[.06] backdrop-blur-xl bg-navy-950/80">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2.5">
      <div class="w-7 h-7 rounded-md bg-brand-500 flex items-center justify-center">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285z"/></svg>
      </div>
      <span class="font-display font-700 text-lg tracking-tight">Portal<span class="text-brand-500">HSE</span></span>
    </a>

    <nav class="hidden md:flex items-center gap-8 text-[14px] text-white/60 font-medium">
      <a href="#features" class="hover:text-white transition-colors">Features</a>
      <a href="#modules"  class="hover:text-white transition-colors">Modules</a>
      <a href="#workflow" class="hover:text-white transition-colors">Workflow</a>
      <a href="#compliance" class="hover:text-white transition-colors">Compliance</a>
    </nav>

    <div class="flex items-center gap-3">
      <a href="{{ url('/admin') }}" class="hidden sm:inline-flex text-[13px] font-medium text-white/70 hover:text-white transition-colors px-4 py-2">
        Sign in
      </a>
      <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 text-[13px] font-semibold bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg transition-colors">
        Open Dashboard
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
      </a>
    </div>
  </div>
</header>

<!-- ═══════════ HERO ═══════════ -->
<section class="pt-32 pb-20 relative overflow-hidden">
  <div class="absolute inset-0 bg-hero-gradient pointer-events-none"></div>
  <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[1px] bg-gradient-to-r from-transparent via-brand-500/40 to-transparent"></div>

  <div class="max-w-7xl mx-auto px-6">

    <!-- Eyebrow badge -->
    <div class="flex justify-center mb-8">
      <span class="inline-flex items-center gap-2 text-[12px] font-semibold text-brand-500 border border-brand-500/30 bg-brand-500/10 px-4 py-1.5 rounded-full badge-glow">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
        Built for HSE &amp; Sustainability Consultancies · Tanzania
      </span>
    </div>

    <!-- Headline -->
    <div class="text-center max-w-4xl mx-auto mb-6">
      <h1 class="font-display text-5xl md:text-6xl lg:text-7xl font-800 leading-[1.05] tracking-tight mb-6">
        One platform for every
        <span class="bg-gradient-to-r from-brand-500 to-blue-300 bg-clip-text text-transparent"> HSE obligation</span>
        <br class="hidden md:block">you carry on site.
      </h1>
      <p class="text-lg md:text-xl text-white/55 leading-relaxed max-w-2xl mx-auto">
        Incident management, risk assessment, permits, EMS, ESG, ESIA, audits, invoicing,
        and payroll — connected in a single role-protected workspace your whole team can trust.
      </p>
    </div>

    <!-- CTAs -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
      <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-[15px] px-7 py-3.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40">
        Sign in to workspace
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
      </a>
      <a href="#modules" class="inline-flex items-center gap-2 text-white/70 hover:text-white font-medium text-[15px] px-7 py-3.5 rounded-xl border border-white/10 hover:border-white/20 transition-all">
        Explore modules
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
      </a>
    </div>

    <!-- Hero visual: Activity feed -->
    <div class="relative max-w-3xl mx-auto glow rounded-2xl border border-white/[.08] bg-navy-900 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-3.5 border-b border-white/[.07] bg-navy-800/50">
        <div class="flex items-center gap-2">
          <div class="flex gap-1.5">
            <div class="w-3 h-3 rounded-full bg-white/10"></div>
            <div class="w-3 h-3 rounded-full bg-white/10"></div>
            <div class="w-3 h-3 rounded-full bg-white/10"></div>
          </div>
          <span class="text-[12px] font-mono text-white/40 ml-2">portal.novarex.co.tz — Operations Feed</span>
        </div>
        <span class="flex items-center gap-1.5 text-[11px] font-mono text-emerald-400/80">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Live
        </span>
      </div>

      <div class="grid grid-cols-12 h-72 overflow-hidden font-mono text-[12px] relative">
        <!-- Left: live feed -->
        <div class="col-span-12 md:col-span-8 h-72 overflow-hidden relative border-r border-white/[.06]">
          <div class="log-scroll absolute inset-x-0 top-0">
            @php
              $feed = [
                ['time'=>'08:42','module'=>'INC','color'=>'bg-red-500/20 text-red-400','ref'=>'INC-2026-041','msg'=>'Near miss reported · Mwanza Gold Plant'],
                ['time'=>'09:01','module'=>'PTW','color'=>'bg-yellow-500/20 text-yellow-400','ref'=>'PTW-2026-019','msg'=>'Permit issued · Hot Work · Building A'],
                ['time'=>'09:15','module'=>'INV','color'=>'bg-brand-500/20 text-brand-400','ref'=>'INV-2026-0023','msg'=>'Invoice sent to client · TZS 43,660,000'],
                ['time'=>'09:44','module'=>'HIRA','color'=>'bg-orange-500/20 text-orange-400','ref'=>'HR-2026-008','msg'=>'Risk rating updated → 9 (High)'],
                ['time'=>'10:03','module'=>'EMS','color'=>'bg-emerald-500/20 text-emerald-400','ref'=>'EMS-2026-012','msg'=>'Environmental aspect assessed · Significant'],
                ['time'=>'10:30','module'=>'ESG','color'=>'bg-purple-500/20 text-purple-400','ref'=>'ESG-2026-003','msg'=>'ESG target updated · 45% emissions reduction'],
                ['time'=>'11:12','module'=>'AUD','color'=>'bg-cyan-500/20 text-cyan-400','ref'=>'AUD-2026-007','msg'=>'ISO 45001 audit finding closed · NC-019'],
                ['time'=>'11:48','module'=>'PAY','color'=>'bg-emerald-500/20 text-emerald-400','ref'=>'PAY-2026-06','msg'=>'Payroll processed · PAYE + NSSF + NHIF + WCF'],
                ['time'=>'12:30','module'=>'ESIA','color'=>'bg-brand-500/20 text-brand-400','ref'=>'ESIA-2026-002','msg'=>'ESIA report v1.2 submitted for review'],
                ['time'=>'13:05','module'=>'CON','color'=>'bg-yellow-500/20 text-yellow-400','ref'=>'CON-2026-011','msg'=>'Consultant proforma verified · Awaiting EFD'],
                ['time'=>'14:20','module'=>'PTW','color'=>'bg-yellow-500/20 text-yellow-400','ref'=>'PTW-2026-020','msg'=>'Permit closed · Confined Space Entry · Safe'],
                ['time'=>'15:10','module'=>'INC','color'=>'bg-red-500/20 text-red-400','ref'=>'INC-2026-042','msg'=>'CAPA action completed · Verified by HSE Lead'],
              ];
              $feed = array_merge($feed, $feed);
            @endphp
            @foreach($feed as $row)
            <div class="flex items-center gap-3 px-5 py-2.5 border-b border-white/[.04] hover:bg-white/[.02] transition-colors">
              <span class="text-white/25 shrink-0 w-10">{{ $row['time'] }}</span>
              <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded {{ $row['color'] }}">{{ $row['module'] }}</span>
              <span class="text-white/35 shrink-0 hidden lg:inline w-32 truncate">{{ $row['ref'] }}</span>
              <span class="text-white/70 truncate">{{ $row['msg'] }}</span>
            </div>
            @endforeach
          </div>
          <div class="absolute inset-x-0 top-0 h-8 bg-gradient-to-b from-navy-900 to-transparent pointer-events-none"></div>
          <div class="absolute inset-x-0 bottom-0 h-8 bg-gradient-to-t from-navy-900 to-transparent pointer-events-none"></div>
        </div>
        <!-- Right: mini stats panel -->
        <div class="hidden md:flex flex-col col-span-4 p-5 gap-4">
          <div>
            <div class="text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1">Open Incidents</div>
            <div class="font-display text-3xl font-700 text-white">3</div>
            <div class="text-[11px] text-red-400 mt-0.5">2 high severity</div>
          </div>
          <div class="h-px bg-white/[.06]"></div>
          <div>
            <div class="text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1">Active Permits</div>
            <div class="font-display text-3xl font-700 text-white">7</div>
            <div class="text-[11px] text-yellow-400 mt-0.5">2 expiring today</div>
          </div>
          <div class="h-px bg-white/[.06]"></div>
          <div>
            <div class="text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1">Invoiced (Jun)</div>
            <div class="font-display text-2xl font-700 text-white">TZS 87M</div>
            <div class="text-[11px] text-emerald-400 mt-0.5">62% collected</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Social proof strip -->
    <div class="mt-10 flex flex-wrap items-center justify-center gap-6 text-[12px] font-medium text-white/35">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-400/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        ISO 45001 aligned
      </div>
      <div class="w-px h-4 bg-white/10"></div>
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-400/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        ISO 14001 aligned
      </div>
      <div class="w-px h-4 bg-white/10"></div>
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-400/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Tanzania TRA / EFD / VFD compliant
      </div>
      <div class="w-px h-4 bg-white/10"></div>
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-400/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        PAYE · NSSF · NHIF · WCF auto-calculated
      </div>
    </div>
  </div>
</section>

<!-- ═══════════ STATS ═══════════ -->
<section class="border-y border-white/[.06] bg-navy-900/40">
  <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
    @php
      $stats = [
        ['value'=>'11+', 'label'=>'Compliance modules'],
        ['value'=>'8',   'label'=>'Role-based access levels'],
        ['value'=>'PDF', 'label'=>'Every record exports'],
        ['value'=>'4',   'label'=>'ISO standards supported'],
      ];
    @endphp
    @foreach($stats as $s)
    <div class="text-center">
      <div class="font-display text-4xl font-800 text-white mb-1">{{ $s['value'] }}</div>
      <div class="text-[13px] text-white/40 font-medium">{{ $s['label'] }}</div>
    </div>
    @endforeach
  </div>
</section>

<!-- ═══════════ FEATURES ═══════════ -->
<section id="features" class="py-24">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center max-w-2xl mx-auto mb-16">
      <p class="text-[12px] font-semibold text-brand-500 uppercase tracking-widest mb-3">Everything in one place</p>
      <h2 class="font-display text-4xl md:text-5xl font-800 leading-tight mb-4">Built for how HSE consultancies actually operate</h2>
      <p class="text-white/50 text-lg leading-relaxed">Every workflow — from the first site inspection to the final invoice — handled without spreadsheets or email chains.</p>
    </div>

    @php
      $features = [
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
          'color' => 'text-red-400 bg-red-400/10',
          'title' => 'Incident Management',
          'desc'  => 'Report, investigate, and close out incidents with severity scoring, CAPA tracking, and full audit trail. Lessons learned automatically linked.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5"/>',
          'color' => 'text-orange-400 bg-orange-400/10',
          'title' => 'Risk Assessment (HAZID + HAZOP + HIRA)',
          'desc'  => 'Three integrated risk frameworks. HAZID register with risk scores, full HAZOP study nodes with guidewords, and HIRA sheets with PDF export.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>',
          'color' => 'text-yellow-400 bg-yellow-400/10',
          'title' => 'Permit to Work (PTW)',
          'desc'  => 'Multi-step permit authorization with checklist items, approvals chain, and automatic status progression from Draft to Closed. PDF certificate included.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12.75 3.03v.568c0 .334.148.65.405.864l1.068.89c.442.369.535 1.01.216 1.49l-.51.766a2.25 2.25 0 01-1.161.886l-.143.048a1.107 1.107 0 00-.57 1.664c.369.555.169 1.307-.427 1.605L9 13.125l.423 1.059a.956.956 0 01-1.652.928l-.679-.906a1.125 1.125 0 00-1.906.172L4.5 15.75l-.612.153M12.75 3.031a9 9 0 00-8.862 12.872M12.75 3.031A9 9 0 0121.75 12c0 .352-.021.699-.063 1.041M12.75 3.031A9 9 0 0120.148 18"/>',
          'color' => 'text-emerald-400 bg-emerald-400/10',
          'title' => 'Environmental Management (EMS)',
          'desc'  => 'Environmental aspects & impacts, legal register, monitoring records, waste tracking, spill reports, and environmental permits — all ISO 14001 structured.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>',
          'color' => 'text-purple-400 bg-purple-400/10',
          'title' => 'ESG Management',
          'desc'  => 'Stakeholder engagement, grievance register, social indicators, governance policies, ethics incidents, and ESG target tracking with PDF summary report.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
          'color' => 'text-brand-400 bg-brand-400/10',
          'title' => 'EIA / ESIA (12-Step)',
          'desc'  => 'Complete 12-step ESIA process: screening, scoping, baseline data, impact assessment, mitigation actions, monitoring, and regulatory submissions — with PDF report.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285z"/>',
          'color' => 'text-cyan-400 bg-cyan-400/10',
          'title' => 'Audit Management (AMS)',
          'desc'  => 'ISO 9001/14001/45001/50001 internal audits with checklists, non-conformity register, full RCA (5-Whys/Fishbone), CAPA actions, and comprehensive PDF audit report.',
        ],
        [
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>',
          'color' => 'text-emerald-400 bg-emerald-400/10',
          'title' => 'Finance, Invoicing & Payroll',
          'desc'  => 'Client invoices with PDF/email/WhatsApp delivery, consultant payment requests with EFD/VFD compliance, field expenses, petty cash, and full Tanzania statutory payroll.',
        ],
      ];
    @endphp

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach($features as $f)
      <div class="group p-6 rounded-2xl border border-white/[.07] bg-card-gradient bg-navy-900/30 hover:border-white/[.14] hover:bg-navy-900/60 transition-all duration-300">
        <div class="w-10 h-10 rounded-xl {{ $f['color'] }} flex items-center justify-center mb-4">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $f['icon'] !!}</svg>
        </div>
        <h3 class="font-display font-700 text-[15px] text-white mb-2 leading-snug">{{ $f['title'] }}</h3>
        <p class="text-[13px] text-white/45 leading-relaxed">{{ $f['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ═══════════ HOW IT WORKS ═══════════ -->
<section id="workflow" class="py-24 bg-navy-900/40 border-y border-white/[.06]">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center max-w-2xl mx-auto mb-16">
      <p class="text-[12px] font-semibold text-brand-500 uppercase tracking-widest mb-3">How it works</p>
      <h2 class="font-display text-4xl md:text-5xl font-800 leading-tight mb-4">From project open to final payment</h2>
      <p class="text-white/50 text-lg">Everything connected — open one project, every module is already attached to it.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8 mb-16">
      @php
        $steps = [
          ['num'=>'01','title'=>'Create a project','desc'=>'Start by opening a project record and assigning roles. All incidents, risks, permits, audits, expenses, and invoices you create automatically link to this project.','color'=>'text-brand-500 bg-brand-500/10 border-brand-500/20'],
          ['num'=>'02','title'=>'Run compliance workflows','desc'=>'Your HSE officers, environmental scientists, and auditors work through their modules — incidents are reported, hazards registered, permits issued, audits conducted — all with PDF exports.','color'=>'text-purple-400 bg-purple-400/10 border-purple-400/20'],
          ['num'=>'03','title'=>'Invoice and get paid','desc'=>'Accountant creates the client invoice, exports PDF, and sends via email or WhatsApp. Consultant payments go through the Proforma → EFD/VFD → Payment compliance workflow before any money moves.','color'=>'text-emerald-400 bg-emerald-400/10 border-emerald-400/20'],
        ];
      @endphp
      @foreach($steps as $s)
      <div class="relative p-8 rounded-2xl border {{ $s['color'] }} bg-navy-900/50">
        <div class="font-display text-5xl font-800 {{ explode(' ', $s['color'])[0] }} opacity-20 mb-4">{{ $s['num'] }}</div>
        <h3 class="font-display font-700 text-xl text-white mb-3">{{ $s['title'] }}</h3>
        <p class="text-white/50 text-[14px] leading-relaxed">{{ $s['desc'] }}</p>
      </div>
      @endforeach
    </div>

    <!-- Project hub diagram -->
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-6">
        <div class="inline-flex items-center gap-3 px-6 py-3.5 rounded-xl border border-brand-500/40 bg-brand-500/10">
          <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
          <span class="font-display font-700 text-white">Project Record — NHSE-2026-001</span>
        </div>
      </div>

      <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
        @php
          $nodes = [
            ['label'=>'Incidents','color'=>'border-red-500/30 text-red-400'],
            ['label'=>'Risk Register','color'=>'border-orange-500/30 text-orange-400'],
            ['label'=>'Permits','color'=>'border-yellow-500/30 text-yellow-400'],
            ['label'=>'EMS / ESG','color'=>'border-emerald-500/30 text-emerald-400'],
            ['label'=>'ESIA / Audits','color'=>'border-cyan-500/30 text-cyan-400'],
            ['label'=>'Finance','color'=>'border-brand-500/30 text-brand-400'],
          ];
        @endphp
        @foreach($nodes as $n)
        <div class="text-center p-4 rounded-xl border {{ $n['color'] }} bg-white/[.02]">
          <div class="text-[12px] font-semibold {{ explode(' ', $n['color'])[1] }}">{{ $n['label'] }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<!-- ═══════════ MODULES DEEP DIVE ═══════════ -->
<section id="modules" class="py-24">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center max-w-2xl mx-auto mb-16">
      <p class="text-[12px] font-semibold text-brand-500 uppercase tracking-widest mb-3">Module breakdown</p>
      <h2 class="font-display text-4xl md:text-5xl font-800 leading-tight mb-4">11 HSE navigation groups. Every workflow covered.</h2>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <!-- Left column -->
      <div class="space-y-4">
        @php
          $left = [
            ['group'=>'HSE System',            'items'=>['Projects & CAPA', 'Lessons Learned']],
            ['group'=>'Incident Management',   'items'=>['Incident Reports', 'Severity Scoring', 'CAPA Actions', 'Lessons Learned']],
            ['group'=>'Risk Assessment (HAZID)','items'=>['Risk Register', 'Likelihood × Severity Matrix', 'Risk Treatment Plan']],
            ['group'=>'Risk Assessment (HAZOP)','items'=>['HAZOP Studies', 'Node Analysis', 'Guideword Review', 'PDF Study Report']],
            ['group'=>'HIRA',                   'items'=>['Hazard Identification', 'Initial & Residual Risk', 'HIRA PDF Export']],
            ['group'=>'Permit to Work (PTW)',   'items'=>['Multi-Step PTW Workflow', 'Checklists & Approvals', 'Permit Certificate PDF']],
          ];
        @endphp
        @foreach($left as $mod)
        <div class="p-5 rounded-xl border border-white/[.07] bg-navy-900/30">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-display font-700 text-[14px] text-white">{{ $mod['group'] }}</h3>
            <span class="text-[10px] font-semibold text-brand-500 bg-brand-500/10 px-2.5 py-0.5 rounded-full">{{ count($mod['items']) }} features</span>
          </div>
          <div class="flex flex-wrap gap-2">
            @foreach($mod['items'] as $item)
            <span class="text-[11px] text-white/40 bg-white/[.04] border border-white/[.06] px-2.5 py-1 rounded-full">{{ $item }}</span>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>

      <!-- Right column -->
      <div class="space-y-4">
        @php
          $right = [
            ['group'=>'Environmental Management (EMS)', 'items'=>['Aspects & Impacts', 'Legal Register', 'Monitoring Records', 'Waste Tracking', 'Spill Reports', 'Env. Permits']],
            ['group'=>'ESG Management',                 'items'=>['Stakeholder Engagement', 'Grievance Register', 'Social Indicators', 'Governance Policies', 'Ethics Incidents', 'ESG Targets']],
            ['group'=>'EIA / ESIA',                     'items'=>['Screening & Scoping', 'Baseline Data', 'Impact Assessment', 'Mitigation Actions', 'Regulatory Submissions', 'ESIA PDF Report']],
            ['group'=>'Environmental Audit',            'items'=>['ISO 14001 Audit', 'Checklists & Findings', 'Env. Audit PDF Report']],
            ['group'=>'Audit Management System (AMS)',  'items'=>['ISO 9001/14001/45001/50001', 'NC Register + RCA', 'CAPA Actions', 'AMS Audit PDF Report']],
            ['group'=>'Finance & Expenses',             'items'=>['Client Invoices (PDF/Email/WA)', 'Consultant Payments (EFD/VFD)', 'Field Expenses', 'Petty Cash', 'Payroll (TZ Statutory)']],
          ];
        @endphp
        @foreach($right as $mod)
        <div class="p-5 rounded-xl border border-white/[.07] bg-navy-900/30">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-display font-700 text-[14px] text-white">{{ $mod['group'] }}</h3>
            <span class="text-[10px] font-semibold text-brand-500 bg-brand-500/10 px-2.5 py-0.5 rounded-full">{{ count($mod['items'] ) }} features</span>
          </div>
          <div class="flex flex-wrap gap-2">
            @foreach($mod['items'] as $item)
            <span class="text-[11px] text-white/40 bg-white/[.04] border border-white/[.06] px-2.5 py-1 rounded-full">{{ $item }}</span>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<!-- ═══════════ COMPLIANCE ═══════════ -->
<section id="compliance" class="py-24 bg-navy-900/40 border-y border-white/[.06]">
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-[12px] font-semibold text-brand-500 uppercase tracking-widest mb-4">Built for Tanzania</p>
        <h2 class="font-display text-4xl md:text-5xl font-800 leading-tight mb-6">
          Compliance baked in, not bolted on.
        </h2>
        <p class="text-white/55 text-lg leading-relaxed mb-8">
          From TRA-compliant invoicing and EFD/VFD receipt tracking to statutory payroll
          deductions and ISO-structured audit frameworks — every requirement your regulator
          or auditor will check is already part of the workflow.
        </p>

        <div class="grid grid-cols-2 gap-4">
          @php
            $badges = [
              ['standard'=>'ISO 45001','label'=>'Occupational H&S','color'=>'border-yellow-500/30 text-yellow-400'],
              ['standard'=>'ISO 14001','label'=>'Environmental Mgmt','color'=>'border-emerald-500/30 text-emerald-400'],
              ['standard'=>'ISO 9001', 'label'=>'Quality Management','color'=>'border-brand-500/30 text-brand-400'],
              ['standard'=>'ISO 50001','label'=>'Energy Management','color'=>'border-cyan-500/30 text-cyan-400'],
              ['standard'=>'TRA/EFD',  'label'=>'Tanzania Revenue Auth','color'=>'border-purple-500/30 text-purple-400'],
              ['standard'=>'NEMC',     'label'=>'Environmental Regs','color'=>'border-red-500/30 text-red-400'],
            ];
          @endphp
          @foreach($badges as $b)
          <div class="flex items-center gap-3 p-3.5 rounded-xl border {{ $b['color'] }} bg-white/[.02]">
            <div class="w-8 h-8 rounded-lg bg-white/[.05] flex items-center justify-center text-[10px] font-bold {{ explode(' ', $b['color'])[1] }}">✓</div>
            <div>
              <div class="text-[13px] font-semibold text-white">{{ $b['standard'] }}</div>
              <div class="text-[11px] text-white/35">{{ $b['label'] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Payslip card -->
      <div class="rounded-2xl border border-white/[.08] bg-navy-900 overflow-hidden glow">
        <div class="px-6 py-4 border-b border-white/[.07] flex items-center justify-between bg-navy-800/50">
          <span class="font-mono text-[11px] text-white/40 uppercase tracking-widest">Payslip — June 2026</span>
          <span class="text-[11px] font-medium text-emerald-400 bg-emerald-400/10 px-2.5 py-1 rounded-full">Paid</span>
        </div>
        <div class="px-6 py-2 divide-y divide-white/[.05] font-mono text-[13px]">
          <div class="flex justify-between py-3">
            <span class="text-white/40">Gross Salary</span>
            <span class="text-white font-medium">TZS 2,200,000</span>
          </div>
          <div class="flex justify-between py-3">
            <span class="text-white/40">PAYE (TRA bands)</span>
            <span class="text-red-400">− 264,000</span>
          </div>
          <div class="flex justify-between py-3">
            <span class="text-white/40">NSSF employee (10%)</span>
            <span class="text-red-400">− 220,000</span>
          </div>
          <div class="flex justify-between py-3">
            <span class="text-white/40">NHIF (3%)</span>
            <span class="text-red-400">− 66,000</span>
          </div>
          <div class="flex justify-between py-3 text-white/25 text-[11px]">
            <span>WCF employer (0.5%) — not from staff</span>
            <span>11,000</span>
          </div>
          <div class="flex justify-between py-4">
            <span class="font-display font-700 text-white text-base">Net Salary</span>
            <span class="font-display font-700 text-emerald-400 text-base">TZS 1,650,000</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════ CTA ═══════════ -->
<section class="py-24 relative overflow-hidden">
  <div class="absolute inset-0 bg-hero-gradient pointer-events-none"></div>
  <div class="max-w-3xl mx-auto px-6 text-center relative">
    <p class="text-[12px] font-semibold text-brand-500 uppercase tracking-widest mb-4">Ready to start</p>
    <h2 class="font-display text-4xl md:text-5xl font-800 leading-tight mb-6">
      Your entire HSE operation,<br>on one record.
    </h2>
    <p class="text-white/50 text-lg leading-relaxed mb-10 max-w-xl mx-auto">
      Sign in to your workspace and begin with any module — everything connects automatically as your team adds data.
    </p>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-[15px] px-8 py-4 rounded-xl transition-all shadow-2xl shadow-brand-500/30 hover:shadow-brand-500/50">
        Open your workspace
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ═══════════ FOOTER ═══════════ -->
<footer class="border-t border-white/[.06] bg-navy-900/60">
  <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
    <div class="col-span-2 md:col-span-1">
      <a href="/" class="flex items-center gap-2.5 mb-4">
        <div class="w-6 h-6 rounded-md bg-brand-500 flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285z"/></svg>
        </div>
        <span class="font-display font-700 text-base">Portal<span class="text-brand-500">HSE</span></span>
      </a>
      <p class="text-[13px] text-white/35 leading-relaxed">HSE & Sustainability management platform for Tanzania's consulting industry.</p>
    </div>

    <div>
      <h4 class="text-[12px] font-semibold text-white/50 uppercase tracking-widest mb-4">Platform</h4>
      <ul class="space-y-3 text-[13px] text-white/40">
        <li><a href="#features"    class="hover:text-white transition-colors">Features</a></li>
        <li><a href="#modules"     class="hover:text-white transition-colors">Modules</a></li>
        <li><a href="#workflow"    class="hover:text-white transition-colors">How it works</a></li>
        <li><a href="#compliance"  class="hover:text-white transition-colors">Compliance</a></li>
      </ul>
    </div>

    <div>
      <h4 class="text-[12px] font-semibold text-white/50 uppercase tracking-widest mb-4">Modules</h4>
      <ul class="space-y-3 text-[13px] text-white/40">
        <li>HSE System</li>
        <li>Incident Management</li>
        <li>HAZID · HAZOP · HIRA</li>
        <li>Permit to Work</li>
        <li>EMS · ESG · ESIA</li>
        <li>Audit Management</li>
      </ul>
    </div>

    <div>
      <h4 class="text-[12px] font-semibold text-white/50 uppercase tracking-widest mb-4">Access</h4>
      <ul class="space-y-3 text-[13px] text-white/40">
        <li><a href="{{ url('/admin') }}" class="hover:text-white transition-colors">Sign in to dashboard</a></li>
        <li><a href="{{ route('training.manual') }}" target="_blank" class="hover:text-white transition-colors">Training manual</a></li>
      </ul>
    </div>
  </div>

  <div class="border-t border-white/[.05]">
    <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px] text-white/25">
      <span>PortalHSE · Built by Deeteki · Mwanza, Tanzania</span>
      <span>© {{ date('Y') }} — All rights reserved</span>
    </div>
  </div>
</footer>

</body>
</html>
