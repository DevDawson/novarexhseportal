<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Grievance extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'complainant_name',
        'complainant_contact',
        'is_anonymous',
        'category',
        'received_date',
        'description',
        'status',
        'severity',
        'investigation_notes',
        'resolution',
        'target_resolution_date',
        'actual_resolution_date',
        'complainant_satisfied',
        'assigned_to',
        'stakeholder_id',
    ];

    protected $casts = [
        'received_date'          => 'date',
        'target_resolution_date' => 'date',
        'actual_resolution_date' => 'date',
        'is_anonymous'           => 'boolean',
        'complainant_satisfied'  => 'boolean',
    ];

    public const CATEGORY_LABELS = [
        'environmental' => 'Environmental',
        'social'        => 'Social',
        'labour'        => 'Labour / Employment',
        'safety'        => 'Safety',
        'land_access'   => 'Land Access',
        'noise_dust'    => 'Noise / Dust / Vibration',
        'other'         => 'Other',
    ];

    public const STATUS_LABELS = [
        'open'         => 'Open',
        'under_review' => 'Under Review',
        'action_taken' => 'Action Taken',
        'resolved'     => 'Resolved',
        'closed'       => 'Closed',
    ];

    public const SEVERITY_LABELS = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
    ];

    // ----------------------------------------------------------------
    // Auto-generate reference
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (Grievance $grievance) {
            if (empty($grievance->reference)) {
                $grievance->reference = static::nextReference(now());
            }
        });
    }

    public static function nextReference(Carbon $date): string
    {
        $prefix = 'GRV-' . $date->format('Y') . '-' . $date->format('m');

        $last = static::query()
            ->where('reference', 'like', $prefix . '-%')
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class);
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getIsOverdueAttribute(): bool
    {
        return $this->target_resolution_date
            && ! in_array($this->status, ['resolved', 'closed'])
            && $this->target_resolution_date->isPast();
    }
}
