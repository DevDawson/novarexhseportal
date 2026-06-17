<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string  $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number . ' — ' . Setting::companyName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invoice',
            with: [
                'invoice'     => $this->invoice,
                'body'        => $this->body,
                'companyName' => Setting::companyName(),
            ],
        );
    }

    public function attachments(): array
    {
        $this->invoice->loadMissing('client', 'project', 'items', 'createdBy');

        $company = [
            'name'    => Setting::companyName(),
            'tagline' => Setting::companyTagline(),
            'address' => Setting::companyAddress(),
            'tin'     => Setting::companyTin(),
            'phone'   => Setting::companyPhone(),
            'email'   => Setting::companyEmail(),
        ];

        $bank = Setting::bankDetails();

        $pdfContent = Pdf::loadView('pdf.invoice', compact('company', 'bank') + ['invoice' => $this->invoice])
            ->setPaper('a4', 'portrait')
            ->output();

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdfContent,
                $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
