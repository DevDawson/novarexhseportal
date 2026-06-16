<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class InternalAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_reference', 'audit_type', 'standard', 'standard_other', 'scope',
        'audit_objectives', 'audit_criteria',
        'project_id', 'department_id',
        'audit_date', 'planned_start_date', 'planned_end_date',
        'lead_auditor_id', 'status',
        'opening_meeting_notes', 'closing_meeting_notes', 'summary', 'report_file',
        'closure_verification_notes', 'closure_verified_by_id', 'closure_date',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'closure_date' => 'date',
    ];

    public const AUDIT_TYPE_LABELS = [
        'internal'       => 'Internal Audit',
        'external'       => 'External Audit',
        'certification'  => 'Certification Audit',
        'surveillance'   => 'Surveillance Audit',
        'supplier'       => 'Supplier Audit',
    ];

    public const STANDARD_LABELS = [
        'iso9001'         => 'ISO 9001 — Quality Management',
        'iso14001'        => 'ISO 14001 — Environmental Management',
        'iso45001'        => 'ISO 45001 — OH&S Management',
        'client_specific' => 'Client-Specific Requirements',
        'other'           => 'Other',
    ];

    public const STATUS_LABELS = [
        'planned'     => 'Planned',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'closed'      => 'Closed',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'audit_team_members');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AuditFinding::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function closureVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closure_verified_by_id');
    }

    public function capaActions(): HasMany
    {
        return $this->hasMany(CapaAction::class, 'audit_id');
    }

    public function lessonsLearned(): HasMany
    {
        return $this->hasMany(LessonsLearned::class, 'audit_id');
    }

    // ----------------------------------------------------------------
    // Auto-generate audit_reference on creating
    // ----------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (InternalAudit $audit) {
            if (empty($audit->audit_reference)) {
                $audit->audit_reference = self::nextReference(now());
            }
        });
    }

    /**
     * Generate next sequential audit reference, e.g. "AUD-2026-06-0001".
     * Mirrors JournalEntry::nextReference() pattern.
     */
    public static function nextReference(Carbon $date): string
    {
        $prefix = 'AUD-' . $date->format('Y-m') . '-';

        $lastNumber = self::where('audit_reference', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(audit_reference, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as max_num')
            ->value('max_num');

        $next = ((int) $lastNumber) + 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    /**
     * Count of non-conformity findings (minor + major) for display.
     */
    public function getNonConformityCountAttribute(): int
    {
        return $this->findings()
            ->whereIn('finding_type', ['minor_nonconformity', 'major_nonconformity'])
            ->count();
    }
}
