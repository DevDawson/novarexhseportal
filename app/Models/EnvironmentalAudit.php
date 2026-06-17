<?php

namespace App\Models;

use App\Services\EnvironmentalAuditService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnvironmentalAudit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'environmental_audits';

    protected $fillable = [
        'audit_number', 'audit_title', 'audit_type', 'audit_reference', 'status',
        'scope', 'objectives', 'criteria',
        'site_location', 'project_id', 'department_id',
        'audit_date', 'planned_start_date', 'planned_end_date',
        'audit_duration_days', 'audit_method',
        'team_leader_id', 'lead_auditor_id',
        'co_auditors', 'technical_experts', 'auditee_representatives',
        'compliance_score', 'rating',
        'management_summary', 'closing_notes',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'audit_date'         => 'date',
        'planned_start_date' => 'date',
        'planned_end_date'   => 'date',
        'approved_at'        => 'datetime',
        'compliance_score'   => 'decimal:2',
    ];

    public const AUDIT_TYPE_LABELS = [
        'internal'   => 'Internal Audit',
        'external'   => 'External Audit',
        'compliance' => 'Compliance Audit',
        'supplier'   => 'Supplier Audit',
        'regulatory' => 'Regulatory Audit',
    ];

    public const STATUS_LABELS = [
        'planned'     => 'Planned',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'closed'      => 'Closed',
    ];

    public const STATUS_COLORS = [
        'planned'     => 'info',
        'in_progress' => 'warning',
        'completed'   => 'primary',
        'closed'      => 'success',
    ];

    public const AUDIT_METHOD_LABELS = [
        'on_site' => 'On-Site',
        'remote'  => 'Remote',
        'hybrid'  => 'Hybrid',
    ];

    public const RATING_COLORS = [
        'excellent' => 'success',
        'good'      => 'info',
        'fair'      => 'warning',
        'poor'      => 'danger',
    ];

    // ------------------------------------------------------------------ //
    // Lifecycle hooks                                                      //
    // ------------------------------------------------------------------ //
    protected static function booted(): void
    {
        static::creating(function (self $a) {
            if (empty($a->audit_number)) {
                $a->audit_number = EnvironmentalAuditService::nextAuditNumber();
            }
        });

        // Auto-seed the 42 checklist items on first creation
        static::created(function (self $a) {
            $items = EnvironmentalAuditService::defaultChecklistItems();
            $rows  = array_map(fn ($i) => [
                'audit_id'         => $a->id,
                'category'         => $i['category'],
                'item_code'        => $i['code'],
                'item_description' => $i['description'],
                'compliance_status' => 'not_applicable',
                'sort_order'       => $i['sort'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ], $items);

            // Use insert (no model events) to avoid cascading score recalculation on creation
            \DB::table('environmental_audit_checklist_items')->insert($rows);
        });
    }

    // ------------------------------------------------------------------ //
    // Score recomputation (called by ChecklistItem saved hook)             //
    // ------------------------------------------------------------------ //
    public function recomputeScore(): void
    {
        $result = EnvironmentalAuditService::computeScore($this);
        $this->timestamps = false;
        $this->update([
            'compliance_score' => $result['score'],
            'rating'           => $result['rating'],
        ]);
        $this->timestamps = true;
    }

    // ------------------------------------------------------------------ //
    // Relationships                                                        //
    // ------------------------------------------------------------------ //
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(EnvironmentalAuditChecklistItem::class, 'audit_id')
                    ->orderBy('sort_order');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(EnvironmentalAuditFinding::class, 'audit_id')
                    ->orderBy('finding_number');
    }
}
