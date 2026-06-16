<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons_learned', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();
            $table->foreignId('audit_id')->nullable()->constrained('internal_audits')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title');
            $table->enum('lesson_type', ['safety', 'environmental', 'process', 'quality', 'emergency_response', 'regulatory', 'other'])->default('safety');
            $table->text('description');
            $table->text('recommendations');
            $table->text('actions_taken')->nullable();
            $table->enum('applicable_to', ['all_projects', 'specific_project', 'department', 'organization_wide'])->default('all_projects');
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons_learned');
    }
};
