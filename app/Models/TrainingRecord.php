<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'training_title', 'training_type', 'provider', 'topic',
        'date_attended', 'duration_hours', 'result', 'certificate_number',
        'expiry_date', 'conducted_by', 'verified_by_id', 'notes',
    ];

    protected $casts = [
        'date_attended' => 'date',
        'expiry_date' => 'date',
    ];

    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_id'); }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(now()) <= 30;
    }
}
