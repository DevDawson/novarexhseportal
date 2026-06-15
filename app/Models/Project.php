<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'project_code',
        'title',
        'description',
        'service_type',
        'project_manager_id',
        'start_date',
        'end_date',
        'contract_value',
        'location',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    /**
     * The client this project belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The staff/user assigned as project manager.
     */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * ESIA / Audit records for this project.
     */
    public function esiaAudits(): HasMany
    {
        return $this->hasMany(EsiaAudit::class);
    }

    /**
     * Incidents reported under this project.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Risks identified for this project.
     */
    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    /**
     * Invoices raised against this project.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Field expenses logged against this project.
     */
    public function fieldExpenses(): HasMany
    {
        return $this->hasMany(FieldExpense::class);
    }

    /**
     * Petty cash transactions linked to this project.
     */
    public function pettyCashTransactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class);
    }

    /**
     * Deliverables / document control items for this project.
     */
    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }

    // ----------------------------------------------------------------
    // EIA / ESIA Module relations
    // ----------------------------------------------------------------

    public function esiaScreening(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EsiaScreening::class);
    }

    public function esiaScopingIssues(): HasMany
    {
        return $this->hasMany(EsiaScopingIssue::class);
    }

    public function esiaBaselineData(): HasMany
    {
        return $this->hasMany(EsiaBaselineData::class);
    }

    public function esiaImpactAssessments(): HasMany
    {
        return $this->hasMany(EsiaImpactAssessment::class);
    }

    public function esiaMitigationActions(): HasMany
    {
        return $this->hasMany(EsiaMitigationAction::class);
    }

    public function esiaReports(): HasMany
    {
        return $this->hasMany(EsiaReport::class);
    }

    public function esiaRegulatorySubmissions(): HasMany
    {
        return $this->hasMany(EsiaRegulatorySubmission::class);
    }
}
