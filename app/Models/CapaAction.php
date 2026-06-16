<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapaAction extends Model
{
    use HasFactory;

    protected $table = 'capa_actions';

    protected $fillable = [
        'capa_reference', 'capa_type', 'source_type', 'incident_id', 'audit_id', 'project_id',
        'title', 'description', 'root_cause', 'category', 'priority',
        'raised_by_id', 'assigned_to_id', 'approved_by_id',
        'due_date', 'completion_date', 'action_taken', 'status',
        'effectiveness_verified', 'effectiveness_notes', 'verified_by_id', 'verified_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completion_date' => 'date',
        'verified_date' => 'date',
        'effectiveness_verified' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (CapaAction $capa) {
            if (empty($capa->capa_reference)) {
                $year = now()->format('Y');
                $month = now()->format('m');
                $count = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $capa->capa_reference = sprintf('CAPA-%s-%s-%04d', $year, $month, $count);
            }
        });
    }

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class); }
    public function audit(): BelongsTo { return $this->belongsTo(InternalAudit::class, 'audit_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function raisedBy(): BelongsTo { return $this->belongsTo(User::class, 'raised_by_id'); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_id'); }
}
