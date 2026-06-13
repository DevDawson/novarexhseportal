<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'reported_by',
        'incident_date',
        'location',
        'incident_type',
        'severity',
        'description',
        'immediate_action',
        'root_cause',
        'corrective_actions',
        'status',
        'closed_date',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'closed_date' => 'date',
    ];

    /**
     * The project this incident relates to (nullable for company-wide incidents).
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user who reported this incident.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
