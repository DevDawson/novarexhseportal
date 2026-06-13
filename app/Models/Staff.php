<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'staff_no',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'national_id',
        'nssf_no',
        'tin_no',
        'nhif_no',
        'job_title',
        'department_id',
        'employment_type',
        'date_joined',
        'basic_salary',
        'bank_name',
        'bank_account_no',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    /**
     * The linked system user account (login), if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The department this staff member belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * All leave requests submitted by this staff member.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * All payroll records for this staff member.
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * All field expenses claimed by this staff member.
     */
    public function fieldExpenses(): HasMany
    {
        return $this->hasMany(FieldExpense::class);
    }

    /**
     * Accessor for the staff member's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
