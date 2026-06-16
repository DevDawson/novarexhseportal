<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('training_title');
            $table->enum('training_type', ['induction', 'refresher', 'toolbox_talk', 'external', 'certification', 'e_learning', 'drill_exercise'])->default('refresher');
            $table->string('provider')->nullable();
            $table->text('topic')->nullable();
            $table->date('date_attended');
            $table->unsignedSmallInteger('duration_hours')->default(1);
            $table->enum('result', ['passed', 'failed', 'not_assessed'])->default('passed');
            $table->string('certificate_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('conducted_by')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_records');
    }
};
