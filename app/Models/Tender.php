<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'tender_title',
        'tender_number',
        'procuring_entity',
        'description',
        'estimated_value',
        'currency',
        'exchange_rate',
        'submission_deadline',
        'stage',
        'assigned_to',
        'win_probability',
        'notes',
    ];

    protected $casts = [
        'submission_deadline' => 'date',
        'estimated_value' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'win_probability' => 'integer',
    ];

    /**
     * Estimated value converted to the opposite currency, using the
     * stored exchange_rate (1 USD = exchange_rate TZS).
     *
     *  - If currency is TZS, returns the USD equivalent.
     *  - If currency is USD, returns the TZS equivalent.
     */
    public function getEstimatedValueConvertedAttribute(): float
    {
        $rate = (float) ($this->exchange_rate ?: 1);

        if ($this->currency === 'USD') {
            return round((float) $this->estimated_value * $rate, 2);
        }

        return $rate > 0 ? round((float) $this->estimated_value / $rate, 2) : 0.0;
    }

    /**
     * The client/procuring entity linked to this tender (if registered as a client).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user (BD officer) assigned to this tender.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * All activities logged for this tender (site visits, follow ups, etc.).
     */
    public function activities(): HasMany
    {
        return $this->hasMany(TenderActivity::class);
    }
}
