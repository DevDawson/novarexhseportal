<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmsImprovementAction extends Model
{
    protected $table = 'ems_improvement_actions';

    protected $fillable = [
        'reference', 'source', 'pdca_phase', 'title', 'description', 'expected_benefit',
        'project_id', 'assigned_to_id', 'raised_by_id',
        'priority', 'status', 'target_date', 'completed_date', 'action_taken',
        'effectiveness_verified', 'effectiveness_notes', 'verified_by_id', 'verified_date',
        'target_kpi',
    ];

    protected $casts = [
        'target_date'             => 'date',
        'completed_date'          => 'date',
        'verified_date'           => 'date',
        'effectiveness_verified'  => 'boolean',
    ];

    public const SOURCE_LABELS = [
        'management_review'    => 'Management Review',
        'internal_audit'       => 'Internal Audit',
        'compliance_evaluation' => 'Compliance Evaluation',
        'kpi_analysis'         => 'KPI Analysis',
        'incident'             => 'Environmental Incident',
        'corrective_action'    => 'Corrective Action',
        'employee_suggestion'  => 'Employee Suggestion',
        'external_audit'       => 'External Audit',
        'stakeholder_feedback' => 'Stakeholder Feedback',
        'other'                => 'Other',
    ];

    public const PDCA_LABELS = [
        'plan' => 'Plan',
        'do'   => 'Do',
        'check' => 'Check',
        'act'  => 'Act',
    ];

    public const STATUS_LABELS = [
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'verified'    => 'Verified',
        'closed'      => 'Closed',
        'cancelled'   => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'open'        => 'danger',
        'in_progress' => 'warning',
        'completed'   => 'info',
        'verified'    => 'primary',
        'closed'      => 'success',
        'cancelled'   => 'gray',
    ];

    public const PRIORITY_COLORS = [
        'low'    => 'gray',
        'medium' => 'warning',
        'high'   => 'danger',
    ];

    public const TARGET_KPI_LABELS = [
        'compliance_rate'  => 'KPI 1 — Compliance Rate (CR)',
        'audit_score'      => 'KPI 2 — Audit Score (AS)',
        'capa_closure'     => 'KPI 4 — CAPA Closure Rate (CAC)',
        'objective_achievement' => 'KPI 3 — Objective Achievement Rate (OA)',
        'training_completion'   => 'KPI 5 — Training Completion Rate (TR)',
        'waste_diversion'  => 'KPI 5 — Waste Diversion Rate',
        'water_reduction'  => 'KPI 6 — Water Reduction Rate',
        'incident_rate'    => 'KPI 7 — Environmental Incident Rate',
        'emi'              => 'EMI — EMS Maturity Index',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $a) {
            if (empty($a->reference)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $a->reference = sprintf('EMS-CI-%d-%04d', $year, $count);
            }
        });
    }

    public function project(): BelongsTo    { return $this->belongsTo(Project::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_id'); }
    public function raisedBy(): BelongsTo   { return $this->belongsTo(User::class, 'raised_by_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_id'); }
}
