<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_type',
        'period',
        'value',
        'unit',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public const INDICATOR_LABELS = [
        'total_employees'            => 'Total Employees',
        'female_employees'           => 'Female Employees',
        'local_employees'            => 'Local Employees',
        'employees_trained'          => 'Employees Trained',
        'training_hours'             => 'Training Hours',
        'community_investment_amount'=> 'Community Investment (Amount)',
        'community_beneficiaries'    => 'Community Beneficiaries',
        'local_procurement_percent'  => 'Local Procurement (%)',
        'contractor_workforce'       => 'Contractor Workforce',
        'new_hires'                  => 'New Hires',
        'employee_turnover'          => 'Employee Turnover',
        'lost_workdays'              => 'Lost Workdays',
    ];

    public const INDICATOR_DEFAULT_UNITS = [
        'total_employees'            => 'persons',
        'female_employees'           => 'persons',
        'local_employees'            => 'persons',
        'employees_trained'          => 'persons',
        'training_hours'             => 'hours',
        'community_investment_amount'=> 'USD',
        'community_beneficiaries'    => 'persons',
        'local_procurement_percent'  => '%',
        'contractor_workforce'       => 'persons',
        'new_hires'                  => 'persons',
        'employee_turnover'          => '%',
        'lost_workdays'              => 'days',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
