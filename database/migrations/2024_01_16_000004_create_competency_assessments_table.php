<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->enum('competency_area', ['safety', 'environmental', 'technical', 'emergency_response', 'leadership', 'quality', 'other'])->default('safety');
            $table->string('competency_description');
            $table->enum('assessment_method', ['observation', 'written_test', 'practical_demonstration', 'supervisor_review', 'simulation'])->default('observation');
            $table->foreignId('assessed_by_id')->constrained('users');
            $table->date('assessment_date');
            $table->unsignedTinyInteger('score')->nullable();
            $table->enum('result', ['competent', 'not_yet_competent', 'requires_training'])->default('competent');
            $table->date('next_assessment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_assessments');
    }
};
