<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsiaRegulatorySubmission extends Model
{
    use HasFactory;

    protected $table = 'esia_regulatory_submissions';

    protected $fillable = [
        'project_id', 'report_id', 'regulatory_authority', 'submission_type',
        'reference_number', 'submitted_at', 'submitted_by', 'status',
        'submission_notes', 'review_comments', 'decision_date',
        'approval_conditions', 'approval_expiry_date', 'certificate_file',
    ];

    protected $casts = [
        'submitted_at'        => 'date',
        'decision_date'       => 'date',
        'approval_expiry_date' => 'date',
    ];

    public const SUBMISSION_TYPE_LABELS = [
        'screening'          => 'Screening Form',
        'scoping'            => 'Scoping Report',
        'draft_eia'          => 'Draft EIA Report',
        'final_eia'          => 'Final EIA Report',
        'esmp'               => 'ESMP',
        'compliance_report'  => 'Compliance Monitoring Report',
    ];

    public const STATUS_LABELS = [
        'draft'                    => 'Draft',
        'submitted'                => 'Submitted',
        'under_review'             => 'Under Review',
        'additional_info_required' => 'Additional Info Required',
        'approved'                 => 'Approved',
        'rejected'                 => 'Rejected',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(EsiaReport::class, 'report_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function getIsApprovalExpiringAttribute(): bool
    {
        return $this->status === 'approved'
            && $this->approval_expiry_date
            && $this->approval_expiry_date->diffInDays(now()) <= 60
            && $this->approval_expiry_date->isFuture();
    }

    public function getIsApprovalExpiredAttribute(): bool
    {
        return $this->status === 'approved'
            && $this->approval_expiry_date
            && $this->approval_expiry_date->isPast();
    }
}
