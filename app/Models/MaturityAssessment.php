<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaturityAssessment extends Model
{
    protected $fillable = [
        'project_id', 'period', 'period_type', 'overall_score',
        'maturity_level', 'notes', 'assessed_by', 'assessed_at', 'status', 'created_by',
    ];

    protected $casts = [
        'assessed_at'   => 'date',
        'overall_score' => 'decimal:2',
    ];

    public static array $levelLabels = [
        'Level 1: Initial',
        'Level 2: Basic',
        'Level 3: Defined',
        'Level 4: Proactive',
        'Level 5: Optimizing',
    ];

    public static function scoreToLevel(float $score): string
    {
        return match (true) {
            $score < 2.0 => 'Level 1: Initial',
            $score < 3.0 => 'Level 2: Basic',
            $score < 3.5 => 'Level 3: Defined',
            $score < 4.3 => 'Level 4: Proactive',
            default      => 'Level 5: Optimizing',
        };
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(MaturityScore::class, 'assessment_id');
    }
}
