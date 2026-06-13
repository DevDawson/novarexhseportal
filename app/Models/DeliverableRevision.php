<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverableRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'deliverable_id',
        'revision_no',
        'file_path',
        'change_description',
        'revised_by',
        'revised_at',
    ];

    protected $casts = [
        'revised_at' => 'datetime',
    ];

    /**
     * The deliverable this revision belongs to.
     */
    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class);
    }

    /**
     * The user who made this revision.
     */
    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
