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
        'project_id', 'parameter_type', 'parameter_name', 'parameter_subtype',
        'sampling_location', 'gps_coordinates',
        'measurement_value', 'unit', 'standard_limit', 'standard_reference', 'exceeds_limit',
        'measurement_date', 'data_source', 'notes', 'trend', 'recorded_by',
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
        'emissions'     => 'Emissions',
        'waste'         => 'Waste Management',
        'biodiversity'  => 'Biodiversity',
        'socioeconomic' => 'Socioeconomic',
        'health'        => 'Health Indicators',
        'land_use'      => 'Land Use',
        'other'         => 'Other',
    ];

    public const PARAMETER_SUBTYPES = [
        'air_quality' => [
            'PM1.0'                      => 'PM1.0 (Particulate Matter ≤1μm)',
            'PM2.5'                      => 'PM2.5 (Fine Particulate Matter)',
            'PM10'                       => 'PM10 (Respirable Particulate Matter)',
            'TSP'                        => 'TSP (Total Suspended Particulates)',
            'CO'                         => 'CO (Carbon Monoxide)',
            'CO2'                        => 'CO₂ (Carbon Dioxide)',
            'SO2'                        => 'SO₂ (Sulfur Dioxide)',
            'NOx'                        => 'NOx (Nitrogen Oxides)',
            'NO2'                        => 'NO₂ (Nitrogen Dioxide)',
            'O3'                         => 'O₃ (Ozone)',
            'H2S'                        => 'H₂S (Hydrogen Sulphide)',
            'VOCs'                       => 'VOCs (Volatile Organic Compounds)',
            'NH3'                        => 'NH₃ (Ammonia)',
            'CH4'                        => 'CH₄ (Methane)',
            'Dust Fall Rate'             => 'Dust Fall Rate',
            'Ambient Temperature'        => 'Ambient Temperature',
            'Relative Humidity'          => 'Relative Humidity',
            'Wind Speed'                 => 'Wind Speed',
            'Wind Direction'             => 'Wind Direction',
            'AQI'                        => 'AQI (Air Quality Index)',
        ],
        'water_quality' => [
            // Physical
            'Temperature'               => 'Temperature',
            'Turbidity'                 => 'Turbidity',
            'Color'                     => 'Color',
            'TDS'                       => 'TDS (Total Dissolved Solids)',
            'TSS'                       => 'TSS (Total Suspended Solids)',
            'Electrical Conductivity'   => 'Electrical Conductivity (EC)',
            'Salinity'                  => 'Salinity',
            // Chemical
            'pH'                        => 'pH',
            'DO'                        => 'DO (Dissolved Oxygen)',
            'BOD'                       => 'BOD (Biological Oxygen Demand)',
            'COD'                       => 'COD (Chemical Oxygen Demand)',
            'Oil & Grease'              => 'Oil & Grease',
            'Chlorides'                 => 'Chlorides',
            'Sulphates'                 => 'Sulphates',
            'Nitrates'                  => 'Nitrates',
            'Nitrites'                  => 'Nitrites',
            'Ammonia'                   => 'Ammonia',
            'Phosphates'                => 'Phosphates',
            'Alkalinity'                => 'Alkalinity',
            'Hardness'                  => 'Hardness',
            'Residual Chlorine'         => 'Residual Chlorine',
            // Heavy Metals
            'Pb'                        => 'Lead (Pb)',
            'Hg'                        => 'Mercury (Hg)',
            'Cd'                        => 'Cadmium (Cd)',
            'Cr'                        => 'Chromium (Cr)',
            'As'                        => 'Arsenic (As)',
            'Cu'                        => 'Copper (Cu)',
            'Zn'                        => 'Zinc (Zn)',
            'Ni'                        => 'Nickel (Ni)',
            'Fe'                        => 'Iron (Fe)',
            'Mn'                        => 'Manganese (Mn)',
            // Microbiological
            'Total Coliforms'           => 'Total Coliforms',
            'Fecal Coliforms'           => 'Fecal Coliforms',
            'E. coli'                   => 'E. coli',
            'Enterococci'               => 'Enterococci',
        ],
        'noise_level' => [
            'Leq'                       => 'Leq (Equivalent Continuous Sound Level)',
            'Lmax'                      => 'Lmax (Maximum Noise Level)',
            'Lmin'                      => 'Lmin (Minimum Noise Level)',
            'Peak Noise Level'          => 'Peak Noise Level',
            'Daytime Noise Level'       => 'Daytime Noise Level',
            'Night-Time Noise Level'    => 'Night-Time Noise Level',
            'Occupational Noise Exposure' => 'Occupational Noise Exposure Level',
        ],
        'soil_quality' => [
            'pH'                        => 'pH',
            'Moisture Content'          => 'Moisture Content',
            'Organic Matter'            => 'Organic Matter',
            'TPH'                       => 'TPH (Total Petroleum Hydrocarbons)',
            'Heavy Metals'              => 'Heavy Metals (General)',
            'Nutrient Content (N)'      => 'Nutrient Content — Nitrogen (N)',
            'Nutrient Content (P)'      => 'Nutrient Content — Phosphorus (P)',
            'Nutrient Content (K)'      => 'Nutrient Content — Potassium (K)',
            'Contamination Level'       => 'Contamination Level',
            'Soil Permeability'         => 'Soil Permeability',
        ],
        'emissions' => [
            'CO2 Emissions'             => 'CO₂ Emissions',
            'CO Emissions'              => 'CO Emissions',
            'SO2 Emissions'             => 'SO₂ Emissions',
            'NOx Emissions'             => 'NOx Emissions',
            'PM Emissions'              => 'Particulate Matter Emissions',
            'VOC Emissions'             => 'VOC Emissions',
            'CH4 Emissions'             => 'CH₄ (Methane) Emissions',
            'GHG Emissions'             => 'GHG Emissions (Total)',
            'Fuel Consumption'          => 'Fuel Consumption',
            'Emission Intensity'        => 'Emission Intensity',
        ],
        'waste' => [
            'Waste Quantity'            => 'Waste Quantity Generated',
            'Hazardous Waste'           => 'Hazardous Waste (kg)',
            'Non-Hazardous Waste'       => 'Non-Hazardous Waste (kg)',
            'Medical/Clinical Waste'    => 'Medical / Clinical Waste',
            'Recycling Rate'            => 'Recycling Rate (%)',
            'Disposal Method'           => 'Disposal Method Used',
            'Landfill Volume'           => 'Landfill Volume (m³)',
            'Waste Contractor'          => 'Licensed Waste Contractor',
            'Manifest Number'           => 'Waste Manifest / Disposal Certificate No.',
        ],
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
