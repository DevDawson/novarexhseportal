<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'entry_date',
        'description',
        'source_type',
        'source_id',
        'posted_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    /**
     * Human-readable labels for source_type values.
     */
    public const SOURCE_LABELS = [
        'manual' => 'Manual Entry',
        'payroll_approval' => 'Payroll Approval',
        'payroll_payment' => 'Payroll Payment',
        'statutory_remittance' => 'Statutory Remittance',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * The payroll record this entry was posted for, if source_type is
     * 'payroll_approval' or 'payroll_payment'.
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'source_id');
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function getIsBalancedAttribute(): bool
    {
        return round($this->total_debit, 2) === round($this->total_credit, 2);
    }

    /**
     * Whether this entry was created automatically by the system
     * (payroll posting) rather than manually by an accountant.
     */
    public function getIsAutoPostedAttribute(): bool
    {
        return $this->source_type !== 'manual';
    }

    /**
     * Generate the next sequential journal reference for a given date,
     * e.g. "JE-2026-06-0001".
     */
    public static function nextReference(\Illuminate\Support\Carbon $date): string
    {
        $prefix = 'JE-'.$date->format('Y-m').'-';

        $lastNumber = self::where('reference', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(reference, '.(strlen($prefix) + 1).') AS UNSIGNED)) as max_num')
            ->value('max_num');

        $next = ((int) $lastNumber) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
