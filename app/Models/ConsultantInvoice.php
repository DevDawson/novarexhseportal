<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantInvoice extends Model
{
    protected $fillable = [
        'project_id',
        'staff_id',
        'consultant_name',
        'consultant_type',
        'consultant_tin',
        'consultant_vrn',
        'consultant_business_reg',
        'consultant_address',
        'consultant_phone',
        'consultant_email',
        'consultant_bank_name',
        'consultant_bank_branch',
        'consultant_bank_account_name',
        'consultant_bank_account_number',
        'consultant_bank_swift',
        'proforma_number',
        'proforma_date',
        'service_description',
        'proforma_net_amount',
        'proforma_vat_amount',
        'proforma_total_amount',
        'proforma_attachment',
        'proforma_verified_at',
        'proforma_verified_by',
        'proforma_verification_notes',
        'efd_receipt_number',
        'efd_receipt_date',
        'efd_amount',
        'efd_attachment',
        'payment_date',
        'payment_reference',
        'payment_amount',
        'payment_notes',
        'status',
        'rejection_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'proforma_date'       => 'date',
        'proforma_verified_at' => 'datetime',
        'efd_receipt_date'    => 'date',
        'payment_date'        => 'date',
        'proforma_net_amount'   => 'decimal:2',
        'proforma_vat_amount'   => 'decimal:2',
        'proforma_total_amount' => 'decimal:2',
        'efd_amount'          => 'decimal:2',
        'payment_amount'      => 'decimal:2',
    ];

    public static array $statuses = [
        'pending'            => 'Pending Proforma',
        'proforma_received'  => 'Proforma Received',
        'proforma_verified'  => 'Proforma Verified',
        'awaiting_efd'       => 'Awaiting EFD/VFD',
        'efd_received'       => 'EFD/VFD Received',
        'paid'               => 'Paid',
        'rejected'           => 'Rejected',
    ];

    public function getDisplayNameAttribute(): string
    {
        if ($this->consultant_type === 'staff' && $this->staff) {
            return $this->staff->full_name ?? $this->staff->name;
        }
        return $this->consultant_name ?? '—';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function proformaVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proforma_verified_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
