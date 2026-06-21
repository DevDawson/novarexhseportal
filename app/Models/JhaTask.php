<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JhaTask extends Model
{
    protected $table = 'jha_tasks';

    protected $fillable = ['jha_analysis_id', 'step_number', 'task_description'];

    public function jhaAnalysis(): BelongsTo { return $this->belongsTo(JhaAnalysis::class, 'jha_analysis_id'); }
    public function hazards(): HasMany       { return $this->hasMany(JhaHazard::class, 'jha_task_id'); }
    public function environment(): HasOne    { return $this->hasOne(JhaEnvironment::class, 'jha_task_id'); }
}
