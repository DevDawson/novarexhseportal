<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentalMonitoringRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'record_date',
        'metric_type',
        'value',
        'unit',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'record_date' => 'date',
        'value'       => 'decimal:2',
    ];

    /**
     * Metric type human-readable labels and their default units.
     * Used for form dropdowns and default unit population.
     */
    public const METRIC_TYPE_LABELS = [
        'water_consumption'          => 'Water Consumption',
        'energy_consumption'         => 'Energy Consumption',
        'fuel_consumption'           => 'Fuel Consumption',
        'waste_generated_hazardous'  => 'Waste Generated (Hazardous)',
        'waste_generated_nonhazardous' => 'Waste Generated (Non-Hazardous)',
        'waste_recycled'             => 'Waste Recycled',
        'ghg_emissions'              => 'GHG Emissions',
        'spills_incidents'           => 'Spills / Environmental Incidents',
    ];

    public const METRIC_TYPE_UNITS = [
        'water_consumption'          => 'm³',
        'energy_consumption'         => 'kWh',
        'fuel_consumption'           => 'litres',
        'waste_generated_hazardous'  => 'kg',
        'waste_generated_nonhazardous' => 'kg',
        'waste_recycled'             => 'kg',
        'ghg_emissions'              => 'tCO2e',
        'spills_incidents'           => 'count',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
