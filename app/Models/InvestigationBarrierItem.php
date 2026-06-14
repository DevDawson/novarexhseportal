<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationBarrierItem extends Model
{
    use HasFactory;

    protected $table = 'investigation_barrier_items';

    protected $fillable = [
        'investigation_id',
        'hazard',
        'required_barrier',
        'barrier_status',
        'control_failure',
        'corrective_action',
    ];

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(IncidentInvestigation::class, 'investigation_id');
    }
}
