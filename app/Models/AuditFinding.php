<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_audit_id',
        'clause_reference',
        'finding_type',
        'description',
        'evidence',
        'corrective_action',
        'responsible_person_id',
        'target_date',
        'verification_notes',
        'verification_date',
        'status',
    ];

    protected $casts = [
        'target_date'       => 'date',
        'verification_date' => 'date',
    ];

    public const FINDING_TYPE_LABELS = [
        'conformity'               => 'Conformity',
        'observation'              => 'Observation',
        'minor_nonconformity'      => 'Minor Nonconformity',
        'major_nonconformity'      => 'Major Nonconformity',
        'opportunity_for_improvement' => 'Opportunity for Improvement (OFI)',
    ];

    public const STATUS_LABELS = [
        'open'           => 'Open',
        'action_planned' => 'Action Planned',
        'closed'         => 'Closed',
        'verified'       => 'Verified',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function internalAudit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }
}
