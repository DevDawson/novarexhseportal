<?php

namespace App\Models;

use App\Services\HazopScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazopNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'hazop_study_id',
        'node_number',
        'node_name',
        'parameter',
        'guide_word',
        'deviation',
        'cause',
        'consequence',
        'existing_safeguards',
        // Initial risk
        'likelihood',
        'severity',
        'exposure',
        'initial_risk_score',
        'risk_classification',
        // RPN
        'rpn_severity',
        'rpn_occurrence',
        'rpn_detectability',
        'rpn_score',
        // Controls
        'recommended_actions',
        'control_effectiveness',
        'residual_risk_score',
        'risk_reduction_factor',
        'residual_risk_classification',
        // Action
        'risk_owner_id',
        'department_id',
        'due_date',
        // Approval
        'approval_status',
        'approved_by_id',
        'approval_date',
        // Closure
        'closure_verification',
        'closure_verified_by_id',
        'closure_date',
        'status',
    ];

    protected $casts = [
        'due_date'           => 'date',
        'approval_date'      => 'date',
        'closure_date'       => 'date',
        'likelihood'         => 'integer',
        'severity'           => 'integer',
        'exposure'           => 'integer',
        'initial_risk_score' => 'integer',
        'rpn_severity'       => 'integer',
        'rpn_occurrence'     => 'integer',
        'rpn_detectability'  => 'integer',
        'rpn_score'          => 'integer',
        'control_effectiveness'    => 'float',
        'residual_risk_score'      => 'float',
        'risk_reduction_factor'    => 'float',
    ];

    public const STATUS_LABELS = [
        'open'                 => 'Open',
        'action_assigned'      => 'Action Assigned',
        'in_progress'          => 'In Progress',
        'verification_pending' => 'Verification Pending',
        'closed'               => 'Closed',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function study(): BelongsTo { return $this->belongsTo(HazopStudy::class, 'hazop_study_id'); }
    public function riskOwner(): BelongsTo { return $this->belongsTo(User::class, 'risk_owner_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
    public function closureVerifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'closure_verified_by_id'); }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'closed' && $this->due_date && $this->due_date->isPast();
    }

    public function getInitialRiskLevelAttribute(): string
    {
        return HazopScoringService::riskLevel($this->initial_risk_score);
    }

    public function getResidualRiskLevelAttribute(): string
    {
        return HazopScoringService::riskLevel($this->residual_risk_score);
    }

    // ----------------------------------------------------------------
    // Auto-compute all risk scores on save
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (HazopNode $node) {
            // L×S×E
            $ir = HazopScoringService::initialScore(
                (int) $node->likelihood,
                (int) $node->severity,
                (int) $node->exposure,
            );
            $node->initial_risk_score = $ir;
            $node->risk_classification = HazopScoringService::riskLevel($ir);

            // RPN
            $node->rpn_score = HazopScoringService::rpn(
                (int) $node->rpn_severity,
                (int) $node->rpn_occurrence,
                (int) $node->rpn_detectability,
            );

            // Residual risk & RRF
            $rr = HazopScoringService::residualRisk($ir, (float) $node->control_effectiveness);
            $node->residual_risk_score = $rr;
            $node->risk_reduction_factor = HazopScoringService::rrf($ir, $rr);
            $node->residual_risk_classification = HazopScoringService::riskLevel($rr);
        });
    }
}
