<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Payroll Register - {{ $period->format('F Y') }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1C2127; }
    h1 { font-size: 14px; margin: 0; color: #3B82F6; }
    .report-title { font-size: 13px; margin: 8px 0 4px; color: #1C2127; text-transform: uppercase; letter-spacing: 0.5px; }
    h2 { font-size: 10px; color: #6B7280; margin: 2px 0 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #3B82F6; color: #fff; padding: 4px 3px; text-align: left; font-size: 8px; }
    td { padding: 3px; border-bottom: 1px solid #E5E7EB; }
    tr:nth-child(even) td { background: #F9FAFB; }
    .total td { font-weight: bold; border-top: 2px solid #3B82F6; background: #EFF6FF; }
    .footer { font-size: 7px; color: #9CA3AF; text-align: center; margin-top: 10px; }
    .right { text-align: right; }
</style></head>
<body>
@include('filament.pdf.partials.letterhead')
<h1 class="report-title">Payroll Register</h1>
<h2>Period: {{ $period->format('F Y') }}</h2>
<table>
    <thead>
        <tr>
            <th>Staff No</th><th>Name</th><th>Dept</th><th>Type</th>
            <th class="right">Gross</th><th class="right">PAYE</th>
            <th class="right">NSSF</th><th class="right">NHIF</th>
            <th class="right">Loan</th><th class="right">Other</th>
            <th class="right">Net</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $r)
        <tr>
            <td>{{ $r['staff_no'] }}</td><td>{{ $r['name'] }}</td>
            <td>{{ $r['department'] }}</td><td>{{ $r['employment_type'] }}</td>
            <td class="right">{{ number_format($r['gross_salary'],0) }}</td>
            <td class="right">{{ number_format($r['paye'],0) }}</td>
            <td class="right">{{ number_format($r['nssf'],0) }}</td>
            <td class="right">{{ number_format($r['nhif'],0) }}</td>
            <td class="right">{{ number_format($r['loan_deduction'],0) }}</td>
            <td class="right">{{ number_format($r['other_deductions'],0) }}</td>
            <td class="right"><strong>{{ number_format($r['net_salary'],0) }}</strong></td>
            <td>{{ ucfirst($r['payment_status']) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="4">TOTALS (TZS)</td>
            <td class="right">{{ number_format($rows->sum('gross_salary'),0) }}</td>
            <td class="right">{{ number_format($rows->sum('paye'),0) }}</td>
            <td class="right">{{ number_format($rows->sum('nssf'),0) }}</td>
            <td class="right">{{ number_format($rows->sum('nhif'),0) }}</td>
            <td class="right">{{ number_format($rows->sum('loan_deduction'),0) }}</td>
            <td class="right">{{ number_format($rows->sum('other_deductions'),0) }}</td>
            <td class="right">{{ number_format($rows->sum('net_salary'),0) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
<p class="footer">Generated {{ now()->format('d M Y H:i') }} &mdash; NovarexHSE TZ &mdash; Confidential</p>
</body></html>
