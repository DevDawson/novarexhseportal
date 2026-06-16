<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnergyPerformanceIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_name', 'description', 'formula', 'unit_of_measure',
        'energy_source', 'baseline_value', 'target_value', 'current_value',
        'period', 'responsible_id', 'status',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'current_value' => 'decimal:4',
    ];

    public function responsible(): BelongsTo { return $this->belongsTo(User::class, 'responsible_id'); }

    public function getPerformanceRatioAttribute(): ?float
    {
        if ($this->baseline_value && $this->current_value) {
            return round(($this->current_value / $this->baseline_value) * 100, 2);
        }
        return null;
    }
}
