<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }
        .page { padding: 28px 32px; }

        /* Header */
        .doc-meta { text-align: right; font-size: 9px; color: #6b7280; margin-bottom: 6px; }
        .doc-meta .doc-title { font-size: 13px; font-weight: bold; color: #1a1a2e; }

        /* Section */
        .section { margin-bottom: 14px; }
        .section-title { background: #1d4ed8; color: #fff; font-size: 10px; font-weight: bold; padding: 4px 8px; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px 12px; }
        .field { margin-bottom: 5px; }
        .field label { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; display: block; }
        .field span  { font-size: 10px; color: #1a1a2e; font-weight: 500; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 4px; }
        th { background: #eff6ff; color: #1d4ed8; font-weight: bold; padding: 5px 6px; text-align: left; border: 1px solid #dbeafe; }
        td { padding: 5px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-low      { background: #dcfce7; color: #166534; }
        .badge-medium   { background: #fef9c3; color: #854d0e; }
        .badge-high     { background: #ffedd5; color: #9a3412; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-success  { background: #dcfce7; color: #166534; }
        .badge-warning  { background: #fef9c3; color: #854d0e; }
        .badge-danger   { background: #fee2e2; color: #991b1b; }
        .badge-info     { background: #dbeafe; color: #1d4ed8; }
        .badge-gray     { background: #f3f4f6; color: #374151; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; margin-top: 20px; padding-top: 8px; font-size: 8px; color: #9ca3af; display: table; width: 100%; }

        /* Signature row */
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 20px; }
        .sig-box { border-top: 1px solid #374151; padding-top: 4px; font-size: 9px; }
        .sig-box label { color: #6b7280; font-size: 8px; }
    </style>
</head>
<body>
<div class="page">
    @include('filament.pdf.partials.letterhead')
    <div class="doc-meta">
        <div class="doc-title">@yield('doc-title')</div>
        <div>Generated: {{ now()->format('d M Y, H:i') }}</div>
        <div>Ref: @yield('doc-ref')</div>
    </div>

    @yield('content')

    <div class="footer">
        <span>{{ \App\Models\Setting::companyName() }} — Confidential</span>
        <span>{{ \App\Models\Setting::companyTagline() }}</span>
        <span>Generated: {{ now()->format('d M Y') }}</span>
    </div>
</div>
</body>
</html>
