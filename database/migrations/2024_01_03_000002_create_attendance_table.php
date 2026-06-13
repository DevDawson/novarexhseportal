<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();

            // Hours_Worked = Time_Out - Time_In (decimal hours)
            $table->decimal('hours_worked', 5, 2)->default(0);

            // Overtime_Hours = Hours_Worked - Standard_Daily_Hours (if positive, else 0)
            $table->decimal('overtime_hours', 5, 2)->default(0);

            $table->enum('status', ['present', 'absent', 'leave', 'holiday'])->default('present');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['staff_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
