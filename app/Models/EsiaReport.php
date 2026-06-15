<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EsiaReport extends Model
{
    use HasFactory;

    protected $table = 'esia_reports';

    protected $fillable = [
        'project_id', 'report_title', 'report_type', 'version',
        'author_id', 'date_prepared', 'executive_summary',
        'document_file', 'status', 'reviewed_by', 'review_date', 'review_comments',
    ];

    protected $casts = [
        'date_prepared' => 'date',
        'review_date'   => 'date',
    ];

    public const REPORT_TYPE_LABELS = [
        'screening_report' => 'Screening Report',
        'scoping_report'   => 'Scoping Report',
        'draft_esia'       => 'Draft ESIA Report',
        'final_esia'       => 'Final ESIA Report',
        'esmp'             => 'Environmental & Social Management Plan (ESMP)',
        'audit_report'     => 'ESIA Audit Report',
    ];

    public const STATUS_LABELS = [
        'draft'       => 'Draft',
        'peer_review' => 'Under Peer Review',
        'final'       => 'Final',
        'submitted'   => 'Submitted to Authority',
        'approved'    => 'Approved',
        'rejected'    => 'Rejected',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function regulatorySubmissions(): HasMany
    {
        return $this->hasMany(EsiaRegulatorySubmission::class, 'report_id');
    }
}
