<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EsiaProjectRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'esia_project_registrations';

    protected $fillable = [
        'project_id', 'esia_ref_number', 'project_type',
        'proponent_name', 'proponent_contact', 'proponent_address',
        'project_location', 'district', 'region',
        'project_area_ha', 'estimated_investment',
        'proposed_start_date', 'proposed_end_date',
        'esia_class', 'esia_required', 'lead_consultant', 'lead_consultant_contact',
        'registration_status', 'registered_by', 'registered_at',
        'project_objectives', 'project_components', 'notes',
    ];

    protected $casts = [
        'proposed_start_date' => 'date',
        'proposed_end_date'   => 'date',
        'registered_at'       => 'date',
        'project_area_ha'     => 'decimal:2',
        'estimated_investment' => 'decimal:2',
        'esia_required'       => 'boolean',
    ];

    public const PROJECT_TYPE_LABELS = [
        'residential'      => 'Residential / Housing',
        'commercial'       => 'Commercial',
        'industrial'       => 'Industrial',
        'infrastructure'   => 'Infrastructure',
        'mining'           => 'Mining / Extractive',
        'agriculture'      => 'Agriculture / Irrigation',
        'energy'           => 'Energy (Power / Oil & Gas)',
        'tourism'          => 'Tourism / Hospitality',
        'waste_management' => 'Waste Management',
        'other'            => 'Other',
    ];

    public const ESIA_CLASS_LABELS = [
        'A'      => 'Category A — Full ESIA Required',
        'B'      => 'Category B — Partial EIA Required',
        'C'      => 'Category C — No EIA Required',
        'exempt' => 'Exempt from ESIA',
    ];

    public const ESIA_CLASS_COLORS = [
        'A'      => 'danger',
        'B'      => 'warning',
        'C'      => 'success',
        'exempt' => 'gray',
    ];

    public const STATUS_LABELS = [
        'draft'        => 'Draft',
        'submitted'    => 'Submitted',
        'under_review' => 'Under Review',
        'approved'     => 'Approved',
        'rejected'     => 'Rejected',
    ];

    public const STATUS_COLORS = [
        'draft'        => 'gray',
        'submitted'    => 'info',
        'under_review' => 'primary',
        'approved'     => 'success',
        'rejected'     => 'danger',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->esia_ref_number)) {
                $year  = now()->format('Y');
                $count = self::whereYear('created_at', $year)->count() + 1;
                $model->esia_ref_number = 'ESIA/' . $year . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
