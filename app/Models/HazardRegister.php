<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardRegister extends Model
{
    use HasFactory;

    protected $table = 'hazard_register';

    protected $fillable = [
        'project_id',
        'activity_task',
        'location',
        'hazard_description',
        'hazard_category',
        'who_might_be_harmed',
        'initial_likelihood',
        'initial_severity',
        'initial_risk_score',
        'existing_controls',
        'additional_controls',
        'additional_controls_description',
        'residual_likelihood',
        'residual_severity',
        'residual_risk_score',
        'responsible_person_id',
        'review_date',
        'status',
    ];

    protected $casts = [
        'review_date' => 'date',
        'initial_likelihood' => 'integer',
        'initial_severity' => 'integer',
        'initial_risk_score' => 'integer',
        'residual_likelihood' => 'integer',
        'residual_severity' => 'integer',
        'residual_risk_score' => 'integer',
        'additional_controls' => 'array',
    ];

    public const HAZARD_CATEGORY_LABELS = [
        'physical'      => 'Physical',
        'chemical'      => 'Chemical',
        'biological'    => 'Biological',
        'ergonomic'     => 'Ergonomic',
        'psychosocial'  => 'Psychosocial',
        'environmental' => 'Environmental',
        'mechanical'    => 'Mechanical',
        'electrical'    => 'Electrical',
    ];

    public const STATUS_LABELS = [
        'open'                 => 'Open',
        'controls_in_progress' => 'Controls In Progress',
        'controlled'           => 'Controlled',
        'closed'               => 'Closed',
    ];

    public const CONTROL_HIERARCHY_OPTIONS = [
        'elimination'    => '1. Elimination — Remove the hazard entirely',
        'substitution'   => '2. Substitution — Replace with a less hazardous option',
        'engineering'    => '3. Engineering Controls — Isolate people from the hazard',
        'administrative' => '4. Administrative Controls — Change the way people work',
        'ppe'            => '5. PPE — Protect the individual',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getInitialRiskLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->initial_risk_score);
    }

    public function getResidualRiskLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->residual_risk_score);
    }

    // ----------------------------------------------------------------
    // Auto-compute risk scores on save (mirrors Incident::booted())
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (HazardRegister $hazard) {
            $hazard->initial_risk_score = RiskScoringService::score(
                (int) $hazard->initial_likelihood,
                (int) $hazard->initial_severity,
            );

            $hazard->residual_risk_score = RiskScoringService::score(
                (int) $hazard->residual_likelihood,
                (int) $hazard->residual_severity,
            );
        });
    }
}
