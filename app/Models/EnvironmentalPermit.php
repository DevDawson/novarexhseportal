<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentalPermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_number', 'project_id', 'permit_type', 'issuing_authority',
        'issue_date', 'expiry_date', 'permit_conditions', 'status',
        'document_path', 'responsible_officer_id', 'renewal_reminder_days', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function responsibleOfficer(): BelongsTo { return $this->belongsTo(User::class, 'responsible_officer_id'); }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->expiry_date ? (int) now()->diffInDays($this->expiry_date, false) : null;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
