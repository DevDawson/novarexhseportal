<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JhaMonitoringCheck extends Model
{
    protected $table = 'jha_monitoring_checks';

    protected $fillable = [
        'jha_analysis_id', 'checked_by', 'checked_at',
        'controls_implemented', 'ppe_available', 'permit_active',
        'workers_briefed', 'emergency_equipment_available',
        'compliance_pct', 'notes',
    ];

    protected $casts = [
        'checked_at'                  => 'datetime',
        'controls_implemented'        => 'boolean',
        'ppe_available'               => 'boolean',
        'permit_active'               => 'boolean',
        'workers_briefed'             => 'boolean',
        'emergency_equipment_available'=> 'boolean',
        'compliance_pct'              => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $check) {
            $checkFields = [
                'controls_implemented', 'ppe_available', 'permit_active',
                'workers_briefed', 'emergency_equipment_available',
            ];
            $implemented = collect($checkFields)->filter(fn ($f) => (bool) $check->$f)->count();
            $check->compliance_pct = round(($implemented / count($checkFields)) * 100, 2);
        });
    }

    public function jhaAnalysis(): BelongsTo { return $this->belongsTo(JhaAnalysis::class, 'jha_analysis_id'); }
    public function checkedBy(): BelongsTo   { return $this->belongsTo(User::class, 'checked_by'); }
}
