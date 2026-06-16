<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazop_studies', function (Blueprint $table) {
            $table->id();
            $table->string('study_ref', 30)->nullable()->unique();
            $table->string('title');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('process_description')->nullable();
            $table->string('pid_reference')->nullable()->comment('P&ID drawing or document reference');
            $table->string('facility_area')->nullable();
            $table->date('study_date')->nullable();
            $table->foreignId('facilitator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('team_members')->nullable()->comment('JSON list of team member names');
            $table->text('study_scope')->nullable();
            $table->text('study_objectives')->nullable();
            $table->enum('status', [
                'draft',
                'in_progress',
                'complete',
                'under_review',
                'approved',
                'closed',
            ])->default('draft');
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('review_date')->nullable();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();
            $table->text('approval_comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazop_studies');
    }
};
