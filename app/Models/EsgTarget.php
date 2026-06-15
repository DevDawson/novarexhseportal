<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsgTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator',
        'category',
        'period',
        'unit',
        'baseline_value',
        'target_value',
        'actual_value',
        'status',
        'notes',
        'owner_id',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value'   => 'decimal:2',
        'actual_value'   => 'decimal:2',
    ];

    public const CATEGORY_LABELS = [
        'environmental' => 'Environmental',
        'social'        => 'Social',
        'governance'    => 'Governance',
    ];

    public const STATUS_LABELS = [
        'not_started' => 'Not Started',
        'on_track'    => 'On Track',
        'at_risk'     => 'At Risk',
        'off_track'   => 'Off Track',
        'achieved'    => 'Achieved',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getProgressPercentAttribute(): ?float
    {
        if (is_null($this->actual_value) || $this->target_value == 0) {
            return null;
        }

        return round(($this->actual_value / $this->target_value) * 100, 1);
    }
}
