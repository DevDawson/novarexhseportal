<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'activity_type',
        'activity_date',
        'description',
        'performed_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    /**
     * The tender this activity belongs to.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    /**
     * The user who performed this activity.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
