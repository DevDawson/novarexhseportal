<?php

namespace App\Models;

use App\Services\PermitToWorkService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PermitToWork extends Model
{
    use HasFactory;

    protected $fillable = [
        // Core identifiers
        'permit_number',
        'permit_type',
        'project_id',
        'work_order_id',
        'status',
        // Location & timing
        'location',
        'site_area',
        'department_id',
        'valid_from',
        'valid_to',
        'duration_estimate',
        'actual_start',
        'actual_completion',
        // Personnel
        'requested_by',
        'issued_by',
        'area_authority_id',
        'supervisor_id',
        'contractor_company',
        'contractor_name',
        'number_of_workers',
        // Risk assessment
        'likelihood',
        'severity',
        'risk_score',
        'risk_classification',
        'linked_hazard_id',
        'linked_hazop_node_id',
        // Hazards & controls
        'hazards_identified',
        'precautions_taken',
        'ppe_required',
        'emergency_procedures',
        // Safety controls (LOTO / isolations)
        'isolation_required',
        'isolation_details',
        'loto_verified',
        // Gas testing
        'gas_test_required',
        'gas_test_results',
        'gas_testing_verified',
        // Safety flags
        'fire_watch_required',
        'fire_watch_confirmed',
        'barricading_required',
        'barricading_confirmed',
        'emergency_standby_required',
        'emergency_standby_confirmed',
        // Approval workflow
        'current_approval_stage',
        'approved_at',
        'final_approved_by_id',
        // Closure
        'suspension_reason',
        'closeout_notes',
        'closeout_by',
        'closeout_at',
        'completion_confirmed_by_id',
        'completion_date',
        'final_inspection_notes',
        'linked_incident_id',
    ];

    protected $casts = [
        'valid_from'                  => 'datetime',
        'valid_to'                    => 'datetime',
        'actual_start'                => 'datetime',
        'actual_completion'           => 'datetime',
        'approved_at'                 => 'datetime',
        'closeout_at'                 => 'datetime',
        'completion_date'             => 'date',
        'ppe_required'                => 'array',
        'gas_test_results'            => 'array',
        'isolation_required'          => 'boolean',
        'loto_verified'               => 'boolean',
        'gas_test_required'           => 'boolean',
        'gas_testing_verified'        => 'boolean',
        'fire_watch_required'         => 'boolean',
        'fire_watch_confirmed'        => 'boolean',
        'barricading_required'        => 'boolean',
        'barricading_confirmed'       => 'boolean',
        'emergency_standby_required'  => 'boolean',
        'emergency_standby_confirmed' => 'boolean',
        'likelihood'                  => 'integer',
        'severity'                    => 'integer',
        'risk_score'                  => 'integer',
        'number_of_workers'           => 'integer',
    ];

    // =================================================================
    // Constants
    // =================================================================

    public const PERMIT_TYPE_LABELS = [
        'hot_work'             => 'Hot Work (Welding, Cutting, Grinding)',
        'cold_work'            => 'Cold Work (Mechanical Maintenance)',
        'electrical_isolation' => 'Electrical Work / Isolation (LOTO)',
        'confined_space'       => 'Confined Space Entry',
        'excavation'           => 'Excavation / Trenching Work',
        'working_at_height'    => 'Working at Height',
        'lifting_operations'   => 'Lifting Operations (Crane, Hoist, Rigging)',
        'pressure_system'      => 'Pressure System Work',
        'chemical_handling'    => 'Chemical Handling / Exposure Work',
        'radiation_work'       => 'Radiation Work',
        'commissioning'        => 'Commissioning / Testing Work',
        'general_maintenance'  => 'General Maintenance Work',
        'general'              => 'General Work',
    ];

    public const STATUS_LABELS = [
        'draft'                  => 'Draft',
        'submitted'              => 'Submitted for Approval',
        'under_review'           => 'Under Review',
        'preparation_verified'   => 'Preparation Verified',
        'approved'               => 'Approved',
        'active'                 => 'Active / Work in Progress',
        'suspended'              => 'Suspended',
        'closed'                 => 'Closed',
        'cancelled'              => 'Cancelled',
        'expired'                => 'Expired',
    ];

    public const STATUS_COLORS = [
        'draft'                => 'gray',
        'submitted'            => 'info',
        'under_review'         => 'warning',
        'preparation_verified' => 'info',
        'approved'             => 'success',
        'active'               => 'success',
        'suspended'            => 'warning',
        'closed'               => 'gray',
        'cancelled'            => 'danger',
        'expired'              => 'danger',
    ];

    public const APPROVAL_STAGE_LABELS = [
        'supervisor'   => 'Supervisor Approval',
        'hse_officer'  => 'HSE Officer Approval',
        'site_manager' => 'Site Manager Final Approval',
    ];

    public const RISK_CLASSIFICATION_COLORS = [
        'high'   => 'danger',
        'medium' => 'warning',
        'low'    => 'success',
    ];

    // =================================================================
    // Relationships
    // =================================================================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function areaAuthority(): BelongsTo
    {
        return $this->belongsTo(User::class, 'area_authority_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function closeoutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closeout_by');
    }

    public function finalApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approved_by_id');
    }

    public function completionConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completion_confirmed_by_id');
    }

    public function linkedHazard(): BelongsTo
    {
        return $this->belongsTo(HazardRegister::class, 'linked_hazard_id');
    }

    public function linkedHazopNode(): BelongsTo
    {
        return $this->belongsTo(HazopNode::class, 'linked_hazop_node_id');
    }

    public function linkedIncident(): BelongsTo
    {
        return $this->belongsTo(Incident::class, 'linked_incident_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(PermitChecklistItem::class)->orderBy('sort_order');
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(PermitExtension::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PtwApproval::class)->orderBy('created_at');
    }

    public function isolationRecords(): HasMany
    {
        return $this->hasMany(PtwIsolationRecord::class);
    }

    public function toolboxTalks(): HasMany
    {
        return $this->hasMany(PtwToolboxTalk::class)->orderBy('conducted_at');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(PtwInspection::class)->orderBy('inspected_at');
    }

    // =================================================================
    // Accessors
    // =================================================================

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, ['approved', 'active', 'suspended'], true)
            && $this->valid_to !== null
            && $this->valid_to->isPast();
    }

    public function getHoursRemainingAttribute(): ?float
    {
        if (! $this->valid_to) {
            return null;
        }
        return round(Carbon::now()->diffInMinutes($this->valid_to, false) / 60, 1);
    }

    public function getPpeRequiredLabelsAttribute(): array
    {
        $options = PermitToWorkService::ppeOptions();
        return collect($this->ppe_required ?? [])
            ->map(fn ($key) => $options[$key] ?? $key)
            ->all();
    }

    public function getRiskScoreBadgeColorAttribute(): string
    {
        return PermitToWorkService::riskClassificationColor($this->risk_classification ?? 'low');
    }

    // =================================================================
    // Lifecycle
    // =================================================================

    protected static function booted(): void
    {
        static::creating(function (PermitToWork $permit) {
            if (empty($permit->permit_number)) {
                $permit->permit_number = PermitToWorkService::nextPermitNumber(now());
            }
        });

        static::saving(function (PermitToWork $permit) {
            $l = (int) ($permit->likelihood ?? 0);
            $s = (int) ($permit->severity ?? 0);

            if ($l > 0 && $s > 0) {
                $score = $l * $s;
                $permit->risk_score = $score;
                $permit->risk_classification = PermitToWorkService::riskClassification(
                    $score,
                    $permit->permit_type ?? 'general'
                );
            }
        });
    }
}
