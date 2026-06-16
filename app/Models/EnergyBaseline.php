<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnergyBaseline extends Model
{
    use HasFactory;

    protected $fillable = [
        'energy_source', 'baseline_period_start', 'baseline_period_end',
        'total_consumption', 'unit', 'methodology', 'adjustment_factors',
        'established_by_id', 'approved_by_id', 'approved_date', 'notes',
    ];

    protected $casts = [
        'baseline_period_start' => 'date',
        'baseline_period_end' => 'date',
        'approved_date' => 'date',
        'total_consumption' => 'decimal:3',
    ];

    public function establishedBy(): BelongsTo { return $this->belongsTo(User::class, 'established_by_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
}
