<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Risk extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'risk_title',
        'description',
        'category',
        'likelihood',
        'severity',
        'risk_rating',
        'mitigation_measures',
        'risk_owner_id',
        'status',
        'review_date',
    ];

    protected $casts = [
        'review_date' => 'date',
        'likelihood' => 'integer',
        'severity' => 'integer',
        'risk_rating' => 'integer',
    ];

    /**
     * The project this risk belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user responsible for managing/mitigating this risk.
     */
    public function riskOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'risk_owner_id');
    }

    /**
     * Risk level (low/medium/high/critical) derived from risk_rating,
     * using the same R=LxI thresholds as the Incident module
     * (0-4 Low, 5-9 Medium, 10-15 High, 16-25 Critical).
     */
    public function getRiskLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->risk_rating);
    }

    /**
     * Required action text for this risk's current level.
     */
    public function getRequiredActionAttribute(): string
    {
        return RiskScoringService::requiredAction($this->risk_level);
    }

    /**
     * Auto-calculate the risk rating (Likelihood x Severity/Impact)
     * whenever likelihood or severity is set. R = L x I, range 0-25.
     */
    protected static function booted(): void
    {
        static::saving(function (Risk $risk) {
            $risk->risk_rating = RiskScoringService::score(
                (int) $risk->likelihood,
                (int) $risk->severity,
            );
        });
    }
}
