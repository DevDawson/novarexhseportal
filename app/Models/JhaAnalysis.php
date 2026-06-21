<?php

namespace App\Models;

use App\Services\HazopScoringService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class JhaAnalysis extends Model
{
    protected $table = 'jha_analyses';

    protected $fillable = [
        'project_id', 'jha_number', 'title', 'location', 'work_date',
        'work_description', 'department', 'total_workers', 'qualified_workers',
        'competency_compliance_pct', 'implementation_compliance_pct', 'residual_risk_reduction_pct',
        'status', 'prepared_by',
        'supervisor_approved_by', 'supervisor_approved_at',
        'hse_approved_by', 'hse_approved_at',
        'pm_approved_by', 'pm_approved_at',
        'client_approved_by', 'client_approved_at',
        'authorized_by', 'authorized_at',
        'rejection_reason', 'created_by',
    ];

    protected $casts = [
        'work_date'                => 'date',
        'supervisor_approved_at'   => 'datetime',
        'hse_approved_at'          => 'datetime',
        'pm_approved_at'           => 'datetime',
        'client_approved_at'       => 'datetime',
        'authorized_at'            => 'datetime',
        'competency_compliance_pct'    => 'decimal:2',
        'implementation_compliance_pct'=> 'decimal:2',
        'residual_risk_reduction_pct'  => 'decimal:2',
    ];

    public static array $statuses = [
        'draft'              => 'Draft',
        'submitted'          => 'Submitted',
        'supervisor_approved'=> 'Supervisor Approved',
        'hse_approved'       => 'HSE Approved',
        'pm_approved'        => 'PM Approved',
        'client_approved'    => 'Client Approved',
        'authorized'         => 'Authorized',
        'rejected'           => 'Rejected',
    ];

    public static function nextNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'JHA-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    // ── Relationships ────────────────────────────────────────────────

    public function project(): BelongsTo         { return $this->belongsTo(Project::class); }
    public function preparedBy(): BelongsTo      { return $this->belongsTo(User::class, 'prepared_by'); }
    public function supervisorApprover(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_approved_by'); }
    public function hseApprover(): BelongsTo     { return $this->belongsTo(User::class, 'hse_approved_by'); }
    public function pmApprover(): BelongsTo      { return $this->belongsTo(User::class, 'pm_approved_by'); }
    public function clientApprover(): BelongsTo  { return $this->belongsTo(User::class, 'client_approved_by'); }
    public function authorizer(): BelongsTo      { return $this->belongsTo(User::class, 'authorized_by'); }
    public function createdBy(): BelongsTo       { return $this->belongsTo(User::class, 'created_by'); }

    public function tasks(): HasMany             { return $this->hasMany(JhaTask::class, 'jha_analysis_id')->orderBy('step_number'); }
    public function legalRequirements(): HasMany { return $this->hasMany(JhaLegalRequirement::class, 'jha_analysis_id'); }
    public function competencyRequirements(): HasMany { return $this->hasMany(JhaCompetencyRequirement::class, 'jha_analysis_id'); }
    public function monitoringChecks(): HasMany  { return $this->hasMany(JhaMonitoringCheck::class, 'jha_analysis_id'); }

    // ── KPI Calculations ─────────────────────────────────────────────

    public function recalculateKpis(): void
    {
        // Competency compliance
        $competencyCompliance = $this->total_workers > 0
            ? round(($this->qualified_workers / $this->total_workers) * 100, 2)
            : 0;

        // Implementation compliance (implemented + verified controls / total controls)
        $allControls = JhaControlMeasure::whereHas('hazard.task.jhaAnalysis', fn ($q) => $q->where('id', $this->id))->count();
        $implementedControls = JhaControlMeasure::whereHas('hazard.task.jhaAnalysis', fn ($q) => $q->where('id', $this->id))
            ->whereIn('status', ['implemented', 'verified'])->count();
        $implementationCompliance = $allControls > 0 ? round(($implementedControls / $allControls) * 100, 2) : 0;

        // Average residual risk reduction across all hazards
        $hazards = JhaHazard::whereHas('task.jhaAnalysis', fn ($q) => $q->where('id', $this->id))->get();
        $totalReduction = 0;
        $hazardCount = $hazards->count();
        foreach ($hazards as $h) {
            if ($h->initial_risk_score > 0) {
                $totalReduction += (($h->initial_risk_score - $h->residual_risk_score) / $h->initial_risk_score) * 100;
            }
        }
        $residualReduction = $hazardCount > 0 ? round($totalReduction / $hazardCount, 2) : 0;

        $this->update([
            'competency_compliance_pct'    => $competencyCompliance,
            'implementation_compliance_pct'=> $implementationCompliance,
            'residual_risk_reduction_pct'  => $residualReduction,
        ]);
    }
}
