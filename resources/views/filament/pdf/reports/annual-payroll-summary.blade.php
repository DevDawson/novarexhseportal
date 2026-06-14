<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Annual Payroll Summary {{ $year }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #1C2127; }
    h1 { font-size: 13px; margin: 0; color: #3B82F6; }
    .report-title { font-size: 13px; margin: 8px 0 4px; color: #1C2127; text-transform: uppercase; letter-spacing: 0.5px; }
    h2 { font-size: 10px; color: #6B7280; margin: 2px 0 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #3B82F6; color: #fff; padding: 4px 3px; text-align: center; font-size: 7.5px; }
    td { padding: 3px; border-bottom: 1px solid #E5E7EB; text-align: right; }
    td:first-child { text-align: left; font-weight: bold; }
    tr:nth-child(even) td { background: #F9FAFB; }
    .total td { font-weight: bold; border-top: 2px solid #3B82F6; background: #EFF6FF; }
    .footer { font-size: 7px; color: #9CA3AF; text-align: center; margin-top: 10px; }
</style></head>
<body>
@include('filament.pdf.partials.letterhead')
<h1 class="report-title">Annual Payroll Summary</h1>
<h2>Year: {{ $year }}</h2>
<table>
    <thead>
        <tr>
            <th>Month</th><th>Staff</th><th>Gross (TZS)</th><th>PAYE</th>
            <th>NSSF Emp</th><th>NSSF Empr</th><th>WCF</th><th>SDL</th>
            <th>NHIF</th><th>Net (TZS)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $r)
        <tr>
            <td>{{ $r['month'] }}</td>
            <td>{{ $r['staff_count'] }}</td>
            <td>{{ number_format($r['total_gross'],0) }}</td>
            <td>{{ number_format($r['total_paye'],0) }}</td>
            <td>{{ number_format($r['total_nssf'],0) }}</td>
            <td>{{ number_format($r['total_nssf_employer'],0) }}</td>
            <td>{{ number_format($r['total_wcf'],0) }}</td>
            <td>{{ number_format($r['total_sdl'],0) }}</td>
            <td>{{ number_format($r['total_nhif'],0) }}</td>
            <td>{{ number_format($r['total_net'],0) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td>TOTALS</td>
            <td>-</td>
            <td>{{ number_format($rows->sum('total_gross'),0) }}</td>
            <td>{{ number_format($rows->sum('total_paye'),0) }}</td>
            <td>{{ number_format($rows->sum('total_nssf'),0) }}</td>
            <td>{{ number_format($rows->sum('total_nssf_employer'),0) }}</td>
            <td>{{ number_format($rows->sum('total_wcf'),0) }}</td>
            <td>{{ number_format($rows->sum('total_sdl'),0) }}</td>
            <td>{{ number_format($rows->sum('total_nhif'),0) }}</td>
            <td>{{ number_format($rows->sum('total_net'),0) }}</td>
        </tr>
    </tfoot>
</table>
<p class="footer">Generated {{ now()->format('d M Y H:i') }} &mdash; NovarexHSE TZ &mdash; Confidential</p>
</body></html>
