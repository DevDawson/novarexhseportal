<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'reported_by',
        'incident_date',
        'location',
        'incident_type',
        'severity',
        'likelihood',
        'impact',
        'risk_score',
        'description',
        'immediate_action',
        'root_cause',
        'corrective_actions',
        'status',
        'closed_date',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'closed_date' => 'date',
        'likelihood' => 'integer',
        'impact' => 'integer',
        'risk_score' => 'integer',
    ];

    /**
     * The project this incident relates to (nullable for company-wide incidents).
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user who reported this incident.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Risk level (low/medium/high/critical) derived from risk_score.
     */
    public function getRiskLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->risk_score);
    }

    /**
     * Required action text for this incident's current risk level.
     */
    public function getRequiredActionAttribute(): string
    {
        return RiskScoringService::requiredAction($this->risk_level);
    }

    /**
     * Auto-calculate Risk Score (Likelihood x Impact) and derive the
     * 'severity' enum (low/medium/high/critical) from the resulting
     * risk level, per the NOVAREX Risk Assessment Methodology (R = L x I).
     */
    protected static function booted(): void
    {
        static::saving(function (Incident $incident) {
            $incident->risk_score = RiskScoringService::score(
                (int) $incident->likelihood,
                (int) $incident->impact,
            );

            $incident->severity = RiskScoringService::level($incident->risk_score);
        });
    }
}
