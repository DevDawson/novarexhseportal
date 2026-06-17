<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a2e; background: #fff; }
  .page { padding: 20px 28px; }

  /* ── Invoice title band ── */
  .invoice-band { background: #1e3a5f; color: #fff; padding: 10px 16px; margin: 10px 0 14px; }
  .invoice-band table { width: 100%; border-collapse: collapse; }
  .invoice-band td { padding: 0; vertical-align: middle; }
  .invoice-band h1 { font-size: 18pt; font-weight: 800; letter-spacing: 2px; }
  .invoice-band .inv-number { font-size: 10pt; opacity: .85; margin-top: 2px; }
  .band-status {
    padding: 4px 14px; border-radius: 3px;
    font-size: 9pt; font-weight: 700; text-transform: uppercase;
  }
  .status-draft        { background: #6b7280; }
  .status-sent         { background: #3b82f6; }
  .status-partially_paid { background: #f59e0b; color: #1a1a2e; }
  .status-paid         { background: #16a34a; }
  .status-overdue      { background: #dc2626; }
  .status-cancelled    { background: #374151; }

  /* ── Meta row ── */
  .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; border: 1px solid #e5e7eb; }
  .meta-table td { padding: 8px 14px; border-right: 1px solid #e5e7eb; text-align: center; width: 25%; vertical-align: top; }
  .meta-table td:last-child { border-right: none; }
  .meta-label { font-size: 7.5pt; color: #6b7280; font-weight: 700; text-transform: uppercase; margin-bottom: 3px; }
  .meta-value { font-size: 10pt; font-weight: 700; color: #1e3a5f; }
  .overdue-red { color: #dc2626; }

  /* ── Parties ── */
  .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  .parties-table td { width: 50%; vertical-align: top; padding: 10px 12px; border: 1px solid #e5e7eb; }
  .parties-table td.right { border-left: none; }
  .party-label { font-size: 7.5pt; font-weight: 700; text-transform: uppercase;
                 letter-spacing: 1px; color: #6b7280; margin-bottom: 5px;
                 border-bottom: 2px solid #1e3a5f; padding-bottom: 3px; }
  .party-name  { font-size: 11pt; font-weight: 700; color: #1e3a5f; margin-bottom: 4px; }
  .party-line  { font-size: 8.5pt; color: #374151; line-height: 1.5; }
  .party-line strong { color: #1a1a2e; }

  /* ── Line items table ── */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9pt; }
  .items-table th {
    background: #1e3a5f; color: #fff; padding: 7px 10px;
    text-align: left; font-weight: 700; font-size: 8.5pt;
  }
  .items-table th.right { text-align: right; }
  .items-table td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  .items-table tr:last-child td { border-bottom: 2px solid #1e3a5f; }
  .items-table tr.even td { background: #f8faff; }
  .td-right { text-align: right; }
  .row-num  { color: #9ca3af; font-size: 8pt; }

  /* ── Totals block ── */
  .totals-outer { width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 14px; }
  .totals-outer td.spacer { width: 60%; }
  .totals-inner { width: 100%; border-collapse: collapse; }
  .totals-inner td { padding: 5px 10px; border-bottom: 1px solid #e5e7eb; font-size: 9pt; }
  .totals-inner td.lbl { color: #374151; }
  .totals-inner td.val { text-align: right; font-weight: 600; }
  .row-grand td { background: #1e3a5f; color: #fff; font-size: 11pt; font-weight: 700; padding: 8px 10px; }
  .row-balance td { color: #dc2626; font-weight: 700; }
  .row-balance-zero td { color: #16a34a; font-weight: 700; }

  /* ── Bank details ── */
  .bank-section {
    border: 1px solid #dbeafe; background: #eff6ff;
    padding: 10px 14px; margin-bottom: 12px;
  }
  .bank-title { font-size: 8pt; font-weight: 700; text-transform: uppercase;
                color: #1e3a5f; letter-spacing: 1px; margin-bottom: 6px;
                border-bottom: 1px solid #bfdbfe; padding-bottom: 3px; }
  .bank-table { width: 100%; border-collapse: collapse; }
  .bank-table td { width: 50%; vertical-align: top; padding: 0 4px; }
  .bank-line { font-size: 8.5pt; color: #374151; line-height: 1.7; }
  .bank-line strong { color: #1e3a5f; min-width: 110px; display: inline-block; }

  /* ── Notes ── */
  .notes-section { border-left: 3px solid #3b82f6; padding: 6px 10px;
                   background: #f8faff; margin-bottom: 12px; font-size: 8.5pt; color: #374151; }
  .notes-title { font-weight: 700; font-size: 8pt; color: #1e3a5f; margin-bottom: 3px; }

  /* ── Thank-you band ── */
  .thankyou {
    text-align: center; padding: 8px; margin: 10px 0;
    background: #f0fdf4; border: 1px solid #86efac;
    color: #16a34a; font-size: 9.5pt; font-weight: 700; border-radius: 3px;
  }

  /* ── Footer ── */
  .footer-table { width: 100%; border-collapse: collapse; border-top: 2px solid #1e3a5f; padding-top: 6px; margin-top: 10px; }
  .footer-table td { font-size: 7.5pt; color: #9ca3af; padding-top: 6px; }
  .footer-table td.right { text-align: right; }
</style>
</head>
<body>
<div class="page">

  @include('filament.pdf.partials.letterhead')

  {{-- ── TITLE BAND ── --}}
  <div class="invoice-band">
    <table>
      <tr>
        <td>
          <h1>INVOICE</h1>
          <div class="inv-number">{{ $invoice->invoice_number }}</div>
        </td>
        <td style="text-align:right">
          <span class="band-status status-{{ $invoice->status }}">{{ str_replace('_', ' ', strtoupper($invoice->status)) }}</span>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── META ROW ── --}}
  <table class="meta-table">
    <tr>
      <td>
        <div class="meta-label">Invoice Date</div>
        <div class="meta-value">{{ $invoice->invoice_date->format('d M Y') }}</div>
      </td>
      <td>
        <div class="meta-label">Due Date</div>
        <div class="meta-value {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->balance > 0 ? 'overdue-red' : '' }}">
          {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}
        </div>
      </td>
      <td>
        <div class="meta-label">Project</div>
        <div class="meta-value" style="font-size:8.5pt">{{ $invoice->project?->title ?? '—' }}</div>
      </td>
      <td>
        <div class="meta-label">Prepared By</div>
        <div class="meta-value" style="font-size:8.5pt">{{ $invoice->createdBy?->name ?? '—' }}</div>
      </td>
    </tr>
  </table>

  {{-- ── PARTIES ── --}}
  <table class="parties-table">
    <tr>
      {{-- Bill From (Service Provider) --}}
      <td>
        <div class="party-label">Bill From</div>
        <div class="party-name">{{ $company['name'] }}</div>
        @if($company['tagline'])
          <div class="party-line">{{ $company['tagline'] }}</div>
        @endif
        @if($company['address'])
          <div class="party-line">{{ $company['address'] }}</div>
        @endif
        @if($company['tin'])
          <div class="party-line"><strong>TIN:</strong> {{ $company['tin'] }}</div>
        @endif
        @if($company['phone'])
          <div class="party-line"><strong>Tel:</strong> {{ $company['phone'] }}</div>
        @endif
        @if($company['email'])
          <div class="party-line"><strong>Email:</strong> {{ $company['email'] }}</div>
        @endif
      </td>

      {{-- Bill To (Client) --}}
      <td class="right">
        <div class="party-label">Bill To</div>
        <div class="party-name">{{ $invoice->client->company_name }}</div>
        @if($invoice->client->contact_person)
          <div class="party-line"><strong>Attn:</strong> {{ $invoice->client->contact_person }}</div>
        @endif
        @if($invoice->client->address)
          <div class="party-line">{{ $invoice->client->address }}</div>
        @endif
        @if($invoice->client->region)
          <div class="party-line">{{ $invoice->client->region }}</div>
        @endif
        @if($invoice->client->tin_number)
          <div class="party-line"><strong>TIN:</strong> {{ $invoice->client->tin_number }}</div>
        @endif
        @if($invoice->client->email)
          <div class="party-line"><strong>Email:</strong> {{ $invoice->client->email }}</div>
        @endif
        @if($invoice->client->phone)
          <div class="party-line"><strong>Tel:</strong> {{ $invoice->client->phone }}</div>
        @endif
      </td>
    </tr>
  </table>

  {{-- ── LINE ITEMS ── --}}
  <table class="items-table">
    <thead>
      <tr>
        <th style="width:30px">#</th>
        <th>Description</th>
        <th class="right" style="width:70px">Qty</th>
        <th class="right" style="width:110px">Unit Price (TZS)</th>
        <th class="right" style="width:120px">Amount (TZS)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $i => $item)
      <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
        <td class="row-num">{{ $i + 1 }}</td>
        <td>{{ $item->description }}</td>
        <td class="td-right">{{ number_format((float)$item->quantity, 2) }}</td>
        <td class="td-right">{{ number_format((float)$item->unit_price, 2) }}</td>
        <td class="td-right">{{ number_format((float)$item->amount, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- ── TOTALS ── --}}
  <table class="totals-outer">
    <tr>
      <td class="spacer"></td>
      <td style="width:40%; vertical-align:top">
        <table class="totals-inner">
          <tr>
            <td class="lbl">Subtotal</td>
            <td class="val">TZS {{ number_format((float)$invoice->subtotal, 2) }}</td>
          </tr>
          <tr>
            <td class="lbl">VAT (18%)</td>
            <td class="val">TZS {{ number_format((float)$invoice->vat, 2) }}</td>
          </tr>
          <tr class="row-grand">
            <td>TOTAL</td>
            <td style="text-align:right">TZS {{ number_format((float)$invoice->total_amount, 2) }}</td>
          </tr>
          @if($invoice->amount_paid > 0)
          <tr>
            <td class="lbl">Amount Paid</td>
            <td class="val">TZS {{ number_format((float)$invoice->amount_paid, 2) }}</td>
          </tr>
          <tr class="{{ $invoice->balance > 0 ? 'row-balance' : 'row-balance-zero' }}">
            <td>Balance Due</td>
            <td style="text-align:right">TZS {{ number_format($invoice->balance, 2) }}</td>
          </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  {{-- ── BANK DETAILS ── --}}
  @if($bank['account_number'] || $bank['name'])
  <div class="bank-section">
    <div class="bank-title">Payment Instructions</div>
    <table class="bank-table">
      <tr>
        <td>
          @if($bank['name'])<div class="bank-line"><strong>Bank Name:</strong> {{ $bank['name'] }}</div>@endif
          @if($bank['branch'])<div class="bank-line"><strong>Branch:</strong> {{ $bank['branch'] }}</div>@endif
          @if($bank['account_name'])<div class="bank-line"><strong>Account Name:</strong> {{ $bank['account_name'] }}</div>@endif
        </td>
        <td>
          @if($bank['account_number'])<div class="bank-line"><strong>Account No:</strong> {{ $bank['account_number'] }}</div>@endif
          @if($bank['swift'])<div class="bank-line"><strong>SWIFT / BIC:</strong> {{ $bank['swift'] }}</div>@endif
          <div class="bank-line"><strong>Reference:</strong> {{ $invoice->invoice_number }}</div>
        </td>
      </tr>
    </table>
  </div>
  @endif

  {{-- ── NOTES ── --}}
  @if($invoice->notes)
  <div class="notes-section">
    <div class="notes-title">Notes / Terms</div>
    {{ $invoice->notes }}
  </div>
  @endif

  @if($invoice->balance <= 0)
  <div class="thankyou">Thank you for your payment!</div>
  @else
  <div class="thankyou" style="background:#fff7ed;border-color:#fcd34d;color:#92400e;">
    Please quote <strong>{{ $invoice->invoice_number }}</strong> with your payment.
    Payment due by {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'as agreed' }}.
  </div>
  @endif

  {{-- ── FOOTER ── --}}
  <table class="footer-table">
    <tr>
      <td>{{ $company['name'] }}@if($company['tagline']) — {{ $company['tagline'] }}@endif</td>
      <td class="right">Invoice {{ $invoice->invoice_number }} &nbsp;|&nbsp; Generated {{ now()->format('d M Y') }}</td>
    </tr>
  </table>

</div>
</body>
</html>
