<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentalAuditChecklistItem extends Model
{
    protected $table = 'environmental_audit_checklist_items';

    protected $fillable = [
        'audit_id', 'category', 'item_code', 'item_description',
        'compliance_status', 'evidence_notes', 'evidence_file',
        'findings_notes', 'sort_order',
    ];

    public const COMPLIANCE_STATUS_LABELS = [
        'compliant'            => 'Compliant',
        'partially_compliant'  => 'Partially Compliant',
        'non_compliant'        => 'Non-Compliant',
        'not_applicable'       => 'Not Applicable',
    ];

    public const COMPLIANCE_STATUS_COLORS = [
        'compliant'            => 'success',
        'partially_compliant'  => 'warning',
        'non_compliant'        => 'danger',
        'not_applicable'       => 'gray',
    ];

    protected static function booted(): void
    {
        // Recompute parent audit score whenever a checklist item changes
        static::saved(function (self $item) {
            $item->audit->recomputeScore();
        });
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(EnvironmentalAudit::class, 'audit_id');
    }
}
