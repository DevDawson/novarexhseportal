<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_to_work_id',
        'item',
        'is_checked',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function permitToWork(): BelongsTo
    {
        return $this->belongsTo(PermitToWork::class);
    }
}
