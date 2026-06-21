<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JhaEnvironment extends Model
{
    protected $table = 'jha_environment';

    protected $fillable = [
        'jha_task_id',
        'waste_generated', 'waste_description',
        'air_emissions', 'air_description',
        'water_discharge', 'water_description',
        'energy_consumption', 'energy_description',
        'biodiversity_impact', 'biodiversity_description',
        'community_impact', 'community_description',
        'env_likelihood', 'env_consequence',
        'env_risk_score', 'env_risk_level',
    ];

    protected $casts = [
        'waste_generated'     => 'boolean',
        'air_emissions'       => 'boolean',
        'water_discharge'     => 'boolean',
        'energy_consumption'  => 'boolean',
        'biodiversity_impact' => 'boolean',
        'community_impact'    => 'boolean',
        'env_likelihood'      => 'integer',
        'env_consequence'     => 'integer',
        'env_risk_score'      => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $env) {
            $env->env_risk_score = max(0, (int)$env->env_likelihood * (int)$env->env_consequence);
            $env->env_risk_level = match (true) {
                $env->env_risk_score >= 16 => 'high',
                $env->env_risk_score >= 9  => 'medium',
                default                    => 'low',
            };
        });
    }

    public function task(): BelongsTo { return $this->belongsTo(JhaTask::class, 'jha_task_id'); }
}
