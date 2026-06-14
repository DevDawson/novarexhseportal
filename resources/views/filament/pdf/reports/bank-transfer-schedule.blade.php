<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Bank Transfer Schedule - {{ $period->format('F Y') }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1C2127; }
    h1 { font-size: 14px; margin: 0; color: #3B82F6; }
    .report-title { font-size: 13px; margin: 8px 0 4px; color: #1C2127; text-transform: uppercase; letter-spacing: 0.5px; }
    h2 { font-size: 10px; color: #6B7280; margin: 2px 0 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #3B82F6; color: #fff; padding: 5px 4px; text-align: left; font-size: 8.5px; }
    td { padding: 4px; border-bottom: 1px solid #E5E7EB; }
    tr:nth-child(even) td { background: #F9FAFB; }
    .total td { font-weight: bold; border-top: 2px solid #3B82F6; background: #EFF6FF; }
    .footer { font-size: 7px; color: #9CA3AF; text-align: center; margin-top: 12px; }
    .right { text-align: right; }
    .status-pending { color: #D97706; font-weight: bold; }
    .status-paid { color: #16A34A; font-weight: bold; }
</style></head>
<body>
@include('filament.pdf.partials.letterhead')
<h1 class="report-title">Bank Transfer Schedule</h1>
<h2>Period: {{ $period->format('F Y') }} &mdash; {{ $rows->count() }} staff to be paid</h2>

<table>
    <thead>
        <tr>
            <th>Staff No</th>
            <th>Employee Name</th>
            <th>Bank Name</th>
            <th>Account Number</th>
            <th>Mobile Number</th>
            <th class="right">Net Salary (TZS)</th>
            <th>Payment Reference</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $r)
        <tr>
            <td>{{ $r['staff_no'] }}</td>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['bank_name'] ?? '-' }}</td>
            <td>{{ $r['account_no'] ?? '-' }}</td>
            <td>{{ $r['mobile_number'] ?? '-' }}</td>
            <td class="right"><strong>{{ number_format($r['net_salary'], 2) }}</strong></td>
            <td>{{ $r['payment_reference'] ?? '-' }}</td>
            <td class="status-{{ $r['payment_status'] }}">{{ ucfirst($r['payment_status']) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="5">TOTAL TO TRANSFER (TZS)</td>
            <td class="right">{{ number_format($rows->sum('net_salary'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<p class="footer">
    Consultants are excluded (paid via separate contract invoicing, not bank payroll transfer).<br>
    Generated {{ now()->format('d M Y H:i') }} - NovarexHSE TZ - Confidential
</p>
</body></html>
