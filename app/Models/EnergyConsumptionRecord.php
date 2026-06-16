<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnergyConsumptionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'energy_source', 'period_start', 'period_end', 'quantity', 'unit',
        'cost', 'currency', 'meter_reading_start', 'meter_reading_end',
        'facility', 'project_id', 'recorded_by_id', 'verified_by_id', 'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'quantity' => 'decimal:3',
        'cost' => 'decimal:2',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_id'); }
}
