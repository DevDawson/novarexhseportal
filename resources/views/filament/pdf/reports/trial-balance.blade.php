<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Trial Balance - {{ $asOf }}</title>
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
    .balanced { color: #16A34A; font-weight: bold; }
    .unbalanced { color: #DC2626; font-weight: bold; }
</style></head>
<body>
<h1>NovarexHSE TZ - Trial Balance</h1>
<h2>As of {{ $asOf }}</h2>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Account Name</th>
            <th>Type</th>
            <th class="right">Debit (TZS)</th>
            <th class="right">Credit (TZS)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $r)
        <tr>
            <td>{{ $r['code'] }}</td>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['type'] }}</td>
            <td class="right">{{ $r['debit'] > 0 ? number_format($r['debit'], 2) : '-' }}</td>
            <td class="right">{{ $r['credit'] > 0 ? number_format($r['credit'], 2) : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="3">TOTALS</td>
            <td class="right">{{ number_format($totalDebit, 2) }}</td>
            <td class="right">{{ number_format($totalCredit, 2) }}</td>
        </tr>
    </tfoot>
</table>

<p style="margin-top: 10px;" class="{{ round($totalDebit, 2) === round($totalCredit, 2) ? 'balanced' : 'unbalanced' }}">
    @if (round($totalDebit, 2) === round($totalCredit, 2))
        Trial Balance is BALANCED (Total Debit = Total Credit).
    @else
        WARNING: Trial Balance is NOT balanced. Difference: TZS {{ number_format(abs($totalDebit - $totalCredit), 2) }}.
    @endif
</p>

<p class="footer">Generated {{ now()->format('d M Y H:i') }} - NovarexHSE TZ - Confidential</p>
</body></html>
