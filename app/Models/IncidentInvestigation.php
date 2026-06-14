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
        'why_1', 'why_2', 'why_3', 'why_4', 'why_5',
        'root_cause',
        'recommendations',
        'timeline_events',
        'witness_statements',
        'direct_causes',
        'contributing_factors',
        'action_plan',
        'verification_notes',
        'verification_date',
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
