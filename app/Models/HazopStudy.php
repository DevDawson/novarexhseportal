<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HazopStudy extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_ref',
        'title',
        'project_id',
        'department_id',
        'process_description',
        'pid_reference',
        'facility_area',
        'study_date',
        'facilitator_id',
        'team_members',
        'study_scope',
        'study_objectives',
        'status',
        'reviewed_by_id',
        'review_date',
        'approved_by_id',
        'approval_date',
        'approval_comments',
    ];

    protected $casts = [
        'study_date'    => 'date',
        'review_date'   => 'date',
        'approval_date' => 'date',
        'team_members'  => 'array',
    ];

    public const STATUS_LABELS = [
        'draft'        => 'Draft',
        'in_progress'  => 'In Progress',
        'complete'     => 'Complete',
        'under_review' => 'Under Review',
        'approved'     => 'Approved',
        'closed'       => 'Closed',
    ];

    public const STATUS_COLORS = [
        'draft'        => 'gray',
        'in_progress'  => 'primary',
        'complete'     => 'info',
        'under_review' => 'warning',
        'approved'     => 'success',
        'closed'       => 'gray',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function facilitator(): BelongsTo { return $this->belongsTo(User::class, 'facilitator_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
    public function nodes(): HasMany { return $this->hasMany(HazopNode::class); }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getTotalNodesAttribute(): int { return $this->nodes()->count(); }
    public function getOpenNodesAttribute(): int { return $this->nodes()->where('status', '!=', 'closed')->count(); }
    public function getCriticalNodesAttribute(): int { return $this->nodes()->where('risk_classification', 'critical')->count(); }

    // ----------------------------------------------------------------
    // Auto-generate study_ref (HAZOP-YYYY-NNNN)
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (HazopStudy $study) {
            if (empty($study->study_ref)) {
                $year = now()->format('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $study->study_ref = sprintf('HAZOP-%s-%04d', $year, $count);
            }
        });
    }
}
