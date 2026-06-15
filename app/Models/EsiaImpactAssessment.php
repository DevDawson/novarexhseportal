<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EsiaImpactAssessment extends Model
{
    use HasFactory;

    protected $table = 'esia_impact_assessments';

    protected $fillable = [
        'project_id', 'activity', 'receptor', 'impact_category',
        'impact_description', 'phase', 'nature',
        'severity', 'likelihood', 'duration', 'sensitivity', 'significance_score', 'significance_level',
        'proposed_mitigation',
        'residual_severity', 'residual_likelihood', 'residual_duration', 'residual_sensitivity',
        'residual_significance_score', 'residual_significance_level',
        'assessed_by',
    ];

    protected $casts = [
        'severity'    => 'integer',
        'likelihood'  => 'integer',
        'duration'    => 'integer',
        'sensitivity' => 'integer',
        'significance_score' => 'integer',
        'residual_severity'    => 'integer',
        'residual_likelihood'  => 'integer',
        'residual_duration'    => 'integer',
        'residual_sensitivity' => 'integer',
        'residual_significance_score' => 'integer',
    ];

    // ----------------------------------------------------------------
    // Labels
    // ----------------------------------------------------------------

    public const IMPACT_CATEGORY_LABELS = [
        'air_quality'       => 'Air Quality',
        'water_resources'   => 'Water Resources',
        'soil_land'         => 'Soil & Land',
        'biodiversity'      => 'Biodiversity',
        'noise_vibration'   => 'Noise & Vibration',
        'waste'             => 'Waste',
        'climate'           => 'Climate Change',
        'cultural_heritage' => 'Cultural Heritage',
        'socioeconomic'     => 'Socioeconomic',
        'health_safety'     => 'Health & Safety',
        'resettlement'      => 'Resettlement',
        'other'             => 'Other',
    ];

    public const PHASE_LABELS = [
        'pre_construction' => 'Pre-Construction',
        'construction'     => 'Construction',
        'operation'        => 'Operation',
        'decommissioning'  => 'Decommissioning',
        'all'              => 'All Phases',
    ];

    public const SIGNIFICANCE_LEVEL_LABELS = [
        'negligible' => 'Negligible',
        'minor'      => 'Minor',
        'moderate'   => 'Moderate',
        'major'      => 'Major',
        'critical'   => 'Critical',
    ];

    public const RATING_OPTIONS = [
        1 => '1 — Very Low',
        2 => '2 — Low',
        3 => '3 — Medium',
        4 => '4 — High',
        5 => '5 — Very High',
    ];

    // ----------------------------------------------------------------
    // Auto-compute significance on save
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            // Initial significance: S × L × D × Sensitivity
            $model->significance_score = $model->severity
                * $model->likelihood
                * $model->duration
                * $model->sensitivity;
            $model->significance_level = self::scoreToLevel($model->significance_score);

            // Residual (only if all 4 residual factors filled)
            if ($model->residual_severity && $model->residual_likelihood
                && $model->residual_duration && $model->residual_sensitivity) {
                $model->residual_significance_score = $model->residual_severity
                    * $model->residual_likelihood
                    * $model->residual_duration
                    * $model->residual_sensitivity;
                $model->residual_significance_level = self::scoreToLevel($model->residual_significance_score);
            }
        });
    }

    public static function scoreToLevel(int $score): string
    {
        if ($score >= 300) return 'critical';
        if ($score >= 100) return 'major';
        if ($score >= 40)  return 'moderate';
        if ($score >= 10)  return 'minor';
        return 'negligible';
    }

    public static function levelColor(string $level): string
    {
        return match ($level) {
            'critical' => 'danger',
            'major'    => 'warning',
            'moderate' => 'primary',
            'minor'    => 'info',
            default    => 'success',
        };
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function mitigationActions(): HasMany
    {
        return $this->hasMany(EsiaMitigationAction::class, 'impact_id');
    }
}
