<?php

namespace App\Models;

use App\Services\HazopScoringService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JhaHazard extends Model
{
    protected $table = 'jha_hazards';

    protected $fillable = [
        'jha_task_id', 'hazard_type', 'hazard_description',
        'initial_likelihood', 'initial_severity', 'initial_exposure',
        'initial_risk_score', 'initial_risk_level',
        'residual_likelihood', 'residual_severity', 'residual_exposure',
        'residual_risk_score', 'residual_risk_level',
        'residual_accepted', 'notes',
    ];

    protected $casts = [
        'initial_likelihood'  => 'integer',
        'initial_severity'    => 'integer',
        'initial_exposure'    => 'integer',
        'initial_risk_score'  => 'integer',
        'residual_likelihood' => 'integer',
        'residual_severity'   => 'integer',
        'residual_exposure'   => 'integer',
        'residual_risk_score' => 'integer',
        'residual_accepted'   => 'boolean',
    ];

    public static array $hazardTypes = [
        'Chemical'     => 'Chemical',
        'Physical'     => 'Physical',
        'Biological'   => 'Biological',
        'Ergonomic'    => 'Ergonomic',
        'Electrical'   => 'Electrical',
        'Mechanical'   => 'Mechanical',
        'Radiation'    => 'Radiation',
        'Fire/Explosion'=> 'Fire / Explosion',
        'Psychosocial' => 'Psychosocial',
        'Environmental'=> 'Environmental',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $hazard) {
            $hazard->initial_risk_score  = HazopScoringService::initialScore(
                (int) $hazard->initial_likelihood,
                (int) $hazard->initial_severity,
                (int) $hazard->initial_exposure,
            );
            $hazard->initial_risk_level = HazopScoringService::riskLevel($hazard->initial_risk_score);

            $hazard->residual_risk_score = HazopScoringService::initialScore(
                (int) $hazard->residual_likelihood,
                (int) $hazard->residual_severity,
                (int) $hazard->residual_exposure,
            );
            $hazard->residual_risk_level = HazopScoringService::riskLevel($hazard->residual_risk_score);

            // Reject JHA if residual is not lower than initial
            $hazard->residual_accepted = $hazard->residual_risk_score < $hazard->initial_risk_score;
        });
    }

    public function task(): BelongsTo         { return $this->belongsTo(JhaTask::class, 'jha_task_id'); }
    public function controlMeasures(): HasMany { return $this->hasMany(JhaControlMeasure::class, 'jha_hazard_id')->orderBy('hierarchy_level')->orderBy('sort_order'); }
}
