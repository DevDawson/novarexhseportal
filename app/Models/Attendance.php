<?php

namespace App\Models;

use App\Services\AttendanceCalculationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'staff_id',
        'attendance_date',
        'time_in',
        'time_out',
        'hours_worked',
        'overtime_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * The staff member this attendance record belongs to.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Auto-calculate hours_worked and overtime_hours from
     * time_in / time_out whenever the record is saved.
     */
    protected static function booted(): void
    {
        static::saving(function (Attendance $attendance) {
            if ($attendance->status !== 'present') {
                $attendance->hours_worked = 0;
                $attendance->overtime_hours = 0;

                return;
            }

            $result = AttendanceCalculationService::calculate(
                $attendance->time_in,
                $attendance->time_out,
            );

            $attendance->hours_worked = $result['hours_worked'];
            $attendance->overtime_hours = $result['overtime_hours'];
        });
    }
}
