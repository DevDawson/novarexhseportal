<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_document_id', 'revision_number', 'revision_reason',
        'revised_by_id', 'reviewed_by_id', 'approved_by_id',
        'revision_date', 'approved_date', 'file_path', 'status', 'notes',
    ];

    protected $casts = [
        'revision_date' => 'date',
        'approved_date' => 'date',
    ];

    public function document(): BelongsTo { return $this->belongsTo(CorporateDocument::class, 'corporate_document_id'); }
    public function revisedBy(): BelongsTo { return $this->belongsTo(User::class, 'revised_by_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_id'); }
}
