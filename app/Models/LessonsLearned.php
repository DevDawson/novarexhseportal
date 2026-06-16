<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonsLearned extends Model
{
    use HasFactory;

    protected $table = 'lessons_learned';

    protected $fillable = [
        'incident_id', 'audit_id', 'project_id', 'department_id',
        'title', 'lesson_type', 'description', 'recommendations', 'actions_taken',
        'applicable_to', 'author_id', 'reviewed_by_id', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function incident(): BelongsTo { return $this->belongsTo(Incident::class); }
    public function audit(): BelongsTo { return $this->belongsTo(InternalAudit::class, 'audit_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_id'); }
}
