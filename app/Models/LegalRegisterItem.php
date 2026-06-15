<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalRegisterItem extends Model
{
    use HasFactory;

    protected $table = 'legal_register';

    protected $fillable = [
        'requirement_title',
        'requirement_type',
        'issuing_authority',
        'applicable_to',
        'compliance_status',
        'evidence_file',
        'expiry_date',
        'last_review_date',
        'next_review_date',
        'notes',
    ];

    protected $casts = [
        'expiry_date'       => 'date',
        'last_review_date'  => 'date',
        'next_review_date'  => 'date',
    ];

    public const REQUIREMENT_TYPE_LABELS = [
        'law'                => 'Law / Act',
        'regulation'         => 'Regulation / Subsidiary Legislation',
        'permit_license'     => 'Permit / License',
        'client_requirement' => 'Client Requirement',
        'other'              => 'Other',
    ];

    public const COMPLIANCE_STATUS_LABELS = [
        'compliant'           => 'Compliant',
        'non_compliant'       => 'Non-Compliant',
        'partially_compliant' => 'Partially Compliant',
        'not_assessed'        => 'Not Assessed',
    ];

    /**
     * True if the permit/license expires within the given number of days.
     */
    public function isExpiringSoon(int $withinDays = 60): bool
    {
        return $this->expiry_date
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= $withinDays;
    }

    /**
     * True if the expiry date is past.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
