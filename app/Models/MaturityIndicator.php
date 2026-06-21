<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaturityIndicator extends Model
{
    protected $fillable = ['dimension_id', 'name', 'auto_source', 'sort_order'];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(MaturityDimension::class, 'dimension_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(MaturityScore::class, 'indicator_id');
    }
}
