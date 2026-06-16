<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'category', 'document_number', 'file_path',
        'issue_date', 'expiry_date', 'uploaded_by', 'status',
        'current_revision', 'document_owner', 'distribution_list', 'next_review_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'next_review_date' => 'date',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentRevision::class);
    }

    public function latestRevision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DocumentRevision::class)->latestOfMany();
    }
}
