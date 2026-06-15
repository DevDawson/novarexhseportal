<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PermitToWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_number',
        'permit_type',
        'project_id',
        'location',
        'description',
        'valid_from',
        'valid_to',
        'requested_by',
        'issued_by',
        'area_authority_id',
        'hazards_identified',
        'precautions_taken',
        'ppe_required',
        'emergency_procedures',
        'isolation_required',
        'isolation_details',
        'gas_test_required',
        'gas_test_results',
        'status',
        'suspension_reason',
        'closeout_notes',
        'closeout_by',
        'closeout_at',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'ppe_required' => 'array',
        'gas_test_results' => 'array',
        'isolation_required' => 'boolean',
        'gas_test_required' => 'boolean',
        'closeout_at' => 'datetime',
    ];

    public const PERMIT_TYPE_LABELS = [
        'hot_work' => 'Hot Work',
        'confined_space' => 'Confined Space Entry',
        'working_at_height' => 'Working at Height',
        'electrical_isolation' => 'Electrical Isolation (LOTO)',
        'excavation' => 'Excavation',
        'lifting_operations' => 'Lifting Operations',
        'cold_work' => 'Cold Work',
        'general' => 'General Work',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'submitted' => 'Submitted for Approval',
        'approved' => 'Approved',
        'active' => 'Active',
        'suspended' => 'Suspended',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ];

    // =================================================================
    // Relationships
    // =================================================================
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function closeoutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closeout_by');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(PermitChecklistItem::class)->orderBy('sort_order');
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(PermitExtension::class);
    }

    // =================================================================
    // Accessors
    // =================================================================

    /**
     * Whether the permit's validity window has passed but it hasn't
     * been formally closed/cancelled - i.e. it's overdue for closeout.
     */
    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, ['approved', 'active', 'suspended'])
            && $this->valid_to !== null
            && $this->valid_to->isPast();
    }

    /**
     * Hours remaining until the permit expires (negative if already
     * past valid_to).
     */
    public function getHoursRemainingAttribute(): ?float
    {
        if (! $this->valid_to) {
            return null;
        }

        return round(Carbon::now()->diffInMinutes($this->valid_to, false) / 60, 1);
    }

    public function getPpeRequiredLabelsAttribute(): array
    {
        $options = \App\Services\PermitToWorkService::ppeOptions();

        return collect($this->ppe_required ?? [])
            ->map(fn ($key) => $options[$key] ?? $key)
            ->all();
    }

    // =================================================================
    // Lifecycle
    // =================================================================
    protected static function booted(): void
    {
        static::creating(function (PermitToWork $permit) {
            if (empty($permit->permit_number)) {
                $permit->permit_number = \App\Services\PermitToWorkService::nextPermitNumber(now());
            }
        });
    }
}
