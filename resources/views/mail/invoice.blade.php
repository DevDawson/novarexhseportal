<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1a1a2e; background: #f4f4f4; margin: 0; padding: 0; }
  .wrapper { max-width: 620px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  .header { background: #1e3a5f; padding: 28px 32px; text-align: center; }
  .header h1 { color: #fff; font-size: 22px; margin: 0 0 4px; letter-spacing: 1px; }
  .header p  { color: #94b8d0; font-size: 13px; margin: 0; }
  .body { padding: 28px 32px; }
  .greeting { font-size: 15px; margin-bottom: 16px; }
  .message  { line-height: 1.7; color: #374151; white-space: pre-line; margin-bottom: 24px; }
  .invoice-box {
    border: 1px solid #e5e7eb; border-radius: 4px;
    overflow: hidden; margin-bottom: 24px;
  }
  .invoice-box-title {
    background: #f0f4ff; padding: 10px 16px;
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .8px; color: #1e3a5f; border-bottom: 1px solid #e5e7eb;
  }
  .invoice-row { display: flex; justify-content: space-between; padding: 9px 16px; border-bottom: 1px solid #f0f0f0; }
  .invoice-row:last-child { border-bottom: none; }
  .inv-label { color: #6b7280; font-size: 13px; }
  .inv-value { font-weight: 600; font-size: 13px; color: #1a1a2e; }
  .inv-value.total { color: #1e3a5f; font-size: 15px; }
  .inv-value.balance { color: #dc2626; }
  .inv-value.paid { color: #16a34a; }
  .attach-note {
    background: #fffbeb; border: 1px solid #fcd34d;
    border-radius: 4px; padding: 12px 16px;
    font-size: 13px; color: #92400e; margin-bottom: 24px;
  }
  .attach-note strong { display: block; margin-bottom: 4px; }
  .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; }
  .footer a { color: #1e3a5f; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">

  {{-- Header --}}
  <div class="header">
    <h1>{{ $companyName }}</h1>
    <p>Invoice Notification</p>
  </div>

  <div class="body">

    {{-- Greeting --}}
    <div class="greeting">
      Dear {{ $invoice->client->contact_person ?: $invoice->client->company_name }},
    </div>

    {{-- Custom message body --}}
    <div class="message">{{ $body }}</div>

    {{-- Invoice summary box --}}
    <div class="invoice-box">
      <div class="invoice-box-title">Invoice Summary</div>

      <div class="invoice-row">
        <span class="inv-label">Invoice Number</span>
        <span class="inv-value">{{ $invoice->invoice_number }}</span>
      </div>

      <div class="invoice-row">
        <span class="inv-label">Invoice Date</span>
        <span class="inv-value">{{ $invoice->invoice_date->format('d M Y') }}</span>
      </div>

      @if($invoice->due_date)
      <div class="invoice-row">
        <span class="inv-label">Due Date</span>
        <span class="inv-value">{{ $invoice->due_date->format('d M Y') }}</span>
      </div>
      @endif

      @if($invoice->project)
      <div class="invoice-row">
        <span class="inv-label">Project</span>
        <span class="inv-value">{{ $invoice->project->title }}</span>
      </div>
      @endif

      <div class="invoice-row">
        <span class="inv-label">Total Amount</span>
        <span class="inv-value total">TZS {{ number_format((float)$invoice->total_amount, 2) }}</span>
      </div>

      @if($invoice->amount_paid > 0)
      <div class="invoice-row">
        <span class="inv-label">Amount Paid</span>
        <span class="inv-value paid">TZS {{ number_format((float)$invoice->amount_paid, 2) }}</span>
      </div>
      <div class="invoice-row">
        <span class="inv-label">Balance Due</span>
        <span class="inv-value {{ $invoice->balance > 0 ? 'balance' : 'paid' }}">
          TZS {{ number_format($invoice->balance, 2) }}
        </span>
      </div>
      @endif

      <div class="invoice-row">
        <span class="inv-label">Status</span>
        <span class="inv-value">{{ str_replace('_', ' ', ucwords($invoice->status)) }}</span>
      </div>
    </div>

    {{-- Attachment notice --}}
    <div class="attach-note">
      <strong>📎 PDF Invoice Attached</strong>
      The full invoice {{ $invoice->invoice_number }}.pdf is attached to this email.
      Please quote the invoice number with your payment.
    </div>

  </div>

  <div class="footer">
    This email was sent by <strong>{{ $companyName }}</strong>.<br>
    Please do not reply directly to this email — contact us at
    <a href="mailto:{{ \App\Models\Setting::companyEmail() }}">{{ \App\Models\Setting::companyEmail() ?: 'our official email' }}</a>.
  </div>

</div>
</body>
</html>
