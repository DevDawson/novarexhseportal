<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'policy_number',
        'policy_type',
        'document_owner',
        'effective_date',
        'review_date',
        'last_reviewed_date',
        'status',
        'version',
        'scope',
        'document_file',
        'approved_by',
    ];

    protected $casts = [
        'effective_date'     => 'date',
        'review_date'        => 'date',
        'last_reviewed_date' => 'date',
    ];

    public const TYPE_LABELS = [
        'hse'          => 'HSE',
        'esg'          => 'ESG',
        'hr'           => 'Human Resources',
        'finance'      => 'Finance',
        'ethics'       => 'Ethics & Conduct',
        'data_privacy' => 'Data Privacy',
        'procurement'  => 'Procurement',
        'other'        => 'Other',
    ];

    public const STATUS_LABELS = [
        'draft'        => 'Draft',
        'active'       => 'Active',
        'under_review' => 'Under Review',
        'superseded'   => 'Superseded',
        'archived'     => 'Archived',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getIsDueForReviewAttribute(): bool
    {
        return $this->review_date
            && $this->status === 'active'
            && $this->review_date->lte(now()->addDays(60));
    }

    public function getIsOverdueReviewAttribute(): bool
    {
        return $this->review_date
            && $this->status === 'active'
            && $this->review_date->isPast();
    }
}
