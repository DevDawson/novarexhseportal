<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtwIsolationRecord extends Model
{
    protected $fillable = [
        'permit_to_work_id',
        'equipment_tag',
        'equipment_description',
        'isolation_type',
        'isolation_point',
        'locked_by_id',
        'lock_applied_at',
        'key_number',
        'is_verified',
        'verified_by_id',
        'verified_at',
        'released_at',
        'released_by_id',
        'release_notes',
    ];

    protected $casts = [
        'lock_applied_at' => 'datetime',
        'verified_at'     => 'datetime',
        'released_at'     => 'datetime',
        'is_verified'     => 'boolean',
    ];

    public const ISOLATION_TYPE_LABELS = [
        'electrical'  => 'Electrical',
        'mechanical'  => 'Mechanical',
        'pneumatic'   => 'Pneumatic',
        'hydraulic'   => 'Hydraulic',
        'thermal'     => 'Thermal',
        'gravity'     => 'Gravity / Stored Energy',
        'other'       => 'Other',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(PermitToWork::class, 'permit_to_work_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by_id');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->released_at === null;
    }
}
