<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EthicsIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'incident_type',
        'reported_date',
        'is_anonymous',
        'description',
        'severity',
        'status',
        'investigation_findings',
        'corrective_action',
        'closure_date',
        'investigated_by',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'closure_date'  => 'date',
        'is_anonymous'  => 'boolean',
    ];

    public const TYPE_LABELS = [
        'bribery_corruption' => 'Bribery / Corruption',
        'fraud'              => 'Fraud',
        'conflict_of_interest' => 'Conflict of Interest',
        'discrimination'     => 'Discrimination',
        'harassment'         => 'Harassment',
        'data_breach'        => 'Data Breach',
        'misconduct'         => 'Misconduct',
        'whistleblower'      => 'Whistleblower Report',
        'other'              => 'Other',
    ];

    public const STATUS_LABELS = [
        'reported'             => 'Reported',
        'under_investigation'  => 'Under Investigation',
        'action_taken'         => 'Action Taken',
        'closed'               => 'Closed',
        'no_action_required'   => 'No Action Required',
    ];

    public const SEVERITY_LABELS = [
        'low'      => 'Low',
        'medium'   => 'Medium',
        'high'     => 'High',
        'critical' => 'Critical',
    ];

    // ----------------------------------------------------------------
    // Auto-generate reference
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (EthicsIncident $incident) {
            if (empty($incident->reference)) {
                $incident->reference = static::nextReference(now());
            }
        });
    }

    public static function nextReference(Carbon $date): string
    {
        $prefix = 'ETH-' . $date->format('Y') . '-' . $date->format('m');

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

    public function investigatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }
}
