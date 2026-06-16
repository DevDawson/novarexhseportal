<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpillReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'spill_reference', 'project_id', 'reported_by_id', 'spill_date', 'location',
        'substance_spilled', 'substance_type', 'estimated_volume', 'volume_unit',
        'environmental_media_affected', 'cause', 'immediate_actions', 'containment_actions',
        'cleanup_actions', 'regulatory_notification_required', 'regulatory_notified_at',
        'status', 'closed_by_id', 'closed_at', 'notes',
    ];

    protected $casts = [
        'spill_date' => 'date',
        'regulatory_notification_required' => 'boolean',
        'regulatory_notified_at' => 'datetime',
        'closed_at' => 'datetime',
        'estimated_volume' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (SpillReport $spill) {
            if (empty($spill->spill_reference)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $count = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $spill->spill_reference = sprintf('SPL-%s-%s-%04d', $year, $month, $count);
            }
        });
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function reportedBy(): BelongsTo { return $this->belongsTo(User::class, 'reported_by_id'); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by_id'); }
}
