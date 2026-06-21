<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvAuditApprovalLog extends Model
{
    protected $table = 'env_audit_approval_logs';

    protected $fillable = [
        'audit_id', 'user_id', 'stage', 'action', 'comments', 'signature_text', 'signed_at',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public static array $stageLabels = [
        'submitted'             => 'Submitted by Auditor',
        'lead_auditor_signed'   => 'Lead Auditor Review',
        'pm_approved'           => 'Project Manager Approval',
        'client_approved'       => 'Client Approval',
        'final_approved'        => 'Final Approval',
        'rejected'              => 'Rejected',
    ];

    public function audit(): BelongsTo { return $this->belongsTo(EnvironmentalAudit::class, 'audit_id'); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
}
