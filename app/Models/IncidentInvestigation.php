<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentInvestigation extends Model
{
    use HasFactory;

    protected $table = 'incident_investigations';

    protected $fillable = [
        'incident_id',
        'method',

        // Section 1: Incident Overview (additional)
        'people_involved',

        // Section 1 (Five Whys)
        'why_1', 'why_2', 'why_3', 'why_4', 'why_5',

        // Section 2: Event Timeline
        'timeline_events',

        // Section 3: Task / Activity Description
        'task_description',
        'procedures_followed',
        'deviations_from_practice',

        // Section 4: Direct Causes
        'direct_causes',
        'unsafe_acts_conditions',
        'immediate_failures',

        // Section 5: Contributing Causes
        'contributing_factors',
        'equipment_environmental_human_factors',
        'communication_supervision_gaps',

        // Section 6: Root Causes (System Failures)
        'root_cause',
        'training_supervision_failure',
        'risk_assessment_adequacy',
        'maintenance_inspection_effectiveness',

        // Section 7: Safeguards / Barriers -> investigation_barrier_items relation

        // Section 8: Human Performance Factors
        'task_understanding',
        'distractions_fatigue_stress',
        'competency_assessment',

        // Section 9: Corrective Actions
        'recommendations',
        'action_plan',

        // Section 10: Preventive Actions
        'preventive_actions',

        // Section 11: Effectiveness Verification
        'verification_notes',
        'effectiveness_indicators',
        'verification_date',

        // Section 12: Management Review
        'lessons_learned',
        'management_review_notes',

        // Witness statements (shared, TapRooT/Barrier)
        'witness_statements',

        // Action tracking (all methods)
        'responsible_person_id',
        'target_date',
        'status',
        'evidence_files',
        'conducted_by',
    ];

    protected $casts = [
        'timeline_events' => 'array',
        'evidence_files' => 'array',
        'target_date' => 'date',
        'verification_date' => 'date',
    ];

    /**
     * Human-readable method labels.
     */
    public const METHOD_LABELS = [
        'five_whys' => '5 Whys Analysis',
        'fishbone' => 'Fishbone (Ishikawa) Analysis',
        'taprout' => 'TapRooT Style Investigation',
        'barrier' => 'Barrier Analysis',
    ];

    /**
     * Recommended investigation method per incident risk level,
     * following NOVAREX Investigation Workflow (Step 4):
     *
     *   Minor (Low)       → 5 Whys
     *   Moderate (Medium) → Fishbone + 5 Whys (use fishbone as primary)
     *   Major (High)      → TapRooT Style
     *   High-Risk (Critical) → Barrier Analysis + TapRooT (use barrier as primary)
     */
    public static function recommendedMethod(int $riskScore): string
    {
        $level = RiskScoringService::level($riskScore);

        return match ($level) {
            'critical' => 'barrier',
            'high' => 'taprout',
            'medium' => 'fishbone',
            default => 'five_whys',
        };
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    /**
     * Fishbone cause entries (People, Equipment, Method, Materials,
     * Environment, Management).
     */
    public function fishboneCauses(): HasMany
    {
        return $this->hasMany(InvestigationFishboneCause::class, 'investigation_id');
    }

    /**
     * Barrier Analysis hazard/control rows.
     */
    public function barrierItems(): HasMany
    {
        return $this->hasMany(InvestigationBarrierItem::class, 'investigation_id');
    }
}
