<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * The invoice this line item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Auto-calculate the line amount (quantity x unit_price)
     * whenever the item is saved.
     */
    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item) {
            $item->amount = round((float) $item->quantity * (float) $item->unit_price, 2);
        });
    }
}
