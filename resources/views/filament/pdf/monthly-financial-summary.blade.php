<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }
  .page { padding: 20px 24px; }

  /* ── Section heading ── */
  .section-title {
    background: #1d4ed8; color: #fff; font-size: 9.5pt; font-weight: bold;
    padding: 5px 10px; margin: 14px 0 6px;
  }

  /* ── KPI summary row ── */
  .kpi-row { display: table; width: 100%; border-collapse: collapse; margin: 10px 0; }
  .kpi-cell {
    display: table-cell; width: 33.33%; padding: 12px 10px; border: 1px solid #e5e7eb;
    text-align: center; vertical-align: middle; border-radius: 4px;
  }
  .kpi-label { font-size: 8pt; color: #6b7280; margin-bottom: 4px; }
  .kpi-value { font-size: 16pt; font-weight: bold; }
  .kpi-sub   { font-size: 7.5pt; color: #6b7280; margin-top: 2px; }
  .positive  { color: #166534; }
  .negative  { color: #991b1b; }
  .neutral   { color: #1d4ed8; }

  /* ── Data tables ── */
  table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 6px; }
  th { background: #eff6ff; color: #1d4ed8; font-weight: bold; padding: 5px 8px; text-align: left;
       border: 1px solid #dbeafe; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  tr:last-child td { border-bottom: 2px solid #1d4ed8; font-weight: bold; }
  .text-right { text-align: right; }
  .row-total td { background: #f0f9ff; font-weight: bold; }

  /* ── Net position band ── */
  .net-band {
    padding: 10px 14px; margin: 10px 0; border-radius: 4px; font-size: 11pt;
    font-weight: bold; text-align: center;
  }
  .net-positive { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
  .net-negative { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

  /* ── Footer ── */
  .footer {
    border-top: 2px solid #1d4ed8; margin-top: 18px; padding-top: 6px;
    font-size: 7.5pt; color: #9ca3af; display: table; width: 100%;
  }
  .footer-l { display: table-cell; text-align: left; }
  .footer-r { display: table-cell; text-align: right; }
</style>
</head>
<body>
<div class="page">

  @include('filament.pdf.partials.letterhead')

  {{-- ── TITLE BAND ── --}}
  <div class="section-title" style="font-size:11pt;margin-top:8px;">
    MONTHLY FINANCIAL SUMMARY — {{ strtoupper($summary['period']->format('F Y')) }}
  </div>

  {{-- ── KPI CARDS ── --}}
  <div class="kpi-row">
    <div class="kpi-cell">
      <div class="kpi-label">Total Revenue</div>
      <div class="kpi-value positive">TZS {{ number_format($summary['revenue_total'], 2) }}</div>
      <div class="kpi-sub">Invoiced {{ $summary['period']->format('M Y') }}</div>
    </div>
    <div class="kpi-cell">
      <div class="kpi-label">Total Outflows</div>
      <div class="kpi-value negative">TZS {{ number_format($summary['total_outflows'], 2) }}</div>
      <div class="kpi-sub">Payroll + Field Expenses + Petty Cash</div>
    </div>
    <div class="kpi-cell">
      <div class="kpi-label">Net Position</div>
      <div class="kpi-value {{ $summary['net_position'] >= 0 ? 'positive' : 'negative' }}">
        TZS {{ number_format($summary['net_position'], 2) }}
      </div>
      <div class="kpi-sub">Revenue − Total Outflows</div>
    </div>
  </div>

  @if($summary['net_position'] >= 0)
    <div class="net-band net-positive">
      NET SURPLUS: TZS {{ number_format($summary['net_position'], 2) }}
    </div>
  @else
    <div class="net-band net-negative">
      NET DEFICIT: TZS {{ number_format(abs($summary['net_position']), 2) }}
    </div>
  @endif

  {{-- ── PAYROLL & STATUTORY ── --}}
  <div class="section-title">PAYROLL &amp; STATUTORY OBLIGATIONS</div>
  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th class="text-right">Amount (TZS)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Staff Paid</td>
        <td class="text-right">{{ $summary['payroll']->staff_count }}</td>
      </tr>
      <tr>
        <td>Total Gross Salaries</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_gross, 2) }}</td>
      </tr>
      <tr>
        <td>Total Net Salaries (Take-Home)</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_net, 2) }}</td>
      </tr>
      <tr>
        <td>PAYE (TRA)</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_paye, 2) }}</td>
      </tr>
      <tr>
        <td>NSSF — Employee Contribution</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_nssf_employee, 2) }}</td>
      </tr>
      <tr>
        <td>NSSF — Employer Contribution</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_nssf_employer, 2) }}</td>
      </tr>
      <tr>
        <td>WCF</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_wcf, 2) }}</td>
      </tr>
      <tr>
        <td>NHIF</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_nhif, 2) }}</td>
      </tr>
      <tr class="row-total">
        <td>Total Statutory Obligations</td>
        <td class="text-right">{{ number_format($summary['statutory_total'], 2) }}</td>
      </tr>
    </tbody>
  </table>

  {{-- ── FIELD EXPENSES ── --}}
  <div class="section-title">FIELD EXPENSES BY CATEGORY</div>
  <table>
    <thead>
      <tr>
        <th>Category</th>
        <th class="text-right">Amount (TZS)</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($summary['field_expenses_by_category'] as $category => $total)
        <tr>
          <td>{{ ucwords(str_replace('_', ' ', $category)) }}</td>
          <td class="text-right">{{ number_format($total, 2) }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="2" style="color:#9ca3af;">No approved field expenses for this period.</td>
        </tr>
      @endforelse
      <tr class="row-total">
        <td>Total Field Expenses</td>
        <td class="text-right">{{ number_format($summary['field_expenses_total'], 2) }}</td>
      </tr>
    </tbody>
  </table>

  {{-- ── PETTY CASH ── --}}
  <div class="section-title">PETTY CASH OUTFLOWS</div>
  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th class="text-right">Amount (TZS)</th>
      </tr>
    </thead>
    <tbody>
      <tr class="row-total">
        <td>Total Petty Cash Outflows (Expenses &amp; Utility Payments)</td>
        <td class="text-right">{{ number_format($summary['petty_cash_total'], 2) }}</td>
      </tr>
    </tbody>
  </table>

  {{-- ── OUTFLOWS RECONCILIATION ── --}}
  <div class="section-title">OUTFLOWS RECONCILIATION</div>
  <table>
    <thead>
      <tr>
        <th>Component</th>
        <th class="text-right">Amount (TZS)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Gross Payroll</td>
        <td class="text-right">{{ number_format($summary['payroll']->total_gross, 2) }}</td>
      </tr>
      <tr>
        <td>Employer NSSF &amp; WCF (on-costs)</td>
        <td class="text-right">{{ number_format((float)$summary['payroll']->total_nssf_employer + (float)$summary['payroll']->total_wcf, 2) }}</td>
      </tr>
      <tr>
        <td>Field Expenses</td>
        <td class="text-right">{{ number_format($summary['field_expenses_total'], 2) }}</td>
      </tr>
      <tr>
        <td>Petty Cash</td>
        <td class="text-right">{{ number_format($summary['petty_cash_total'], 2) }}</td>
      </tr>
      <tr class="row-total">
        <td>Total Outflows</td>
        <td class="text-right">{{ number_format($summary['total_outflows'], 2) }}</td>
      </tr>
    </tbody>
  </table>

  {{-- ── FOOTER ── --}}
  <div class="footer">
    <div class="footer-l">{{ \App\Models\Setting::companyName() }} — Confidential</div>
    <div class="footer-r">
      Report period: {{ $summary['period']->format('F Y') }}
      &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, H:i') }}
    </div>
  </div>

</div>
</body>
</html>
