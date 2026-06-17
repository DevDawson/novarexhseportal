<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_stakeholder_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('screening_id')->nullable()->constrained('esia_screenings')->nullOnDelete();
            $table->enum('consultation_type', [
                'public_meeting', 'focus_group', 'written_comment',
                'site_visit', 'workshop', 'expert_consultation', 'other',
            ])->default('public_meeting');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('venue', 255)->nullable();
            $table->date('consultation_date')->nullable();
            $table->unsignedSmallInteger('number_attended')->default(0);
            $table->string('facilitator', 255)->nullable();
            $table->text('stakeholder_groups')->nullable();
            $table->text('key_concerns_raised')->nullable();
            $table->text('responses_given')->nullable();
            $table->text('how_incorporated')->nullable();
            $table->enum('status', ['planned', 'completed', 'cancelled'])->default('planned');
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('minutes_file', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_stakeholder_consultations');
    }
};
