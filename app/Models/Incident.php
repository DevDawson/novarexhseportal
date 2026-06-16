<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        // Investigation
        'investigation_method',
        'investigation_status',
        'investigation_responsible_person',
        'investigation_target_date',
        // 5 Whys
        'why_1', 'why_2', 'why_3', 'why_4', 'why_5',
        // Fishbone
        'fishbone_data',
        // TapRooT
        'taproot_timeline',
        'taproot_witnesses',
        'taproot_direct_causes',
        'taproot_contributing_factors',
        'taproot_verification_review',
        // Barrier Analysis
        'barrier_analysis_data',
        // Shared
        'corrective_actions_plan',
        'evidence_files',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'closed_date' => 'date',
        'investigation_target_date' => 'date',
        'likelihood' => 'integer',
        'impact' => 'integer',
        'risk_score' => 'integer',
        'fishbone_data' => 'array',
        'taproot_timeline' => 'array',
        'barrier_analysis_data' => 'array',
        'corrective_actions_plan' => 'array',
        'evidence_files' => 'array',
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

    public function investigations(): HasMany
    {
        return $this->hasMany(IncidentInvestigation::class);
    }

    public function capaActions(): HasMany
    {
        return $this->hasMany(CapaAction::class);
    }

    public function lessonsLearned(): HasMany
    {
        return $this->hasMany(LessonsLearned::class);
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
     * Suggest the appropriate investigation method based on risk_score,
     * per the NOVAREX Recommended Workflow:
     *
     *   Low / Medium (0-9)   → 5 Whys
     *   High (10-15)         → Fishbone (+ 5 Whys)
     *   Critical (16-25)     → TapRooT + Barrier Analysis
     */
    public function getSuggestedMethodAttribute(): string
    {
        return match ($this->risk_level) {
            'critical' => 'taproot',
            'high' => 'fishbone',
            default => 'five_whys',
        };
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
