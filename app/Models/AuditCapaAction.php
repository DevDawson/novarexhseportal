<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AuditManagementService;

class AuditCapaAction extends Model
{
    use SoftDeletes;

    protected $table = 'audit_capa_actions';

    protected $fillable = [
        'nc_id', 'internal_audit_id', 'action_number', 'action_type',
        'description', 'root_cause_addressed', 'responsible_person_id', 'department',
        'target_date', 'actual_completion_date', 'status',
        'evidence_notes', 'evidence_file',
        'verification_status', 'verified_by_id', 'verified_at',
        'effectiveness_check', 'effectiveness_notes',
    ];

    protected $casts = [
        'target_date'             => 'date',
        'actual_completion_date'  => 'date',
        'verified_at'             => 'datetime',
        'effectiveness_check'     => 'boolean',
    ];

    public const ACTION_TYPE_LABELS = [
        'corrective'  => 'Corrective Action',
        'preventive'  => 'Preventive Action',
    ];

    public const STATUS_LABELS = [
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'verified'    => 'Verified',
    ];

    public const STATUS_COLORS = [
        'open'        => 'danger',
        'in_progress' => 'warning',
        'completed'   => 'primary',
        'verified'    => 'success',
    ];

    public const VERIFICATION_LABELS = [
        'not_due' => 'Not Due',
        'pending' => 'Pending Verification',
        'passed'  => 'Passed',
        'failed'  => 'Failed',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuditCapaAction $capa) {
            if (empty($capa->action_number)) {
                $capa->action_number = AuditManagementService::nextCapaNumber($capa->internal_audit_id);
            }
        });

        static::saving(function (AuditCapaAction $capa) {
            if ($capa->status === 'completed' && ! $capa->actual_completion_date) {
                $capa->actual_completion_date = today();
            }
            if ($capa->status === 'verified' && ! $capa->verified_at) {
                $capa->verified_at  = now();
                $capa->verified_by_id = auth()->id();
            }
        });
    }

    public function nc(): BelongsTo
    {
        return $this->belongsTo(AuditNonConformity::class, 'nc_id');
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class, 'internal_audit_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->target_date
            && $this->target_date->isPast()
            && ! in_array($this->status, ['completed', 'verified']);
    }
}
