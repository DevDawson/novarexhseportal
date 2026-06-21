<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JhaLegalRequirement extends Model
{
    protected $table = 'jha_legal_requirements';

    protected $fillable = ['jha_analysis_id', 'legislation', 'requirement_detail', 'compliant', 'notes'];

    protected $casts = ['compliant' => 'boolean'];

    public static array $legislationOptions = [
        'OSHA Tanzania (CAP 297)'             => 'OSHA Tanzania (CAP 297)',
        'Environmental Management Act (EMA)'  => 'Environmental Management Act (EMA)',
        'IFC EHS Guidelines'                  => 'IFC EHS Guidelines',
        'ISO 45001 Requirements'              => 'ISO 45001 Requirements',
        'ISO 14001 Requirements'              => 'ISO 14001 Requirements',
        'Client HSE Requirements'             => 'Client HSE Requirements',
        'EWURA Regulations'                   => 'EWURA Regulations',
        'NEMC Requirements'                   => 'NEMC Requirements',
        'TPDC Safety Standards'               => 'TPDC Safety Standards',
        'Other'                               => 'Other',
    ];

    public function jhaAnalysis(): BelongsTo { return $this->belongsTo(JhaAnalysis::class, 'jha_analysis_id'); }
}
