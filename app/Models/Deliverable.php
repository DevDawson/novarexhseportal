<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deliverable extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'document_title',
        'document_code',
        'document_type',
        'revision_no',
        'file_path',
        'status',
        'prepared_by',
        'reviewed_by',
        'submission_date',
        'due_date',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * The project this deliverable belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user who prepared this deliverable.
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * The user who reviewed this deliverable.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Full revision history for this deliverable.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(DeliverableRevision::class);
    }
}
