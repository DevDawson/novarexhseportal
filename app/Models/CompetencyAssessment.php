<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'competency_area', 'competency_description', 'assessment_method',
        'assessed_by_id', 'assessment_date', 'score', 'result',
        'next_assessment_date', 'notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'next_assessment_date' => 'date',
    ];

    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function assessedBy(): BelongsTo { return $this->belongsTo(User::class, 'assessed_by_id'); }
}
