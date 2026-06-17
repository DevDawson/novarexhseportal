<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AuditManagementService;

class AuditNonConformity extends Model
{
    use SoftDeletes;

    protected $table = 'audit_non_conformities';

    protected $fillable = [
        'internal_audit_id', 'checklist_item_id', 'nc_number', 'nc_type',
        'clause_reference', 'description', 'objective_evidence', 'department_responsible',
        'likelihood', 'severity', 'risk_score', 'risk_level',
        'rca_method', 'why_1', 'why_2', 'why_3', 'why_4', 'why_5', 'root_cause_summary',
        'fishbone_people', 'fishbone_process', 'fishbone_equipment',
        'fishbone_material', 'fishbone_environment', 'fishbone_management',
        'corrective_action_proposed', 'preventive_action_proposed',
        'status', 'raised_by_id', 'assigned_to_id', 'due_date',
        'closed_at', 'closed_by_id', 'verified_by_id', 'verified_at',
        'effectiveness_verified', 'effectiveness_notes',
    ];

    protected $casts = [
        'due_date'               => 'date',
        'closed_at'              => 'datetime',
        'verified_at'            => 'datetime',
        'effectiveness_verified' => 'boolean',
        'likelihood'             => 'integer',
        'severity'               => 'integer',
        'risk_score'             => 'integer',
    ];

    public const NC_TYPE_LABELS = [
        'major'    => 'Major NC',
        'minor'    => 'Minor NC',
        'critical' => 'Critical NC',
    ];

    public const NC_TYPE_COLORS = [
        'major'    => 'warning',
        'minor'    => 'info',
        'critical' => 'danger',
    ];

    public const STATUS_LABELS = [
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'closed'      => 'Closed',
        'rejected'    => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'open'        => 'danger',
        'in_progress' => 'warning',
        'closed'      => 'success',
        'rejected'    => 'gray',
    ];

    public const RCA_METHOD_LABELS = [
        'none'      => 'Not Yet Started',
        'five_whys' => '5 Whys Analysis',
        'fishbone'  => 'Fishbone (Ishikawa) Diagram',
        'both'      => 'Both: 5 Whys + Fishbone',
    ];

    protected static function booted(): void
    {
        static::saving(function (AuditNonConformity $nc) {
            if ($nc->likelihood && $nc->severity) {
                $nc->risk_score = $nc->likelihood * $nc->severity;
                $nc->risk_level = AuditManagementService::riskLevel($nc->risk_score);
            }

            if ($nc->status === 'closed' && ! $nc->closed_at) {
                $nc->closed_at  = now();
                $nc->closed_by_id = auth()->id();
            }
        });

        static::saved(function (AuditNonConformity $nc) {
            AuditManagementService::recomputeStats($nc->audit);
        });
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class, 'internal_audit_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(AuditChecklistItem::class, 'checklist_item_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function capaActions(): HasMany
    {
        return $this->hasMany(AuditCapaAction::class, 'nc_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['closed', 'rejected']);
    }
}
