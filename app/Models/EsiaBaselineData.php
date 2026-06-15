<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsiaBaselineData extends Model
{
    use HasFactory;

    protected $table = 'esia_baseline_data';

    protected $fillable = [
        'project_id', 'parameter_type', 'parameter_name', 'sampling_location',
        'measurement_value', 'unit', 'standard_limit', 'exceeds_limit',
        'measurement_date', 'data_source', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'exceeds_limit'    => 'boolean',
        'measurement_value' => 'decimal:4',
    ];

    public const PARAMETER_TYPE_LABELS = [
        'air_quality'   => 'Air Quality',
        'water_quality' => 'Water Quality',
        'soil_quality'  => 'Soil Quality',
        'noise_level'   => 'Noise Level',
        'biodiversity'  => 'Biodiversity',
        'socioeconomic' => 'Socioeconomic',
        'health'        => 'Health Indicators',
        'land_use'      => 'Land Use',
        'other'         => 'Other',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
