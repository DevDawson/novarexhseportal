<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationFishboneCause extends Model
{
    use HasFactory;

    protected $table = 'investigation_fishbone_causes';

    protected $fillable = [
        'investigation_id',
        'category',
        'cause',
    ];

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(IncidentInvestigation::class, 'investigation_id');
    }
}
