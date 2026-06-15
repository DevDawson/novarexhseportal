<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentalAspect extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'activity_process',
        'environmental_aspect',
        'environmental_impact',
        'impact_category',
        'likelihood',
        'severity',
        'significance_score',
        'existing_controls',
        'legal_requirement_ref',
        'responsible_person_id',
        'review_date',
        'status',
    ];

    protected $casts = [
        'review_date'        => 'date',
        'likelihood'         => 'integer',
        'severity'           => 'integer',
        'significance_score' => 'integer',
    ];

    public const IMPACT_CATEGORY_LABELS = [
        'air'         => 'Air Quality',
        'water'       => 'Water',
        'soil'        => 'Soil / Land',
        'waste'       => 'Waste',
        'biodiversity'=> 'Biodiversity',
        'noise'       => 'Noise',
        'energy'      => 'Energy',
        'other'       => 'Other',
    ];

    public const STATUS_LABELS = [
        'significant'     => 'Significant',
        'not_significant' => 'Not Significant',
        'controlled'      => 'Controlled',
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

    public function getSignificanceLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->significance_score);
    }

    // ----------------------------------------------------------------
    // Auto-compute significance score and status on save
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (EnvironmentalAspect $aspect) {
            $aspect->significance_score = RiskScoringService::score(
                (int) $aspect->likelihood,
                (int) $aspect->severity,
            );

            // Auto-set status from score unless manually set to 'controlled'
            if ($aspect->status !== 'controlled') {
                $aspect->status = $aspect->significance_score >= 10
                    ? 'significant'
                    : 'not_significant';
            }
        });
    }
}
