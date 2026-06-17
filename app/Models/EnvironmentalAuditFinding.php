<?php

namespace App\Models;

use App\Services\EnvironmentalAuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnvironmentalAuditFinding extends Model
{
    use SoftDeletes;

    protected $table = 'environmental_audit_findings';

    protected $fillable = [
        'audit_id', 'checklist_item_id', 'finding_number',
        'clause_reference', 'process_area', 'finding_type',
        'description', 'objective_evidence', 'root_cause_analysis',
        'environmental_impact_category',
        'risk_level', 'likelihood', 'severity', 'risk_score', 'regulatory_impact',
        'recommended_action', 'action_owner', 'department_responsible',
        'target_completion_date', 'priority_level', 'action_status',
        'closed_at', 'closed_by', 'effectiveness_verified', 'effectiveness_notes',
    ];

    protected $casts = [
        'target_completion_date' => 'date',
        'closed_at'              => 'datetime',
        'regulatory_impact'      => 'boolean',
        'effectiveness_verified' => 'boolean',
        'likelihood'             => 'integer',
        'severity'               => 'integer',
        'risk_score'             => 'integer',
    ];

    public const FINDING_TYPE_LABELS = [
        'major_nc'    => 'Major Non-Conformance',
        'minor_nc'    => 'Minor Non-Conformance',
        'observation' => 'Observation',
        'ofi'         => 'Opportunity for Improvement',
    ];

    public const FINDING_TYPE_COLORS = [
        'major_nc'    => 'danger',
        'minor_nc'    => 'warning',
        'observation' => 'info',
        'ofi'         => 'success',
    ];

    public const IMPACT_CATEGORY_LABELS = [
        'air'   => 'Air Quality',
        'water' => 'Water Quality',
        'soil'  => 'Soil / Land',
        'noise' => 'Noise',
        'waste' => 'Waste',
        'other' => 'Other',
    ];

    public const RISK_LEVEL_LABELS = [
        'low'      => 'Low',
        'medium'   => 'Medium',
        'high'     => 'High',
        'critical' => 'Critical',
    ];

    public const RISK_LEVEL_COLORS = [
        'low'      => 'success',
        'medium'   => 'warning',
        'high'     => 'danger',
        'critical' => 'danger',
    ];

    public const ACTION_STATUS_LABELS = [
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'closed'      => 'Closed',
    ];

    public const ACTION_STATUS_COLORS = [
        'open'        => 'danger',
        'in_progress' => 'warning',
        'closed'      => 'success',
    ];

    public const PRIORITY_LABELS = [
        'low'      => 'Low',
        'medium'   => 'Medium',
        'high'     => 'High',
        'critical' => 'Critical',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $f) {
            $f->risk_score = $f->likelihood * $f->severity;
            $f->risk_level = EnvironmentalAuditService::findingRiskLevel($f->likelihood, $f->severity);

            if ($f->action_status === 'closed' && ! $f->closed_at) {
                $f->closed_at = now();
            }
        });

        static::creating(function (self $f) {
            if (empty($f->finding_number)) {
                $count = self::where('audit_id', $f->audit_id)->withTrashed()->count() + 1;
                $f->finding_number = 'F-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(EnvironmentalAudit::class, 'audit_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(EnvironmentalAuditChecklistItem::class, 'checklist_item_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
