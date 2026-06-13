<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Financial Summary - {{ $summary['period']->format('F Y') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin-top: 24px; margin-bottom: 8px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .subtitle { color: #6b7280; margin-top: 4px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td { padding: 4px 0; border-bottom: 1px solid #f3f4f6; }
        td.label { width: 70%; }
        td.value { width: 30%; text-align: right; }
        .total-row td { font-weight: bold; border-top: 1px solid #9ca3af; }
        .header-box { display: table; width: 100%; margin-bottom: 16px; }
        .header-cell { display: table-cell; width: 33%; text-align: center; padding: 8px; border: 1px solid #e5e7eb; }
        .header-cell .amount { font-size: 16px; font-weight: bold; }
        .positive { color: #16a34a; }
        .negative { color: #dc2626; }
    </style>
</head>
<body>

    {{-- Placeholder header - replace with company letterhead / logo via base64 image if needed --}}
    <h1>{{ config('app.name', 'Webmaster Crew ERP') }}</h1>
    <p class="subtitle">Monthly Financial Summary &mdash; {{ $summary['period']->format('F Y') }}</p>

    <div class="header-box">
        <div class="header-cell">
            Revenue<br>
            <span class="amount positive">TZS {{ number_format($summary['revenue_total'], 2) }}</span>
        </div>
        <div class="header-cell">
            Total Outflows<br>
            <span class="amount negative">TZS {{ number_format($summary['total_outflows'], 2) }}</span>
        </div>
        <div class="header-cell">
            Net Position<br>
            <span class="amount {{ $summary['net_position'] >= 0 ? 'positive' : 'negative' }}">
                TZS {{ number_format($summary['net_position'], 2) }}
            </span>
        </div>
    </div>

    <h2>Payroll &amp; Statutory Obligations</h2>
    <table>
        <tr><td class="label">Staff Paid</td><td class="value">{{ $summary['payroll']->staff_count }}</td></tr>
        <tr><td class="label">Total Gross Salaries</td><td class="value">{{ number_format($summary['payroll']->total_gross, 2) }}</td></tr>
        <tr><td class="label">Total Net Salaries (Take-Home)</td><td class="value">{{ number_format($summary['payroll']->total_net, 2) }}</td></tr>
        <tr><td class="label">PAYE (TRA)</td><td class="value">{{ number_format($summary['payroll']->total_paye, 2) }}</td></tr>
        <tr><td class="label">NSSF - Employee</td><td class="value">{{ number_format($summary['payroll']->total_nssf_employee, 2) }}</td></tr>
        <tr><td class="label">NSSF - Employer</td><td class="value">{{ number_format($summary['payroll']->total_nssf_employer, 2) }}</td></tr>
        <tr><td class="label">WCF</td><td class="value">{{ number_format($summary['payroll']->total_wcf, 2) }}</td></tr>
        <tr><td class="label">NHIF</td><td class="value">{{ number_format($summary['payroll']->total_nhif, 2) }}</td></tr>
        <tr class="total-row"><td class="label">Total Statutory Obligations</td><td class="value">{{ number_format($summary['statutory_total'], 2) }}</td></tr>
    </table>

    <h2>Field Expenses by Category</h2>
    <table>
        @forelse ($summary['field_expenses_by_category'] as $category => $total)
            <tr>
                <td class="label">{{ ucfirst(str_replace('_', ' ', $category)) }}</td>
                <td class="value">{{ number_format($total, 2) }}</td>
            </tr>
        @empty
            <tr><td class="label" colspan="2">No approved field expenses for this period.</td></tr>
        @endforelse
        <tr class="total-row"><td class="label">Total Field Expenses</td><td class="value">{{ number_format($summary['field_expenses_total'], 2) }}</td></tr>
        <tr class="total-row"><td class="label">Petty Cash Outflows</td><td class="value">{{ number_format($summary['petty_cash_total'], 2) }}</td></tr>
    </table>

    <p class="subtitle" style="margin-top: 32px;">
        Generated on {{ now()->format('d M Y H:i') }} &mdash; For internal management use only.
    </p>

</body>
</html>
