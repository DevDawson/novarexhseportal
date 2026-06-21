<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaturityScore extends Model
{
    protected $fillable = ['assessment_id', 'indicator_id', 'score', 'auto_calculated', 'evidence'];

    protected $casts = [
        'auto_calculated' => 'boolean',
        'score'           => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(MaturityAssessment::class, 'assessment_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(MaturityIndicator::class, 'indicator_id');
    }
}
