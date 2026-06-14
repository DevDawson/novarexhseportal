<!DOCTYPE html>
<html><head><meta charset="utf-8">
<title>Salary Cost Report - {{ $period->format('F Y') }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1C2127; }
    h1 { font-size: 14px; margin: 0; color: #3B82F6; }
    h2 { font-size: 10px; color: #6B7280; margin: 2px 0 14px; }

    .section-title {
        font-size: 11px; font-weight: bold; text-transform: uppercase;
        background: #F6F4EF; padding: 5px 8px; margin-top: 14px; margin-bottom: 4px;
        border-left: 3px solid #3B82F6;
    }

    table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    td { padding: 5px 6px; border-bottom: 1px solid #E5E7EB; }
    td.label { color: #6B7280; }
    td.value { text-align: right; font-weight: 500; }

    .total-row td { font-weight: bold; border-top: 1px solid #9CA3AF; }

    .grand-box {
        background: #EFFDF4; border: 1px solid #22C55E; border-radius: 4px;
        padding: 12px; text-align: center; margin: 16px 0;
    }
    .grand-box .label { font-size: 10px; color: #16A34A; text-transform: uppercase; letter-spacing: 1px; }
    .grand-box .amount { font-size: 20px; font-weight: bold; color: #15803D; }

    .footer { font-size: 7px; color: #9CA3AF; text-align: center; margin-top: 14px; }
</style></head>
<body>
<h1>NovarexHSE TZ - Salary Cost Report</h1>
<h2>Period: {{ $period->format('F Y') }} &mdash; {{ $summary['staff_count'] }} staff on payroll</h2>

<div class="section-title">Employee Earnings (Total Gross)</div>
<table>
    <tr><td class="label">Total Gross Salaries</td><td class="value">TZS {{ number_format($summary['total_gross'], 2) }}</td></tr>
    <tr><td class="label">- of which Overtime Pay</td><td class="value">TZS {{ number_format($summary['total_overtime_pay'], 2) }}</td></tr>
</table>

<div class="section-title">Employee Deductions (Withheld from Net Pay)</div>
<table>
    <tr><td class="label">PAYE (TRA)</td><td class="value">TZS {{ number_format($summary['total_paye'], 2) }}</td></tr>
    <tr><td class="label">NSSF (Employee Contribution)</td><td class="value">TZS {{ number_format($summary['total_nssf_employee'], 2) }}</td></tr>
    <tr><td class="label">NHIF / Health Insurance</td><td class="value">TZS {{ number_format($summary['total_nhif'], 2) }}</td></tr>
    @php
        $totalEmployeeDeductions = $summary['total_paye'] + $summary['total_nssf_employee'] + $summary['total_nhif'];
    @endphp
    <tr class="total-row"><td class="label">Total Employee Deductions</td><td class="value">TZS {{ number_format($totalEmployeeDeductions, 2) }}</td></tr>
</table>

<div class="section-title">Employer Statutory Contributions (On Top of Gross)</div>
<table>
    <tr><td class="label">NSSF (Employer Contribution, 10%)</td><td class="value">TZS {{ number_format($summary['total_nssf_employer'], 2) }}</td></tr>
    <tr><td class="label">WCF (Workers Compensation Fund)</td><td class="value">TZS {{ number_format($summary['total_wcf'], 2) }}</td></tr>
    <tr><td class="label">SDL (Skills Development Levy, 4.5%)</td><td class="value">TZS {{ number_format($summary['total_sdl'], 2) }}</td></tr>
    @php
        $totalEmployerContributions = $summary['total_nssf_employer'] + $summary['total_wcf'] + $summary['total_sdl'];
    @endphp
    <tr class="total-row"><td class="label">Total Employer Contributions</td><td class="value">TZS {{ number_format($totalEmployerContributions, 2) }}</td></tr>
</table>

<div class="section-title">Net Payout to Staff</div>
<table>
    <tr><td class="label">Total Net Salaries (Take-Home)</td><td class="value">TZS {{ number_format($summary['total_net'], 2) }}</td></tr>
</table>

<div class="grand-box">
    <div class="label">Total Cost to Company (Gross + Employer Contributions)</div>
    <div class="amount">TZS {{ number_format($summary['total_employer_cost'], 2) }}</div>
</div>

<p class="footer">
    Total Cost to Company = Total Gross Salaries + Employer NSSF + WCF + SDL.<br>
    Generated {{ now()->format('d M Y H:i') }} - NovarexHSE TZ - Confidential
</p>
</body></html>
