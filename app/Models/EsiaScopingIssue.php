<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsiaScopingIssue extends Model
{
    use HasFactory;

    protected $table = 'esia_scoping_issues';

    protected $fillable = [
        'project_id', 'screening_id', 'issue_type', 'issue_title',
        'description', 'data_required', 'methodology',
        'responsible_expert', 'included_in_scope', 'exclusion_justification', 'sort_order',
    ];

    protected $casts = [
        'included_in_scope' => 'boolean',
    ];

    public const ISSUE_TYPE_LABELS = [
        'air_quality'       => 'Air Quality',
        'water_resources'   => 'Water Resources',
        'soil_land'         => 'Soil & Land',
        'biodiversity'      => 'Biodiversity & Ecology',
        'noise_vibration'   => 'Noise & Vibration',
        'waste_management'  => 'Waste Management',
        'climate'           => 'Climate Change',
        'cultural_heritage' => 'Cultural Heritage',
        'socioeconomic'     => 'Socioeconomic',
        'health_safety'     => 'Health & Safety',
        'resettlement'      => 'Resettlement / Land Access',
        'gender_inclusion'  => 'Gender & Social Inclusion',
        'other'             => 'Other',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(EsiaScreening::class, 'screening_id');
    }
}
