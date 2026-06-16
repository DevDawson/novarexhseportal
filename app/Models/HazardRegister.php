<?php

namespace App\Models;

use App\Services\RiskScoringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HazardRegister extends Model
{
    use HasFactory;

    protected $table = 'hazard_register';

    protected $fillable = [
        'hazard_id',
        'project_id',
        'department_id',
        'date_identified',
        'identified_by_id',
        'activity_task',
        'location',
        'hazard_category',
        'hazard_source',
        'hazard_description',
        'potential_causes',
        'potential_consequences',
        'who_might_be_harmed',
        'justification_of_risk_rating',
        // Initial risk
        'initial_likelihood',
        'initial_severity',
        'initial_risk_score',
        // Controls
        'existing_controls',
        'additional_controls',
        'additional_controls_description',
        // Residual risk
        'residual_likelihood',
        'residual_severity',
        'residual_risk_score',
        // Action tracking
        'responsible_person_id',
        'priority_level',
        'escalation_level',
        'target_completion_date',
        'review_date',
        // Approval
        'approved_by_id',
        'approval_date',
        // Verification
        'verification_method',
        'verification_evidence',
        'verified_by_id',
        'verification_date',
        // Closure
        'closure_comments',
        'closed_by_id',
        'closure_date',
        'status',
    ];

    protected $casts = [
        'review_date' => 'date',
        'date_identified' => 'date',
        'target_completion_date' => 'date',
        'approval_date' => 'date',
        'verification_date' => 'date',
        'closure_date' => 'date',
        'initial_likelihood' => 'integer',
        'initial_severity' => 'integer',
        'initial_risk_score' => 'integer',
        'residual_likelihood' => 'integer',
        'residual_severity' => 'integer',
        'residual_risk_score' => 'integer',
        'additional_controls' => 'array',
    ];

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    public const HAZARD_CATEGORY_LABELS = [
        'physical'      => 'Physical',
        'chemical'      => 'Chemical',
        'biological'    => 'Biological',
        'ergonomic'     => 'Ergonomic',
        'psychosocial'  => 'Psychosocial',
        'environmental' => 'Environmental',
        'mechanical'    => 'Mechanical',
        'electrical'    => 'Electrical',
        'fire'          => 'Fire / Explosion',
        'radiation'     => 'Radiation',
    ];

    public const STATUS_LABELS = [
        'draft'                => 'Draft',
        'open'                 => 'Open',
        'under_assessment'     => 'Under Assessment',
        'action_required'      => 'Action Required',
        'controls_in_progress' => 'Controls In Progress',
        'verification_pending' => 'Verification Pending',
        'controlled'           => 'Controlled',
        'closed'               => 'Closed',
    ];

    public const WORKFLOW_COLORS = [
        'draft'                => 'gray',
        'open'                 => 'danger',
        'under_assessment'     => 'warning',
        'action_required'      => 'danger',
        'controls_in_progress' => 'primary',
        'verification_pending' => 'warning',
        'controlled'           => 'success',
        'closed'               => 'gray',
    ];

    public const CONTROL_HIERARCHY_OPTIONS = [
        'elimination'    => '1. Elimination — Remove the hazard entirely',
        'substitution'   => '2. Substitution — Replace with a less hazardous option',
        'engineering'    => '3. Engineering Controls — Isolate people from the hazard',
        'administrative' => '4. Administrative Controls — Change the way people work',
        'ppe'            => '5. PPE — Protect the individual',
    ];

    public const ESCALATION_LABELS = [
        'supervisor'      => 'Supervisor',
        'hse_officer'     => 'HSE Officer',
        'hse_manager'     => 'HSE Manager',
        'top_management'  => 'Top Management (MD / CEO)',
    ];

    public const PRIORITY_COLORS = [
        'low'      => 'success',
        'medium'   => 'warning',
        'high'     => 'danger',
        'critical' => 'primary',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function identifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'identified_by_id'); }
    public function responsiblePerson(): BelongsTo { return $this->belongsTo(User::class, 'responsible_person_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_id'); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by_id'); }

    public function actions(): HasMany { return $this->hasMany(HazardAction::class, 'hazard_register_id'); }
    public function attachments(): HasMany { return $this->hasMany(HazardAttachment::class, 'hazard_register_id'); }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getInitialRiskLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->initial_risk_score);
    }

    public function getResidualRiskLevelAttribute(): string
    {
        return RiskScoringService::level((int) $this->residual_risk_score);
    }

    public function getOpenActionsCountAttribute(): int
    {
        return $this->actions()->where('closure_status', 'open')->count();
    }

    public function getOverdueActionsCountAttribute(): int
    {
        return $this->actions()->where('closure_status', 'open')->where('due_date', '<', now())->count();
    }

    // ----------------------------------------------------------------
    // Auto-generate hazard_id + risk scores on save
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (HazardRegister $hazard) {
            if (empty($hazard->hazard_id)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $count = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $hazard->hazard_id = sprintf('HZ-%s-%s-%04d', $year, $month, $count);
            }
        });

        static::saving(function (HazardRegister $hazard) {
            $hazard->initial_risk_score = RiskScoringService::score(
                (int) $hazard->initial_likelihood,
                (int) $hazard->initial_severity,
            );
            $hazard->residual_risk_score = RiskScoringService::score(
                (int) $hazard->residual_likelihood,
                (int) $hazard->residual_severity,
            );
        });
    }
}
