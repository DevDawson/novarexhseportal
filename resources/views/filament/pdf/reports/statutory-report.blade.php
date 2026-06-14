<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>{{ $title }} - {{ $period->format('F Y') }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1C2127; }
    .header { margin-bottom: 10px; }
    h1 { font-size: 13px; margin: 0; color: #3B82F6; }
    h2 { font-size: 10px; color: #6B7280; margin: 2px 0 0; }
    .authority { font-size: 9px; color: #6B7280; margin: 4px 0 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #F59E0B; color: #1C2127; padding: 4px 3px; text-align: left; font-size: 8px; }
    td { padding: 3px; border-bottom: 1px solid #E5E7EB; }
    tr:nth-child(even) td { background: #F9FAFB; }
    .total td { font-weight: bold; border-top: 2px solid #F59E0B; background: #FFFBEB; }
    .footer { font-size: 7px; color: #9CA3AF; text-align: center; margin-top: 10px; }
    .right { text-align: right; }
</style></head>
<body>
<div class="header">
    <h1>NovarexHSE TZ &mdash; {{ $title }}</h1>
    <h2>Period: {{ $period->format('F Y') }}</h2>
    <p class="authority">Payable to: {{ $authority }}</p>
</div>
<table>
    <thead>
        <tr>
            @foreach ($columns as $key => $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $r)
        <tr>
            @foreach ($columns as $key => $label)
                @if (is_numeric($r[$key] ?? null))
                    <td class="right">{{ number_format($r[$key], 2) }}</td>
                @else
                    <td>{{ $r[$key] ?? '-' }}</td>
                @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="{{ count($columns) - 1 }}">TOTAL (TZS)</td>
            <td class="right">{{ number_format($rows->sum($total_key), 2) }}</td>
        </tr>
    </tfoot>
</table>
<p class="footer">Generated {{ now()->format('d M Y H:i') }} &mdash; NovarexHSE TZ &mdash; For statutory remittance only</p>
</body></html>
