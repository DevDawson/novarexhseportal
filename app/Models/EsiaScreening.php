<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EsiaScreening extends Model
{
    use HasFactory;

    protected $table = 'esia_screenings';

    protected $fillable = [
        'project_id', 'scale', 'sensitivity', 'pollution_potential',
        'screening_score', 'category', 'project_description',
        'screening_justification', 'screened_by', 'screened_at',
        'status', 'reviewer_notes',
    ];

    protected $casts = [
        'screened_at' => 'date',
        'scale' => 'integer',
        'sensitivity' => 'integer',
        'pollution_potential' => 'integer',
        'screening_score' => 'integer',
    ];

    // ----------------------------------------------------------------
    // Labels
    // ----------------------------------------------------------------

    public const CATEGORY_LABELS = [
        'A' => 'Category A — Full ESIA Required',
        'B' => 'Category B — Limited Environmental Assessment',
        'C' => 'Category C — No EIA Required',
    ];

    public const STATUS_LABELS = [
        'pending'   => 'Pending Review',
        'in_review' => 'Under Review',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
    ];

    // ----------------------------------------------------------------
    // Auto-compute score and category on save
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->screening_score = $model->scale + $model->sensitivity + $model->pollution_potential;

            $score = $model->screening_score;
            if ($score >= 11) {
                $model->category = 'A';
            } elseif ($score >= 6) {
                $model->category = 'B';
            } else {
                $model->category = 'C';
            }
        });
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function screenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'screened_by');
    }

    public function scopingIssues(): HasMany
    {
        return $this->hasMany(EsiaScopingIssue::class, 'screening_id');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'A' => 'danger',
            'B' => 'warning',
            'C' => 'success',
            default => 'gray',
        };
    }
}
