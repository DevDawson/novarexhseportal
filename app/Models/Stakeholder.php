<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stakeholder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'organisation',
        'contact_person',
        'email',
        'phone',
        'influence_level',
        'interest_level',
        'engagement_strategy',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'influence_level' => 'integer',
        'interest_level'  => 'integer',
        'is_active'       => 'boolean',
    ];

    public const CATEGORY_LABELS = [
        'community'  => 'Community',
        'government' => 'Government / Regulator',
        'ngo'        => 'NGO / Civil Society',
        'client'     => 'Client',
        'supplier'   => 'Supplier / Contractor',
        'employee'   => 'Employee / Worker',
        'media'      => 'Media',
        'investor'   => 'Investor / Financier',
        'other'      => 'Other',
    ];

    public const STRATEGY_LABELS = [
        'monitor'        => 'Monitor',
        'keep_informed'  => 'Keep Informed',
        'keep_satisfied' => 'Keep Satisfied',
        'manage_closely' => 'Manage Closely',
    ];

    // ----------------------------------------------------------------
    // Relations
    // ----------------------------------------------------------------

    public function engagements(): HasMany
    {
        return $this->hasMany(StakeholderEngagement::class);
    }

    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class);
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getStrategyQuadrantAttribute(): string
    {
        $inf = (int) $this->influence_level;
        $int = (int) $this->interest_level;

        if ($inf >= 3 && $int >= 3) return 'manage_closely';
        if ($inf >= 3)              return 'keep_satisfied';
        if ($int >= 3)              return 'keep_informed';
        return 'monitor';
    }
}
