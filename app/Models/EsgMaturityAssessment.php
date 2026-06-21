<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsgMaturityAssessment extends Model
{
    protected $table = 'esg_maturity_assessments';

    protected $fillable = [
        'period', 'period_type', 'status', 'assessed_by_id', 'assessed_at', 'notes',
        // E indicators
        'cr_score', 'wr_score', 'er_score', 'wtr_score', 'ems_score',
        // S indicators
        'tr_score', 'ltifr_score', 'ewr_score', 'csr_score', 'dei_score',
        // G indicators
        'ccr_score', 'acr_score', 'dcr_score', 'ecr_score', 'mrr_score',
        // Computed
        'e_score', 's_score', 'g_score', 'esg_mi',
        'auto_sources',
    ];

    protected $casts = [
        'assessed_at'   => 'datetime',
        'auto_sources'  => 'array',
        'cr_score' => 'decimal:2', 'wr_score' => 'decimal:2', 'er_score' => 'decimal:2',
        'wtr_score' => 'decimal:2', 'ems_score' => 'decimal:2',
        'tr_score' => 'decimal:2', 'ltifr_score' => 'decimal:2', 'ewr_score' => 'decimal:2',
        'csr_score' => 'decimal:2', 'dei_score' => 'decimal:2',
        'ccr_score' => 'decimal:2', 'acr_score' => 'decimal:2', 'dcr_score' => 'decimal:2',
        'ecr_score' => 'decimal:2', 'mrr_score' => 'decimal:2',
        'e_score' => 'decimal:2', 's_score' => 'decimal:2',
        'g_score' => 'decimal:2', 'esg_mi' => 'decimal:2',
    ];

    // ── ESG-MI Maturity levels per spec ──────────────────────────────────
    public static function emiToLevel(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Transformational',
            $score >= 80 => 'Advanced',
            $score >= 70 => 'Managed',
            $score >= 60 => 'Developing',
            default      => 'Initial',
        };
    }

    public static function emiToColor(string $level): string
    {
        return match ($level) {
            'Transformational' => 'success',
            'Advanced'         => 'info',
            'Managed'          => 'primary',
            'Developing'       => 'warning',
            default            => 'danger',
        };
    }

    public static array $LEVEL_DESCRIPTIONS = [
        'Transformational' => 'Industry-leading ESG performance; proactive beyond compliance',
        'Advanced'         => 'Strong ESG integration with measurable improvements',
        'Managed'          => 'Systematic ESG management with defined processes',
        'Developing'       => 'Basic ESG practices in place; significant gaps remain',
        'Initial'          => 'ESG efforts are informal; significant improvement required',
    ];

    // ── Indicator metadata ────────────────────────────────────────────────
    public static array $INDICATORS = [
        // Environmental
        'cr'    => ['label' => 'Compliance Rate',               'component' => 'E', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'cr_score'],
        'wr'    => ['label' => 'Waste Diversion Rate',          'component' => 'E', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'wr_score'],
        'er'    => ['label' => 'Emissions Reduction Rate',      'component' => 'E', 'weight_in' => 1, 'source' => 'semi_auto', 'score_field' => 'er_score'],
        'wtr'   => ['label' => 'Water Reduction Efficiency',    'component' => 'E', 'weight_in' => 1, 'source' => 'semi_auto', 'score_field' => 'wtr_score'],
        'ems'   => ['label' => 'EMS Maturity Index',            'component' => 'E', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'ems_score'],
        // Social
        'tr'    => ['label' => 'Training Completion Rate',      'component' => 'S', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'tr_score'],
        'ltifr' => ['label' => 'LTIFR Performance Score',       'component' => 'S', 'weight_in' => 1, 'source' => 'semi_auto', 'score_field' => 'ltifr_score'],
        'ewr'   => ['label' => 'Employee Well-being Score',     'component' => 'S', 'weight_in' => 1, 'source' => 'manual',    'score_field' => 'ewr_score'],
        'csr'   => ['label' => 'Community Engagement Score',    'component' => 'S', 'weight_in' => 1, 'source' => 'semi_auto', 'score_field' => 'csr_score'],
        'dei'   => ['label' => 'Diversity, Equity & Inclusion', 'component' => 'S', 'weight_in' => 1, 'source' => 'semi_auto', 'score_field' => 'dei_score'],
        // Governance
        'ccr'   => ['label' => 'Compliance & Ethics Score',     'component' => 'G', 'weight_in' => 1, 'source' => 'semi_auto', 'score_field' => 'ccr_score'],
        'acr'   => ['label' => 'Audit Closure Rate',            'component' => 'G', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'acr_score'],
        'dcr'   => ['label' => 'Document Control Rate',         'component' => 'G', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'dcr_score'],
        'ecr'   => ['label' => 'Corrective Action Closure',     'component' => 'G', 'weight_in' => 1, 'source' => 'auto',      'score_field' => 'ecr_score'],
        'mrr'   => ['label' => 'Management Review Rate',        'component' => 'G', 'weight_in' => 1, 'source' => 'manual',    'score_field' => 'mrr_score'],
    ];

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by_id');
    }
}
