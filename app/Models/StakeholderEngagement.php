<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StakeholderEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stakeholder_id',
        'engagement_date',
        'method',
        'topic',
        'summary',
        'outcome',
        'commitments_made',
        'follow_up_date',
        'follow_up_completed',
        'conducted_by',
    ];

    protected $casts = [
        'engagement_date'     => 'date',
        'follow_up_date'      => 'date',
        'follow_up_completed' => 'boolean',
    ];

    public const METHOD_LABELS = [
        'meeting'              => 'Meeting',
        'consultation'         => 'Consultation',
        'survey'               => 'Survey',
        'site_visit'           => 'Site Visit',
        'written_communication'=> 'Written Communication',
        'public_hearing'       => 'Public Hearing',
        'focus_group'          => 'Focus Group',
        'other'                => 'Other',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class);
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getIsFollowUpOverdueAttribute(): bool
    {
        return $this->follow_up_date
            && ! $this->follow_up_completed
            && $this->follow_up_date->isPast();
    }
}
