<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Department Payroll Report - {{ $period->format('F Y') }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1C2127; }
    h1 { font-size: 14px; margin: 0; color: #3B82F6; }
    h2 { font-size: 10px; color: #6B7280; margin: 2px 0 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #3B82F6; color: #fff; padding: 5px 4px; text-align: left; font-size: 9px; }
    td { padding: 5px 4px; border-bottom: 1px solid #E5E7EB; }
    tr:nth-child(even) td { background: #F9FAFB; }
    .total td { font-weight: bold; border-top: 2px solid #3B82F6; background: #EFF6FF; }
    .footer { font-size: 7px; color: #9CA3AF; text-align: center; margin-top: 12px; }
    .right { text-align: right; }
</style></head>
<body>
<h1>NovarexHSE TZ - Department Payroll Report</h1>
<h2>Period: {{ $period->format('F Y') }}</h2>

<table>
    <thead>
        <tr>
            <th>Department</th>
            <th class="right">Staff Count</th>
            <th class="right">Total Gross (TZS)</th>
            <th class="right">PAYE</th>
            <th class="right">NSSF (Employee)</th>
            <th class="right">NHIF</th>
            <th class="right">SDL</th>
            <th class="right">Total Net (TZS)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $r)
        <tr>
            <td>{{ $r['department'] }}</td>
            <td class="right">{{ $r['staff_count'] }}</td>
            <td class="right">{{ number_format($r['total_gross'], 2) }}</td>
            <td class="right">{{ number_format($r['total_paye'], 2) }}</td>
            <td class="right">{{ number_format($r['total_nssf'], 2) }}</td>
            <td class="right">{{ number_format($r['total_nhif'], 2) }}</td>
            <td class="right">{{ number_format($r['total_sdl'], 2) }}</td>
            <td class="right"><strong>{{ number_format($r['total_net'], 2) }}</strong></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="2">COMPANY TOTAL</td>
            <td class="right">{{ number_format($rows->sum('total_gross'), 2) }}</td>
            <td class="right">{{ number_format($rows->sum('total_paye'), 2) }}</td>
            <td class="right">{{ number_format($rows->sum('total_nssf'), 2) }}</td>
            <td class="right">{{ number_format($rows->sum('total_nhif'), 2) }}</td>
            <td class="right">{{ number_format($rows->sum('total_sdl'), 2) }}</td>
            <td class="right">{{ number_format($rows->sum('total_net'), 2) }}</td>
        </tr>
    </tfoot>
</table>

<p class="footer">Generated {{ now()->format('d M Y H:i') }} - NovarexHSE TZ - Confidential</p>
</body></html>
