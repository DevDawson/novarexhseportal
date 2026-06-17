<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EsiaComplianceMonitoring extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'esia_compliance_monitoring';

    protected $fillable = [
        'project_id', 'mitigation_id', 'monitoring_type',
        'parameter_monitored', 'monitoring_frequency', 'monitoring_date',
        'monitored_by', 'result_value', 'result_unit', 'result_description',
        'compliance_status', 'corrective_action',
        'corrective_action_due', 'corrective_action_completed',
        'verified_by', 'verified_at', 'evidence_file', 'notes',
    ];

    protected $casts = [
        'monitoring_date'              => 'date',
        'corrective_action_due'        => 'date',
        'corrective_action_completed'  => 'date',
        'verified_at'                  => 'date',
        'result_value'                 => 'decimal:4',
    ];

    public const MONITORING_TYPE_LABELS = [
        'self_monitoring'       => 'Self Monitoring',
        'third_party'           => 'Third-Party Audit',
        'regulatory_inspection' => 'Regulatory Inspection',
        'community_monitoring'  => 'Community Monitoring',
    ];

    public const FREQUENCY_LABELS = [
        'daily'       => 'Daily',
        'weekly'      => 'Weekly',
        'monthly'     => 'Monthly',
        'quarterly'   => 'Quarterly',
        'semi_annual' => 'Semi-Annual',
        'annual'      => 'Annual',
        'event_based' => 'Event-Based',
    ];

    public const COMPLIANCE_STATUS_LABELS = [
        'compliant'     => 'Compliant',
        'non_compliant' => 'Non-Compliant',
        'partial'       => 'Partially Compliant',
        'not_assessed'  => 'Not Yet Assessed',
    ];

    public const COMPLIANCE_STATUS_COLORS = [
        'compliant'     => 'success',
        'non_compliant' => 'danger',
        'partial'       => 'warning',
        'not_assessed'  => 'gray',
    ];

    public function getIsOverdueAttribute(): bool
    {
        return $this->compliance_status === 'non_compliant'
            && $this->corrective_action_due
            && $this->corrective_action_due->isPast()
            && ! $this->corrective_action_completed;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mitigation(): BelongsTo
    {
        return $this->belongsTo(EsiaMitigationAction::class, 'mitigation_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
