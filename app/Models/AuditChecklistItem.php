<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\AuditManagementService;

class AuditChecklistItem extends Model
{
    protected $table = 'audit_checklist_items';

    protected $fillable = [
        'internal_audit_id', 'iso_standard', 'clause_reference', 'question',
        'requirement_type', 'response', 'score', 'evidence_notes', 'auditor_notes',
        'sort_order',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public const RESPONSE_LABELS = [
        'not_assessed'   => 'Not Assessed',
        'compliant'      => 'Compliant',
        'non_compliant'  => 'Non-Compliant',
        'observation'    => 'Observation',
        'not_applicable' => 'Not Applicable',
    ];

    public const RESPONSE_COLORS = [
        'compliant'      => 'success',
        'non_compliant'  => 'danger',
        'observation'    => 'warning',
        'not_applicable' => 'gray',
        'not_assessed'   => 'gray',
    ];

    public const REQUIREMENT_LABELS = [
        'mandatory'    => 'Mandatory',
        'recommended'  => 'Recommended',
        'optional'     => 'Optional',
    ];

    protected static function booted(): void
    {
        static::saved(function (AuditChecklistItem $item) {
            AuditManagementService::recomputeStats($item->audit);
        });
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class, 'internal_audit_id');
    }
}
