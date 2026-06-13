<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'category',
        'amount',
        'description',
        'transaction_date',
        'recorded_by',
        'project_id',
        'balance_after',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * The user who recorded this transaction.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * The project this transaction is attributed to (optional).
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
