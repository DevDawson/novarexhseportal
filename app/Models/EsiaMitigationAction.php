<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsiaMitigationAction extends Model
{
    use HasFactory;

    protected $table = 'esia_mitigation_actions';

    protected $fillable = [
        'project_id', 'impact_id', 'mitigation_type', 'activity_description',
        'phase', 'responsible_party', 'timeline_start', 'timeline_end',
        'estimated_cost', 'cost_currency', 'kpi', 'monitoring_frequency',
        'status', 'actual_completion_date', 'completion_notes',
    ];

    protected $casts = [
        'timeline_start'         => 'date',
        'timeline_end'           => 'date',
        'actual_completion_date' => 'date',
        'estimated_cost'         => 'decimal:2',
    ];

    public const TYPE_LABELS = [
        'avoid'    => 'Avoid',
        'minimize' => 'Minimize',
        'restore'  => 'Restore',
        'offset'   => 'Offset',
        'enhance'  => 'Enhance (positive)',
    ];

    public const STATUS_LABELS = [
        'planned'     => 'Planned',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'overdue'     => 'Overdue',
        'cancelled'   => 'Cancelled',
    ];

    public const PHASE_LABELS = [
        'pre_construction' => 'Pre-Construction',
        'construction'     => 'Construction',
        'operation'        => 'Operation',
        'decommissioning'  => 'Decommissioning',
        'all'              => 'All Phases',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function impact(): BelongsTo
    {
        return $this->belongsTo(EsiaImpactAssessment::class, 'impact_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'completed'
            && $this->status !== 'cancelled'
            && $this->timeline_end
            && $this->timeline_end->isPast();
    }
}
