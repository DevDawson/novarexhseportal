<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsiaAudit extends Model
{
    use HasFactory;

    protected $table = 'esia_audits';

    protected $fillable = [
        'project_id',
        'type',
        'reference_number',
        'assessment_date',
        'lead_assessor_id',
        'findings_summary',
        'recommendations',
        'report_file',
        'status',
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];

    /**
     * The project this assessment/audit belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user who led the assessment/audit.
     */
    public function leadAssessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_assessor_id');
    }
}
