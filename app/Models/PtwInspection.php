<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtwInspection extends Model
{
    protected $fillable = [
        'permit_to_work_id',
        'inspector_id',
        'inspected_at',
        'findings',
        'compliance_score',
        'corrective_actions',
        'is_compliant',
    ];

    protected $casts = [
        'inspected_at'     => 'datetime',
        'compliance_score' => 'integer',
        'is_compliant'     => 'boolean',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(PermitToWork::class, 'permit_to_work_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function getComplianceBadgeColorAttribute(): string
    {
        if ($this->compliance_score >= 80) return 'success';
        if ($this->compliance_score >= 60) return 'warning';
        return 'danger';
    }
}
