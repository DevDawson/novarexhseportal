<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'certification_name', 'issuing_body', 'certificate_number',
        'issue_date', 'expiry_date', 'status', 'document_path', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->expiry_date ? (int) now()->diffInDays($this->expiry_date, false) : null;
    }
}
