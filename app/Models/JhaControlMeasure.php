<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JhaControlMeasure extends Model
{
    protected $table = 'jha_control_measures';

    protected $fillable = [
        'jha_hazard_id', 'hierarchy_level', 'description',
        'responsible_person', 'status', 'sort_order',
    ];

    protected $casts = ['hierarchy_level' => 'integer', 'sort_order' => 'integer'];

    public static array $hierarchyLabels = [
        1 => 'Level 1 — Elimination',
        2 => 'Level 2 — Substitution',
        3 => 'Level 3 — Engineering Controls',
        4 => 'Level 4 — Administrative Controls',
        5 => 'Level 5 — PPE',
    ];

    public static array $hierarchyExamples = [
        1 => 'Remove hazardous chemical / eliminate the hazard completely',
        2 => 'Replace with safer alternative (e.g. water-based paint)',
        3 => 'Machine guarding, ventilation, interlocks',
        4 => 'Training, procedures, signs, job rotation',
        5 => 'Helmet, gloves, respirator, safety boots',
    ];

    public function hazard(): BelongsTo { return $this->belongsTo(JhaHazard::class, 'jha_hazard_id'); }
}
