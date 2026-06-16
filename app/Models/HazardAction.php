<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'hazard_register_id', 'action_description', 'action_owner_id', 'department_id',
        'priority', 'due_date', 'verification_status', 'closure_status',
        'completed_date', 'completion_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(HazardRegister::class, 'hazard_register_id');
    }

    public function actionOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_owner_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->closure_status === 'open' && $this->due_date && $this->due_date->isPast();
    }
}
