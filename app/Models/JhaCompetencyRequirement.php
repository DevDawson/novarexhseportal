<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JhaCompetencyRequirement extends Model
{
    protected $table = 'jha_competency_requirements';

    protected $fillable = [
        'jha_analysis_id', 'competency_type', 'description',
        'required_workers', 'qualified_workers', 'compliance_pct',
    ];

    protected $casts = [
        'required_workers'  => 'integer',
        'qualified_workers' => 'integer',
        'compliance_pct'    => 'decimal:2',
    ];

    public static array $competencyTypes = [
        'OSHA Certificate'           => 'OSHA Certificate',
        'NEBOSH IGC'                 => 'NEBOSH IGC',
        'IOSH Managing Safely'       => 'IOSH Managing Safely',
        'Equipment Operator License' => 'Equipment Operator License',
        'First Aid Training'         => 'First Aid Training',
        'Confined Space Entry'       => 'Confined Space Entry',
        'Working at Height'          => 'Working at Height',
        'Hot Work Permit'            => 'Hot Work Permit',
        'Chemical Handling'          => 'Chemical Handling',
        'Scaffolding Certificate'    => 'Scaffolding Certificate',
        'Lifting Operations'         => 'Lifting Operations',
        'Electrical Safety'          => 'Electrical Safety',
        'Other'                      => 'Other',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $comp) {
            $comp->compliance_pct = $comp->required_workers > 0
                ? round(($comp->qualified_workers / $comp->required_workers) * 100, 2)
                : 0;
        });
    }

    public function jhaAnalysis(): BelongsTo { return $this->belongsTo(JhaAnalysis::class, 'jha_analysis_id'); }
}
