<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_days_per_year',
        'is_paid',
    ];

    protected $casts = [
        'max_days_per_year' => 'integer',
        'is_paid' => 'boolean',
    ];

    /**
     * All leave requests made under this leave type.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
