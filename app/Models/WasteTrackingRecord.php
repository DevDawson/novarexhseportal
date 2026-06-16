<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteTrackingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'waste_type', 'waste_description', 'quantity', 'unit',
        'generation_date', 'disposal_method', 'disposal_facility', 'transporter',
        'manifest_number', 'recorded_by_id', 'disposal_date', 'status', 'notes',
    ];

    protected $casts = [
        'generation_date' => 'date',
        'disposal_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_id'); }
}
