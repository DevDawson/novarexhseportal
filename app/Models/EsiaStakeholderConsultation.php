<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EsiaStakeholderConsultation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'esia_stakeholder_consultations';

    protected $fillable = [
        'project_id', 'screening_id', 'consultation_type',
        'title', 'description', 'venue', 'consultation_date',
        'number_attended', 'facilitator', 'stakeholder_groups',
        'key_concerns_raised', 'responses_given', 'how_incorporated',
        'status', 'conducted_by', 'minutes_file', 'notes',
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'number_attended'   => 'integer',
    ];

    public const CONSULTATION_TYPE_LABELS = [
        'public_meeting'       => 'Public Meeting',
        'focus_group'          => 'Focus Group Discussion',
        'written_comment'      => 'Written Comment / Survey',
        'site_visit'           => 'Site Visit / Walkover',
        'workshop'             => 'Workshop / Seminar',
        'expert_consultation'  => 'Expert / Technical Consultation',
        'other'                => 'Other',
    ];

    public const CONSULTATION_TYPE_ICONS = [
        'public_meeting'       => 'heroicon-o-user-group',
        'focus_group'          => 'heroicon-o-users',
        'written_comment'      => 'heroicon-o-pencil-square',
        'site_visit'           => 'heroicon-o-map-pin',
        'workshop'             => 'heroicon-o-academic-cap',
        'expert_consultation'  => 'heroicon-o-beaker',
        'other'                => 'heroicon-o-ellipsis-horizontal-circle',
    ];

    public const STATUS_LABELS = [
        'planned'   => 'Planned',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'planned'   => 'info',
        'completed' => 'success',
        'cancelled' => 'gray',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(EsiaScreening::class, 'screening_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
