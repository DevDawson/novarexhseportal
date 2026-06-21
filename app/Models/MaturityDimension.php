<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaturityDimension extends Model
{
    protected $fillable = ['code', 'name', 'weight', 'sort_order'];

    public function indicators(): HasMany
    {
        return $this->hasMany(MaturityIndicator::class, 'dimension_id')->orderBy('sort_order');
    }
}
