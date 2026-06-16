<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnergyActionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'opportunity_type', 'energy_source_affected',
        'expected_saving_quantity', 'expected_saving_unit', 'expected_cost', 'actual_saving',
        'assigned_to_id', 'approved_by_id', 'target_date', 'completion_date',
        'status', 'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completion_date' => 'date',
        'expected_cost' => 'decimal:2',
        'expected_saving_quantity' => 'decimal:3',
        'actual_saving' => 'decimal:3',
    ];

    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
}
