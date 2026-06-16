<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtwApproval extends Model
{
    protected $fillable = [
        'permit_to_work_id',
        'approval_stage',
        'approver_id',
        'decision',
        'comments',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public const STAGE_LABELS = [
        'supervisor'   => 'Supervisor',
        'hse_officer'  => 'HSE Officer',
        'site_manager' => 'Site Manager',
    ];

    public const DECISION_LABELS = [
        'pending'                => 'Pending',
        'approved'               => 'Approved',
        'rejected'               => 'Rejected',
        'modification_requested' => 'Modification Requested',
    ];

    public const DECISION_COLORS = [
        'pending'                => 'gray',
        'approved'               => 'success',
        'rejected'               => 'danger',
        'modification_requested' => 'warning',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(PermitToWork::class, 'permit_to_work_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
