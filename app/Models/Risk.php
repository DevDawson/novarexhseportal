<?php

namespace App\Models;

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
     * Auto-calculate the risk rating (likelihood x severity)
     * whenever likelihood or severity is set.
     */
    protected static function booted(): void
    {
        static::saving(function (Risk $risk) {
            if ($risk->likelihood && $risk->severity) {
                $risk->risk_rating = (int) $risk->likelihood * (int) $risk->severity;
            }
        });
    }
}
