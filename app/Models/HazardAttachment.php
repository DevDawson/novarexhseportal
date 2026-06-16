<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'hazard_register_id', 'file_name', 'file_path', 'file_type',
        'attachment_type', 'description', 'uploaded_by_id', 'upload_date',
    ];

    protected $casts = [
        'upload_date' => 'date',
    ];

    public function hazard(): BelongsTo
    {
        return $this->belongsTo(HazardRegister::class, 'hazard_register_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
