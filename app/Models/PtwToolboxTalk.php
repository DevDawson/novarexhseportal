<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtwToolboxTalk extends Model
{
    protected $fillable = [
        'permit_to_work_id',
        'conducted_by_id',
        'conducted_at',
        'topics_covered',
        'attendees',
        'number_of_attendees',
        'summary',
        'safety_concerns_raised',
    ];

    protected $casts = [
        'conducted_at'       => 'datetime',
        'number_of_attendees' => 'integer',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(PermitToWork::class, 'permit_to_work_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by_id');
    }
}
