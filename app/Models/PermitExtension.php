<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_to_work_id',
        'previous_valid_to',
        'extended_to',
        'reason',
        'extended_by',
    ];

    protected $casts = [
        'previous_valid_to' => 'datetime',
        'extended_to' => 'datetime',
    ];

    public function permitToWork(): BelongsTo
    {
        return $this->belongsTo(PermitToWork::class);
    }

    public function extendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extended_by');
    }
}
