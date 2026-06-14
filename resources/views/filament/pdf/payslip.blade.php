<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $staff->full_name }} - {{ $payroll->payroll_period->format('F Y') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1C2127; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0; color: #3B82F6; }
        .header p { margin: 2px 0; color: #6B7280; font-size: 10px; }
        .title { text-align: center; font-size: 13px; font-weight: bold; margin: 10px 0; text-transform: uppercase; letter-spacing: 1px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td { padding: 3px 4px; border-bottom: 1px solid #E5E7EB; }
        td.label { color: #6B7280; width: 50%; }
        td.value { text-align: right; font-weight: 500; width: 50%; }

        .section-title {
            font-size: 11px; font-weight: bold; text-transform: uppercase;
            background: #F6F4EF; padding: 4px 6px; margin-top: 10px; margin-bottom: 4px;
            border-left: 3px solid #3B82F6;
        }

        .total-row td { font-weight: bold; border-top: 1px solid #9CA3AF; border-bottom: none; }
        .net-box {
            background: #EFFDF4; border: 1px solid #22C55E; border-radius: 4px;
            padding: 8px; text-align: center; margin: 10px 0;
        }
        .net-box .label { font-size: 10px; color: #16A34A; text-transform: uppercase; letter-spacing: 1px; }
        .net-box .amount { font-size: 18px; font-weight: bold; color: #15803D; }

        .footer { margin-top: 16px; font-size: 9px; color: #9CA3AF; text-align: center; }
        .refs td { font-size: 10px; }
    </style>
</head>
<body>

    @include('filament.pdf.partials.letterhead')

    <div class="title">Payslip &mdash; {{ $payroll->payroll_period->format('F Y') }}</div>

    {{-- Employee Information (fields 1-5) --}}
    <div class="section-title">Employee Information</div>
    <table>
        <tr><td class="label">Employee Name</td><td class="value">{{ $staff->full_name }}</td></tr>
        <tr><td class="label">Employee ID</td><td class="value">{{ $staff->staff_no }}</td></tr>
        <tr><td class="label">Department</td><td class="value">{{ $staff->department?->name ?? '-' }}</td></tr>
        <tr><td class="label">Position</td><td class="value">{{ $staff->job_title ?? '-' }}</td></tr>
        <tr><td class="label">Employment Type</td><td class="value">{{ ucwords(str_replace('_', ' ', $payroll->employment_type)) }}</td></tr>
        <tr><td class="label">Payroll Month</td><td class="value">{{ $payroll->payroll_period->format('F Y') }}</td></tr>
    </table>

    {{-- Earnings (fields 6-8: Gross Salary, Allowances, Overtime) --}}
    <div class="section-title">Earnings</div>
    <table>
        @if (in_array($payroll->employment_type, ['permanent', 'contract', 'intern']))
            <tr><td class="label">Basic Salary</td><td class="value">{{ number_format($payroll->basic_salary, 2) }}</td></tr>
            <tr><td class="label">Allowances</td><td class="value">{{ number_format($payroll->allowances, 2) }}</td></tr>
            @if ($payroll->bonus > 0)
                <tr><td class="label">Bonus</td><td class="value">{{ number_format($payroll->bonus, 2) }}</td></tr>
            @endif
        @elseif ($payroll->employment_type === 'part_time')
            <tr><td class="label">Hours Worked</td><td class="value">{{ number_format($payroll->hours_worked, 2) }}</td></tr>
            <tr><td class="label">Hourly Rate</td><td class="value">{{ number_format($staff->hourly_rate ?? 0, 2) }}</td></tr>
        @elseif ($payroll->employment_type === 'casual')
            <tr><td class="label">Days Worked</td><td class="value">{{ number_format($payroll->days_worked, 2) }}</td></tr>
            <tr><td class="label">Daily Rate</td><td class="value">{{ number_format($staff->daily_rate ?? 0, 2) }}</td></tr>
        @elseif ($payroll->employment_type === 'consultant')
            <tr><td class="label">Contract Amount</td><td class="value">{{ number_format($staff->contract_amount ?? 0, 2) }}</td></tr>
        @endif

        @if ($payroll->overtime_hours > 0)
            <tr><td class="label">Overtime ({{ number_format($payroll->overtime_hours, 2) }} hrs @ 1.5x)</td><td class="value">{{ number_format($payroll->overtime_pay, 2) }}</td></tr>
        @endif

        <tr class="total-row">
            <td class="label">{{ $payroll->employment_type === 'consultant' ? 'Gross Payment' : 'Gross Salary' }}</td>
            <td class="value">TZS {{ number_format($payroll->gross_salary, 2) }}</td>
        </tr>
    </table>

    {{-- Deductions (fields 9-12: PAYE, NSSF, Loan, Other Deductions) --}}
    <div class="section-title">Deductions</div>
    <table>
        @if ($payroll->employment_type === 'consultant')
            <tr><td class="label">Withholding Tax</td><td class="value">{{ number_format($payroll->withholding_tax, 2) }}</td></tr>
            <tr class="total-row"><td class="label">Total Deductions</td><td class="value">{{ number_format($payroll->withholding_tax, 2) }}</td></tr>
        @else
            <tr><td class="label">PAYE</td><td class="value">{{ number_format($payroll->paye, 2) }}</td></tr>
            <tr><td class="label">NSSF (Employee)</td><td class="value">{{ number_format($payroll->nssf, 2) }}</td></tr>
            <tr><td class="label">NHIF</td><td class="value">{{ number_format($payroll->nhif, 2) }}</td></tr>
            @if ($payroll->loan_deduction > 0)
                <tr><td class="label">Loan Deduction</td><td class="value">{{ number_format($payroll->loan_deduction, 2) }}</td></tr>
            @endif
            @if ($payroll->advance_deduction > 0)
                <tr><td class="label">Advance Deduction</td><td class="value">{{ number_format($payroll->advance_deduction, 2) }}</td></tr>
            @endif
            @if ($payroll->other_deductions > 0)
                <tr><td class="label">Other Deductions</td><td class="value">{{ number_format($payroll->other_deductions, 2) }}</td></tr>
            @endif
            @php
                $totalDeductions = $payroll->paye + $payroll->nssf + $payroll->nhif
                    + $payroll->loan_deduction + $payroll->advance_deduction + $payroll->other_deductions;
            @endphp
            <tr class="total-row"><td class="label">Total Deductions</td><td class="value">{{ number_format($totalDeductions, 2) }}</td></tr>
        @endif
    </table>

    {{-- Net Salary (field 13) --}}
    <div class="net-box">
        <div class="label">{{ $payroll->employment_type === 'consultant' ? 'Net Payment' : 'Net Salary' }}</div>
        <div class="amount">TZS {{ number_format($payroll->net_salary, 2) }}</div>
    </div>

    {{-- References (fields 14-15: Approval Reference, Payment Reference) --}}
    <div class="section-title">Payment Details</div>
    <table class="refs">
        <tr><td class="label">Payment Status</td><td class="value">{{ ucfirst($payroll->payment_status) }}</td></tr>
        <tr><td class="label">Payment Date</td><td class="value">{{ $payroll->payment_date?->format('d M Y') ?? '-' }}</td></tr>
        <tr><td class="label">Approval Reference</td><td class="value">{{ $payroll->approval_reference ?? '-' }}</td></tr>
        <tr><td class="label">Payment Reference</td><td class="value">{{ $payroll->payment_reference ?? '-' }}</td></tr>
        @if ($staff->bank_name)
            <tr><td class="label">Bank</td><td class="value">{{ $staff->bank_name }} - {{ $staff->bank_account_no }}</td></tr>
        @endif
    </table>

    <div class="footer">
        This is a system-generated payslip from NovarexHSE TZ. Generated on {{ now()->format('d M Y H:i') }}.
        For queries, contact HR.
    </div>

</body>
</html>
