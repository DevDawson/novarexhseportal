<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsiaAlternative extends Model
{
    use HasFactory;

    protected $table = 'esia_alternatives';

    protected $fillable = [
        'project_id',
        'screening_id',
        'alternative_type',
        'title',
        'description',
        'environmental_impact',
        'cost_factor',
        'social_acceptance',
        'feasibility',
        'preference_score',
        'is_recommended',
        'recommendation_notes',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'environmental_impact' => 'integer',
        'cost_factor'          => 'integer',
        'social_acceptance'    => 'integer',
        'feasibility'          => 'integer',
        'preference_score'     => 'integer',
        'is_recommended'       => 'boolean',
        'evaluated_at'         => 'date',
    ];

    public const TYPE_LABELS = [
        'no_project'    => 'No-Project Option (Baseline)',
        'site'          => 'Alternative Site / Location',
        'technology'    => 'Alternative Technology',
        'design'        => 'Alternative Design / Layout',
        'process'       => 'Alternative Operational Process',
        'energy_source' => 'Alternative Energy Source',
        'other'         => 'Other Alternative',
    ];

    public const TYPE_DESCRIPTIONS = [
        'no_project'    => 'Decision to not implement the project — used as baseline for comparison.',
        'site'          => 'Evaluation of different possible project locations to minimise environmental impacts.',
        'technology'    => 'Comparison of different technologies or equipment to achieve the same objective.',
        'design'        => 'Modification of project design, layout, size, or configuration to reduce risks.',
        'process'       => 'Assessment of different operational methods or construction processes.',
        'energy_source' => 'Evaluation of different energy options (renewable vs non-renewable) to reduce emissions.',
        'other'         => 'Other alternative not covered by the above categories.',
    ];

    public const FACTOR_OPTIONS = [
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            // Preference score: higher = better alternative
            // SA and Feasibility: higher raw = better (positive weight)
            // EI and Cost: higher raw = worse (inverted to 6-x)
            $model->preference_score =
                ((int) $model->social_acceptance + (int) $model->feasibility)
                + (6 - (int) $model->environmental_impact)
                + (6 - (int) $model->cost_factor);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(EsiaScreening::class, 'screening_id');
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getPreferenceColorAttribute(): string
    {
        if ($this->preference_score >= 16) return 'success';
        if ($this->preference_score >= 12) return 'info';
        if ($this->preference_score >= 8)  return 'warning';
        return 'danger';
    }
}
